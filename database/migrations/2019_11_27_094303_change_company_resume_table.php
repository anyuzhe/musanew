<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCompanyResumeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('company_resume', function (Blueprint $table) {
            $table->integer('source_recruit_id')->nullable()->change();
            $table->integer('source_entrust_id')->nullable()->change();
            $table->integer('source_job_id')->nullable()->change();
            $table->integer('source_company_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->table('company_resume', function (Blueprint $table) {
            //
        });
    }
}
