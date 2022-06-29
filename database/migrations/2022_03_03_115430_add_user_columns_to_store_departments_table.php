<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserColumnsToStoreDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_departments', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->string('user_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_departments', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('user_name');
        });
    }
}
