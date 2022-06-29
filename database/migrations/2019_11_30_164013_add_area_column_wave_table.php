<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaColumnWaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waves', function (Blueprint $table) {
            $table->string('area')->nullable(true);
            $table->unsignedInteger('area_id')->nullable(true);
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
            $table->dropColumn('area');
            $table->dropColumn('area_id');
        });
    }
}
