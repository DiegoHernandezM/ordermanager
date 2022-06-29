<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCarton extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartons', function (Blueprint $table) {
            $table->string('waveNumber')->nullable();
            $table->string('businessName')->nullable();
            $table->string('area')->nullable();
            $table->bigInteger('orderNumber')->nullable();
            $table->string('barcode')->nullable();
            $table->bigInteger('route')->nullable();
            $table->string('route_name')->nullable();
            $table->bigInteger('store')->nullable();
            $table->string('store_name')->nullable();
            $table->json('labelDetail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartons', function (Blueprint $table) {
            $table->dropColumn('waveNumber')->nullable();
            $table->dropColumn('businessName')->nullable();
            $table->dropColumn('area')->nullable();
            $table->dropColumn('orderNumber')->nullable();
            $table->dropColumn('barcode')->nullable();
            $table->dropColumn('route')->nullable();
            $table->dropColumn('route_name')->nullable();
            $table->dropColumn('store')->nullable();
            $table->dropColumn('store_name')->nullable();
            $table->dropColumn('labelDetail')->nullable();
        });
    }
}
