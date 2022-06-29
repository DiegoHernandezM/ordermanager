<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionalFieldsToOrdergroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->unsignedInteger('solicitudId')->nullable();
            $table->unsignedInteger('claveOS')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->unsignedInteger('solicitudId')->nullable();
            $table->unsignedInteger('claveOS')->nullable();
        });
    }
}
