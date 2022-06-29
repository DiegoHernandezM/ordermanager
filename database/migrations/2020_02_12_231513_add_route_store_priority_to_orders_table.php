<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRouteStorePriorityToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('routeDescription')->nullable(true);
            $table->integer('routePriority');
            $table->integer('route');
            $table->string('storeDescription')->nullable(true);
            $table->integer('storePriority');
            $table->integer('store');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('routeDescription');
            $table->dropColumn('routePriority');
            $table->dropColumn('storeDescription');
            $table->dropColumn('storePriority');
            $table->dropColumn('store');
        });
    }
}
