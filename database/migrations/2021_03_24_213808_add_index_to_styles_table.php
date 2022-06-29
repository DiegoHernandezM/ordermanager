<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToStylesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('division_id');
            $table->index('classification_id');
            $table->index('family_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropIndex('department_id');
            $table->dropIndex('division_id');
            $table->dropIndex('classification_id');
            $table->dropIndex('family_id');
        });
    }
}
