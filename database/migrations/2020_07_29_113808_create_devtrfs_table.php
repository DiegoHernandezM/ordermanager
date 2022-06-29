<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevtrfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devtrfs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('devolution_id');
            $table->string('transferNum');
            $table->string('store');
            $table->unsignedInteger('total_pieces')->default(0);
            $table->unsignedInteger('total_prepacks')->default(0);
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
        Schema::dropIfExists('devtrfs');
    }
}
