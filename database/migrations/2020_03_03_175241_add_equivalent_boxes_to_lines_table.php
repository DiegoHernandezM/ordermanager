<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEquivalentBoxesToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->decimal('equivalent_boxes', 8, 2);
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
            $table->dropColumn('equivalent_boxes');
        });
    }
}
