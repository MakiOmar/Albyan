<?php

namespace Tests\Unit;

use App\Models\CourseImport;
use App\Models\Translation\WebinarTranslation;
use App\Models\Webinar;
use App\Services\CourseImportService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class CourseImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('full_name')->nullable();
        });

        Schema::create('webinars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->unsignedInteger('teacher_id')->nullable();
            $table->unsignedInteger('creator_id')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('image_cover')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->double('price')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status')->nullable();
            $table->boolean('private')->default(false);
            $table->boolean('downloadable')->default(false);
            $table->boolean('support')->default(false);
            $table->boolean('certificate')->default(false);
            $table->boolean('forum')->default(false);
            $table->boolean('subscribe')->default(false);
            $table->integer('points')->nullable();
            $table->text('message_for_reviewer')->nullable();
            $table->unsignedInteger('start_date')->nullable();
            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('updated_at')->nullable();
        });

        Schema::create('webinar_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('webinar_id');
            $table->string('locale', 191);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('seo_description')->nullable();
        });

        DB::table('users')->insert([
            'id' => 1,
            'full_name' => 'Teacher One',
        ]);
    }

    public function test_it_updates_existing_course_by_id(): void
    {
        $webinar = Webinar::query()->create([
            'slug' => 'old-slug',
            'type' => 'course',
            'teacher_id' => 1,
            'creator_id' => 1,
            'thumbnail' => '/old-thumb.jpg',
            'image_cover' => '/old-cover.jpg',
            'status' => 'pending',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        WebinarTranslation::query()->create([
            'webinar_id' => $webinar->id,
            'locale' => 'en',
            'title' => 'Old title',
            'description' => 'Old desc',
            'seo_description' => 'Old seo',
        ]);

        $service = new CourseImportService();
        $result = $service->processRow([
            'id' => $webinar->id,
            'slug' => 'new-slug',
            'locale' => 'en',
            'title' => 'Updated title',
            'description' => 'Updated description',
            'status' => 'active',
        ], new CourseImport());

        $this->assertSame('updated', $result['result']);
        $this->assertDatabaseHas('webinars', [
            'id' => $webinar->id,
            'slug' => 'new-slug',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('webinar_translations', [
            'webinar_id' => $webinar->id,
            'locale' => 'en',
            'title' => 'Updated title',
        ]);
    }

    public function test_it_updates_existing_course_by_slug_when_id_missing(): void
    {
        $webinar = Webinar::query()->create([
            'slug' => 'match-by-slug',
            'type' => 'course',
            'teacher_id' => 1,
            'creator_id' => 1,
            'thumbnail' => '/thumb.jpg',
            'image_cover' => '/cover.jpg',
            'status' => 'pending',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        WebinarTranslation::query()->create([
            'webinar_id' => $webinar->id,
            'locale' => 'en',
            'title' => 'Initial title',
            'description' => null,
            'seo_description' => null,
        ]);

        $service = new CourseImportService();
        $result = $service->processRow([
            'slug' => 'match-by-slug',
            'locale' => 'en',
            'title' => 'Slug updated title',
            'status' => 'inactive',
        ], new CourseImport());

        $this->assertSame('updated', $result['result']);
        $this->assertDatabaseHas('webinars', [
            'id' => $webinar->id,
            'status' => 'inactive',
        ]);
    }

    public function test_it_creates_new_course_when_no_match(): void
    {
        $service = new CourseImportService();
        $result = $service->processRow([
            'slug' => 'new-course-slug',
            'locale' => 'en',
            'title' => 'New Course',
            'description' => 'New description',
            'type' => 'course',
            'teacher_id' => 1,
            'thumbnail' => '/new-thumb.jpg',
            'image_cover' => '/new-cover.jpg',
            'status' => 'pending',
        ], new CourseImport());

        $this->assertSame('created', $result['result']);
        $this->assertDatabaseHas('webinars', [
            'slug' => 'new-course-slug',
            'teacher_id' => 1,
        ]);
        $this->assertDatabaseHas('webinar_translations', [
            'locale' => 'en',
            'title' => 'New Course',
        ]);
    }
}
