<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('department');
            $table->string('provider')->nullable(true);
            $table->string('barcode');
            $table->string('style');
            $table->string('sku');
            $table->string('description');
            $table->integer('pieces');
            $table->integer('prepacks');
            $table->boolean('active')->default(true);
            $table->integer('order_id')->unsigned()->index();
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
        Schema::dropIfExists('lines');
    }
}
