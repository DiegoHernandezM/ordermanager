<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cartons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('wave_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('total_pieces');
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
        Schema::dropIfExists('cartons');
    }
}
