<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllocationgroupToOrderGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->unsignedInteger('allocationgroup')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_groups', function (Blueprint $table) {
            $table->dropColumn('allocationgroup');
        });
    }
}
