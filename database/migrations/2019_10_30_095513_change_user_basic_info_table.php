<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserBasicInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('moodle')->table('user_basic_info', function (Blueprint $table) {
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
        Schema::connection('moodle')->table('user_basic_info', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
}
