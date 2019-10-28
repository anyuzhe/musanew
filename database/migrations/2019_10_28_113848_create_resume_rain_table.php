<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResumeRainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->create('resume_rain', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('resume_id')->comment('简历id');
            $table->string('start_date', 191)->nullable()->comment('开始时间');
            $table->string('end_date', 191)->nullable()->comment('结束时间');
            $table->string('organization_name', 191)->nullable()->comment('机构名称');
            $table->text('rain_content')->nullable()->comment('培训内容');
            $table->string('rain_result', 191)->nullable()->comment('培训成果');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('resume_rain');
    }
}
