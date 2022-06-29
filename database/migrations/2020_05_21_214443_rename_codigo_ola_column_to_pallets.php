<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCodigoOlaColumnToPallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallets', function (Blueprint $table) {
            //
            $table->dropColumn('codigo_ola');
            $table->dropColumn('wave_ref');
            $table->integer('wave_id');
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
            $table->dropColumn('wave_id');
            $table->string('wave_ref')->nullable(true);
            $table->string('codigo_ola')->nullable(true);
            //
        });
    }
}
