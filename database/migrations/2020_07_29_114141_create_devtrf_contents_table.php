<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevtrfContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devtrf_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('devtrf_id');
            $table->string('sku');
            $table->unsignedInteger('variation_id');
            $table->unsignedInteger('pieces')->default(0);
            $table->unsignedInteger('prepacks')->default(0);
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
        Schema::dropIfExists('devtrf_contents');
    }
}
