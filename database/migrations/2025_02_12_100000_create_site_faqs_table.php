<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteFaqsTable extends Migration
{
    /**
     * Run the migrations.
     * Site FAQs for homepage accordion section (standalone content management).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_faqs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->unsignedInteger('order')->default(0);
            $table->enum('status', ['active', 'disable'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_faqs');
    }
}
