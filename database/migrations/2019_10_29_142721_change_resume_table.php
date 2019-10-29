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
            $table->string('resume_name', 191)->default('')->comment('简历名称');

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
        });
    }
}
