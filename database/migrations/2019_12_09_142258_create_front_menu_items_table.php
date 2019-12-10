<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFrontMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('front_menu_items', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('menu_id')->default(1);
            $table->string('title');
            $table->string('url')->nullable();
            $table->integer('type')->default(1);
            $table->string('icon')->nullable();
            $table->string('auth_key')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('order')->default(99);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('front_menu_items');
    }
}
