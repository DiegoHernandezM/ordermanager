<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePalletContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pallet_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('wave_ref');
            $table->string('folio_mov');
            $table->string('sku');
            $table->integer('cantidad');
            $table->integer('cajas');
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
        Schema::dropIfExists('pallet_contents');
    }
}
