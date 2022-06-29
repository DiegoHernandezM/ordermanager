<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCartonLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carton_lines', function (Blueprint $table) {
            $table->index('sku');
            $table->index('carton_id');
            $table->index('line_id');
            $table->index('style');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carton_lines', function (Blueprint $table) {
            $table->dropIndex('sku');
            $table->dropIndex('carton_id');
            $table->dropIndex('line_id');
            $table->dropIndex('style');
        });
    }
}
