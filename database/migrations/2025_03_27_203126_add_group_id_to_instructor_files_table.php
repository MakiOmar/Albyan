<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('instructor_files', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('webinar_id');
            $table->foreign('group_id')->references('id')->on('course_groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('instructor_files', function (Blueprint $table) {
            // حذف العلاقة أولاً
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }

};
