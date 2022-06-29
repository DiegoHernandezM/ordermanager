<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNullableColumnsApplicationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('application_types', function (Blueprint $table) {
            $table->string('action_name')->nullable()->change();
            $table->string('description')->nullable()->change();
            $table->string('access_type')->nullable()->change();
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
        Schema::table('application_types', function (Blueprint $table) {
            $table->string('action_name')->change();
            $table->string('description')->change();
            $table->string('access_type')->change();
        });
    }
}
