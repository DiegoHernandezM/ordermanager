<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDivisionColumnToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn('department');
            $table->dropColumn('provider');
            $table->dropColumn('barcode');
            $table->string('division')->nullable(true);
            $table->string('category')->nullable(true);
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
            $table->string('department')->nullable(true);
            $table->string('provider')->nullable(true);
            $table->string('barcode')->nullable(true);
            $table->dropColumn('division');
            $table->dropColumn('category');
        });
    }
}
