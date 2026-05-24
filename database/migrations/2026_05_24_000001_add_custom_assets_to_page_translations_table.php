<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomAssetsToPageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->longText('styles')->nullable()->after('content');
            $table->longText('scripts')->nullable()->after('styles');
            $table->longText('head_content')->nullable()->after('scripts');
            $table->longText('footer_content')->nullable()->after('head_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn(['styles', 'scripts', 'head_content', 'footer_content']);
        });
    }
}
