<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassificationColumnToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->string('classification')->nullable(true);
            $table->string('priority')->nullable(true);
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
            $table->dropColumn('classification');
            $table->dropColumn('priority');
        });
    }
}
