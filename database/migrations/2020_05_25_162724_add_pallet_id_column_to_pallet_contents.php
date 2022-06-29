<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPalletIdColumnToPalletContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_contents', function (Blueprint $table) {
            //
            $table->integer('pallet_id')->default(0);
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
            //
            $table->dropColumn('pallet_id');
        });
    }
}
