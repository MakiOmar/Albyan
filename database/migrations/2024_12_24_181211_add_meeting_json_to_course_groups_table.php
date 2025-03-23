<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('course_groups', function (Blueprint $table) {
        $table->json('meeting_json')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('course_groups', function (Blueprint $table) {
        $table->dropColumn('meeting_json');
    });
}

};
