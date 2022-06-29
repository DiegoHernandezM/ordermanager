<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->boolean('active')->default(1);
            $table->integer('order');
            $table->unsignedInteger('depends_id');
            $table->unsignedInteger('promotion_id');
            $table->string('keywords');
            $table->string('description');
            $table->string('meta_product_description');
            $table->timestamps();
        });
    }
}
