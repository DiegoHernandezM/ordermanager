<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_colors', function (Blueprint $table) {
            $table->string('color_dictionary')->nullable();
            $table->string('hexadecimal_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropColumn('color_dictionary');
            $table->dropColumn('hexadecimal_color');
        });
    }
}
