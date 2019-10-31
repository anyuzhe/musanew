<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTimelenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('jobs', function (Blueprint $table) {
            $table->timestamp('created_at', 0)->nullable()->change();
            $table->timestamp('updated_at', 0)->nullable()->change();
        });
        Schema::connection('musa')->table('company_addresses', function (Blueprint $table) {
            $table->timestamp('created_at', 0)->nullable()->change();
            $table->timestamp('updated_at', 0)->nullable()->change();
        });
        Schema::connection('musa')->table('company', function (Blueprint $table) {
            $table->timestamp('created_at', 0)->nullable()->change();
            $table->timestamp('updated_at', 0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
