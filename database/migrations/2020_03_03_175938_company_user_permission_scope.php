<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CompanyUserPermissionScope extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('musa')->create('company_user_permission_scopes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id');
            $table->integer('company_permission_id');
            $table->integer('user_id');
            $table->string('key',191);
            $table->integer('type')->default(1);
            $table->text('department_ids')->nullable();
            $table->text('user_ids')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('key');
            $table->index('company_permission_id','permission_scope_company_permission_id');
            $table->index('company_id','permission_scope_company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('musa')->dropIfExists('company_user_permission_scopes');
    }
}
