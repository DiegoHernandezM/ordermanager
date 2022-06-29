<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartonLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carton_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('carton_id');
            $table->unsignedInteger('line_id');
            $table->unsignedInteger('prepacks')->default(0);
            $table->unsignedInteger('pieces')->default(0);
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
        Schema::dropIfExists('carton_lines');
    }
}
