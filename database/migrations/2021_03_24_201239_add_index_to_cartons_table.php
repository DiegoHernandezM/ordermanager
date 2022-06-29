<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCartonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartons', function (Blueprint $table) {
            $table->index('barcode');
            $table->index('wave_id');
            $table->index('transferNum');
            $table->index('store');
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
            $table->dropIndex('barcode');
            $table->dropIndex('wave_id');
            $table->dropIndex('transferNum');
            $table->dropIndex('store');
        });
    }
}
