<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePalletMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('pallet_movement');
        Schema::create('pallet_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session');
            $table->integer('wave_id');
            $table->integer('pallet_id');
            $table->integer('zone_type_id');
            $table->integer('user_id');
            $table->integer('cant');
            $table->string('sku');
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
        Schema::dropIfExists('pallet_movements');
    }
}
