<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortedBoxesColumnToWavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waves', function (Blueprint $table) {
            $table->unsignedInteger('sorted_boxes')->nullable();
            $table->unsignedInteger('sorted_prepacks')->nullable();
            $table->unsignedInteger('prepacks')->nullable();
            $table->dateTime('picking_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('waves', function (Blueprint $table) {
            $table->dropColumn('sorted_boxes');
            $table->dropColumn('sorted_prepacks');
            $table->dropColumn('prepacks');
            $table->dropColumn('picking_end');
        });
    }
}
