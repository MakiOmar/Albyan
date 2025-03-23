<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarCertificatesTable extends Migration
{
    public function up()
    {
        Schema::create(
            'webinar_certificates',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('student_id');
                $table->string('webinar_title')->nullable();
                $table->json('certificates');  // This will store the certificates' paths as JSON
                $table->timestamps();

                // Foreign key constraint
                $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('webinar_certificates');
    }
}
