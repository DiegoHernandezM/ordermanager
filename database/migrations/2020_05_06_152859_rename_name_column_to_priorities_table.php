<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameNameColumnToPrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('priorities', function (Blueprint $table) {
            $table->renameColumn('name', 'label');
            $table->string('jda_id', 4)->nullable();
            $table->string('jda_name', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('priorities', function (Blueprint $table) {
            $table->renameColumn('label', 'name');
            $table->dropColumn('jda_id');
            $table->dropColumn('jda_name');
        });
    }
}
