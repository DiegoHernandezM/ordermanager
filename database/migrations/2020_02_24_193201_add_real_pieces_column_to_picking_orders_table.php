<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRealPiecesColumnToPickingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('picking_orders', function (Blueprint $table) {
            $table->integer('real_pieces')->nullable(true);
            $table->integer('real_boxes')->nullable(true);
            $table->dropColumn('location');
        });

        Schema::table('picking_orders', function (Blueprint $table) {
            $table->string('location')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('picking_orders', function (Blueprint $table) {
            $table->dropColumn('real_pieces');
            $table->dropColumn('real_boxes');
            $table->dropColumn('location');
        });

        Schema::table('picking_orders', function (Blueprint $table) {
            $table->string('location');
        });
    }
}
