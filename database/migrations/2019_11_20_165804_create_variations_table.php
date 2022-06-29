<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id')->nullable(true);
            $table->string('sku');
            $table->string('name')->nullable(true);
            $table->string('color_id')->nullable(true);
            $table->integer('stock')->nullable(true);
            $table->integer('stock_blocked')->nullable(true);
            $table->integer('discount')->nullable(true);
            $table->decimal('price')->nullable(true);
            $table->decimal('weight')->nullable(true);
            $table->boolean('active')->nullable(true);
            $table->integer('order')->nullable(true);
            $table->decimal('regular_price')->nullable(true);
            $table->decimal('cost')->nullable(true);
            $table->dateTime('color_updated_at')->nullable(true);
            $table->integer('last_pieces')->nullable(true);
            $table->integer('sync_active')->nullable(true);
            $table->decimal('original_price')->nullable(true);
            $table->integer('liquidation_stock')->nullable(true);
            $table->integer('liquidation_stock_blocked')->nullable(true);
            $table->integer('ecom_size_id')->nullable(true);
            $table->decimal('price_usd')->nullable(true);
            $table->decimal('discount_usd')->nullable(true);
            $table->decimal('regular_price_usd')->nullable(true);
            $table->decimal('original_price_usd')->nullable(true);
            $table->boolean('conversion_type_usd')->nullable(true);
        
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
        Schema::dropIfExists('variations');
    }
}
