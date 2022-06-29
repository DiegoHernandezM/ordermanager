<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameApplicationNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('application_types', function(Blueprint $table) {
            $table->dropColumn('application_name');
        });
        Schema::table('application_types', function(Blueprint $table) {
            $table->string('action_name')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('application_types', function(Blueprint $table) {
            $table->dropColumn('action_name');
        });
        Schema::table('application_types', function(Blueprint $table) {
            $table->string('application_name')->nullable(true);
        });
    }
}
