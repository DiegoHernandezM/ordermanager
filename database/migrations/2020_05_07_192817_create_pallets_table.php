<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('codigo_ola');
            $table->string('wave_ref');
            $table->string('fecha_mov');
            $table->string('lpn_transportador')-> nullable();
            $table->string('almacen_dest');
            $table->string('ubicacion_dest');
            $table->string('status');
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
        Schema::dropIfExists('pallets');
    }
}
