<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnsToCartonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartons', function (Blueprint $table) {
            $table->dateTime('audit_init')->nullable();
            $table->dateTime('audit_end')->nullable();
            $table->unsignedInteger('authorized_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartons', function (Blueprint $table) {
            $table->dropColumn('audit_init');
            $table->dropColumn('audit_end');
            $table->dropColumn('authorized_by');
        });
    }
}
