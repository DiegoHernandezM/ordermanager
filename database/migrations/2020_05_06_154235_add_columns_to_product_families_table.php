<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductFamiliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_families', function (Blueprint $table) {
            $table->string('label', 100)->nullable();
            $table->unsignedInteger('ranking')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_families', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->dropColumn('ranking');
        });
    }
}
