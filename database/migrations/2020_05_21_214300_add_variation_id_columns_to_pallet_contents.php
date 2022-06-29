<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariationIdColumnsToPalletContents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pallet_contents', function (Blueprint $table) {
            $table->dropColumn('wave_ref');
            $table->integer('variation_id');
            $table->integer('department_id');
            $table->integer('wave_id');
            //
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
            $table->dropColumn('variation_id');
            $table->dropColumn('department_id');
        });
    }
}
