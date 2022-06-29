<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductAndVariationColumnsLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->unsignedInteger('variation_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('wave_id')->nullable(true);
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
            $table->dropColumn('variation_id');
            $table->dropColumn('product_id');
            $table->dropColumn('wave_id');
        });
    }
}
