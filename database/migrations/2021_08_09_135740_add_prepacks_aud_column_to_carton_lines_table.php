<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrepacksAudColumnToCartonLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carton_lines', function (Blueprint $table) {
            $table->unsignedInteger('prepacks_aud');
            $table->unsignedInteger('pieces_aud');
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
            $table->dropColumn('prepacks_aud');
            $table->dropColumn('pieces_aud');
        });
    }
}
