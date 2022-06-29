<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToPalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->index('lpn_transportador');
            $table->index('wave_id');
            $table->index('zone_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->dropIndex('lpn_transportador');
            $table->dropIndex('wave_id');
            $table->dropIndex('zone_id');
        });
    }
}
