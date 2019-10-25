<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPauseAtToEntrustTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('company_job_recruit_entrust', function (Blueprint $table) {
            $table->timestamp('pause_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->table('company_job_recruit_entrust', function (Blueprint $table) {
            $table->dropColumn('pause_at');

        });
    }
}
