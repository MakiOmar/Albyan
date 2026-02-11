<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCategoryAndValueToHomeSectionsTable extends Migration
{
    /**
     * Run the migrations.
     * Adds category_id and value (JSON) for category_courses section; extends name enum.
     *
     * @return void
     */
    public function up()
    {
        $hasCategoryId = Schema::hasColumn('home_sections', 'category_id');
        $hasValue = Schema::hasColumn('home_sections', 'value');

        if (!$hasCategoryId || !$hasValue) {
            Schema::table('home_sections', function (Blueprint $table) use ($hasCategoryId, $hasValue) {
                if (!$hasCategoryId) {
                    $table->unsignedInteger('category_id')->nullable()->after('order');
                }
                if (!$hasValue) {
                    $table->json('value')->nullable()->after('category_id');
                }
            });
        }

        // Ensure category_id type matches categories.id (unsigned int) for foreign key
        if ($hasCategoryId) {
            DB::statement('ALTER TABLE home_sections MODIFY COLUMN category_id INT UNSIGNED NULL');
        }

        $fkExists = DB::selectOne("
            SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'home_sections'
            AND CONSTRAINT_NAME = 'home_sections_category_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [config('database.connections.mysql.database')]);
        if (!$fkExists) {
            Schema::table('home_sections', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            });
        }

        // Extend enum to include category_courses (full list from HomeSection::$names + new value)
        $enumValues = [
            'featured_classes',
            'latest_bundles',
            'latest_classes',
            'best_rates',
            'trend_categories',
            'full_advertising_banner',
            'best_sellers',
            'discount_classes',
            'free_classes',
            'store_products',
            'testimonials',
            'subscribes',
            'find_instructors',
            'reward_program',
            'become_instructor',
            'forum_section',
            'video_or_image_section',
            'instructors',
            'half_advertising_banner',
            'organizations',
            'blog',
            'upcoming_courses',
            'category_courses',
        ];
        $enumList = "'" . implode("','", $enumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Update any category_courses sections so enum change does not fail
        DB::table('home_sections')->where('name', 'category_courses')->update(['name' => 'blog']);

        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'value']);
        });

        // Restore original enum (without category_courses)
        $enumValues = [
            'featured_classes',
            'latest_bundles',
            'latest_classes',
            'best_rates',
            'trend_categories',
            'full_advertising_banner',
            'best_sellers',
            'discount_classes',
            'free_classes',
            'store_products',
            'testimonials',
            'subscribes',
            'find_instructors',
            'reward_program',
            'become_instructor',
            'forum_section',
            'video_or_image_section',
            'instructors',
            'half_advertising_banner',
            'organizations',
            'blog',
            'upcoming_courses',
        ];
        $enumList = "'" . implode("','", $enumValues) . "'";
        DB::statement("ALTER TABLE home_sections MODIFY COLUMN name ENUM({$enumList}) NOT NULL");
    }
}
