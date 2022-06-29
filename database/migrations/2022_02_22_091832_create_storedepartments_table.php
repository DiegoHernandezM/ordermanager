<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoredepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('store_id');
            $table->string('storeNumber');
            $table->unsignedInteger('department_id')->nullable();
            $table->string('departmentNumber')->nullable();
            $table->dateTime('block_until')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_departments');
    }
}
