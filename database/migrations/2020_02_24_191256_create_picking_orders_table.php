<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picking_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('wave_id');
            $table->string('sku');
            $table->integer('pieces');
            $table->integer('boxes');
            $table->unsignedInteger('department_id');
            $table->string('location');
            $table->unsignedInteger('user_id');
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
        Schema::dropIfExists('picking_orders');
    }
}
