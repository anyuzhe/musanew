<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->create('company_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->integer('user_id')->nullable();

            //操作 内容 模块
            $table->string('operation',191)->nullable();
            $table->text('content')->nullable();
            $table->string('module',191)->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('user_id');
            $table->index('operation');
            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('company_logs');
    }
}
