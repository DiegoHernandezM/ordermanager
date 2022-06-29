<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlannedPiecesColumnToWavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waves', function (Blueprint $table) {
            $table->unsignedInteger('planned_pieces')->nullable();
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
            $table->dropColumn('planned_pieces');
        });
    }
}
