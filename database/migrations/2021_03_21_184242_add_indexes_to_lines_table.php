<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->index('wave_id');
            $table->index(['variation_id', 'style_id']);
            $table->index('sku');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropIndex('wave_id');
            $table->dropIndex(['variation_id', 'style_id']);
            $table->dropIndex('sku');
            $table->dropIndex('priority');
        });
    }
}
