<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReworkPalletMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_movements', function (Blueprint $table) {
            $table->dropColumn('cant');
            $table->dropColumn('sku');
            $table->dropColumn('zone_type_id');
            $table->string('from_zone');
            $table->string('to_zone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_movements', function (Blueprint $table) {
            $table->dropColumn('from_zone');
            $table->dropColumn('to_zone');
        });
    }
}
