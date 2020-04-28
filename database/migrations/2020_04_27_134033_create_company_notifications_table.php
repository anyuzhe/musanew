<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->dropIfExists('company_notifications');
        Schema::connection('musa')->create('company_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();

            $table->string('type',191)->nullable();
            $table->longText('content')->nullable();
            $table->longText('other_data')->nullable();
            $table->integer('is_read')->default(0);
            $table->timestamps();

            $table->index('company_id');
            $table->index('type');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('company_notifications');
    }
}
