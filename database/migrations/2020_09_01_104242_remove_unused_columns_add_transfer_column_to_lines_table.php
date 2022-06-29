<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedColumnsAddTransferColumnToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->dropColumn('division');
            $table->dropColumn('category');
            $table->dropColumn('classification');
            $table->dropColumn('priority');
            $table->dropColumn('rounded_boxes');
            $table->dropColumn('equivalent_boxes');
            $table->dropColumn('ranking');
            $table->string('transfer')->nullable();
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
            $table->unsignedInteger('division');
            $table->unsignedInteger('category');
            $table->unsignedInteger('classification');
            $table->unsignedInteger('priority');
            $table->unsignedInteger('rounded_boxes');
            $table->unsignedInteger('equivalent_boxes');
            $table->unsignedInteger('ranking');
            $table->dropColumn('transfer');
        });
    }
}
