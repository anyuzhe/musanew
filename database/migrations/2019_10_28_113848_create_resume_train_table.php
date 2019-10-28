<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResumeTrainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->create('resume_train', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('resume_id')->comment('简历id');
            $table->string('start_date', 191)->nullable()->comment('开始时间');
            $table->string('end_date', 191)->nullable()->comment('结束时间');
            $table->string('organization_name', 191)->nullable()->comment('机构名称');
            $table->text('train_content')->nullable()->comment('培训内容');
            $table->string('train_result', 191)->nullable()->comment('培训成果');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('resume_train');
    }
}
