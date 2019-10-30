<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeResume1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('resume', function (Blueprint $table) {
            $table->tinyInteger('on_the_job')->default(0)->comment('是否在职');
            $table->tinyInteger('is_base')->default(0)->comment('是否是基本信息');
            $table->tinyInteger('is_used')->default(1)->comment('是否使用');
            $table->tinyInteger('is_personal')->default(0)->comment('是否是个人拥有');
            $table->tinyInteger('is_public')->default(0)->comment('是否公开');
            $table->string('on_the_job_company_name', 191)->default('')->comment('在职公司');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->table('resume', function (Blueprint $table) {
            $table->dropColumn('on_the_job');
            $table->dropColumn('on_the_job_company_name');
            $table->dropColumn('is_public');
            $table->dropColumn('is_personal');
            $table->dropColumn('is_base');
        });
    }
}
