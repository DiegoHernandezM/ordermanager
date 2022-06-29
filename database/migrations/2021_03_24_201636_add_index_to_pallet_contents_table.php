<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToPalletContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_contents', function (Blueprint $table) {
            $table->index('sku');
            $table->index('wave_id');
            $table->index('pallet_id');
            $table->index('department_id');
            $table->index('style_id');
            $table->index('variation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pallet_contents', function (Blueprint $table) {
            $table->dropIndex('sku');
            $table->dropIndex('wave_id');
            $table->dropIndex('pallet_id');
            $table->dropIndex('department_id');
            $table->dropIndex('style_id');
            $table->dropIndex('variation_id');
        });
    }
}
