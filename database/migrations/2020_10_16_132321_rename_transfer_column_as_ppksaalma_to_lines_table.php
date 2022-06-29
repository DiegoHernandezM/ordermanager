<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTransferColumnAsPpksaalmaToLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines', function (Blueprint $table) {
            $table->renameColumn('transfer', 'ppksaalma');
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
            $table->renameColumn('ppksaalma', 'transfer');
        });
    }
}
