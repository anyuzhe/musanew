<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRecruit1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('company_job_recruit', function (Blueprint $table) {
            $table->tinyInteger('is_public')->default(0)->comment('是否公开');
        });
        Schema::connection('musa')->table('company_job_recruit_entrust', function (Blueprint $table) {
            $table->tinyInteger('is_public')->default(0)->comment('是否公开');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->table('company_job_recruit', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
        Schema::connection('musa')->table('company_job_recruit_entrust', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
}
