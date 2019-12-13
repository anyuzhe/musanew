<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->table('company', function (Blueprint $table) {
            $table->string('company_code')->nullable()->change();
            $table->integer('status')->default(1);
            $table->string('contact_number')->nullable();
            $table->string('site')->nullable();
            $table->string('deposit_bank_name')->nullable();
            $table->string('deposit_bank_account')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->table('company', function (Blueprint $table) {
            //
        });
    }
}
