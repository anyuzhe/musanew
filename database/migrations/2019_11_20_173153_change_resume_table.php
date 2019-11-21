<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeResumeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('resume', function (Blueprint $table) {
            $table->integer('is_married')->default(null)->comment('婚姻状态 0未婚 1已婚')->nullable()->change();
            $table->integer('on_the_job')->default(null)->comment('是否在职')->nullable()->change();
            $table->integer('gender')->default(null)->comment('性别 1男0女')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resume', function (Blueprint $table) {
            //
        });
    }
}
