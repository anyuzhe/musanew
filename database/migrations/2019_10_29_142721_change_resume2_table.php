<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeResume2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('resume', function (Blueprint $table) {
            $table->string('resume_name', 191)->default('')->comment('简历名称');
            $table->string('usable_range', 191)->default('')->comment('使用范围');
            $table->string('avatar', 191)->nullable()->comment('头像');

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
            $table->dropColumn('resume_name');
            $table->dropColumn('usable_range');
            $table->dropColumn('avatar');
        });
    }
}