<?php

// database/migrations/xxxx_xx_xx_create_instructor_files_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstructorFilesTable extends Migration
{
    public function up()
    {
        Schema::create('instructor_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webinar_id')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->string('title');
            $table->string('path');
            $table->timestamps();

            // لو في علاقات مستقبلية
            // $table->foreign('webinar_id')->references('id')->on('webinars')->onDelete('set null');
            // $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('instructor_files');
    }
}
