<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyResumeGradeSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->create('company_resume_grade_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',191)->nullable();
            $table->string('scope',191)->nullable();
            $table->longText('value')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('company_resume_grade_settings');
    }
}
