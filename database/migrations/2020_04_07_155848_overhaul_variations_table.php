<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OverhaulVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('stock_blocked', 'discount', 'price', 'weight', 'order', 'regular_price', 'cost', 'color_updated_at', 'last_pieces', 'sync_active', 'original_price', 'liquidation_stock', 'liquidation_stock_blocked', 'ecom_size_id', 'price_usd', 'discount_usd', 'regular_price_usd', 'original_price_usd', 'conversion_type_usd')) {
            Schema::table('variations', function (Blueprint $table) {            
                $table->dropColumn('stock_blocked');
                $table->dropColumn('discount');
                $table->dropColumn('price');
                $table->dropColumn('weight');
                $table->dropColumn('order');
                $table->dropColumn('regular_price');
                $table->dropColumn('cost');
                $table->dropColumn('color_updated_at');
                $table->dropColumn('last_pieces');
                $table->dropColumn('sync_active');
                $table->dropColumn('original_price');
                $table->dropColumn('liquidation_stock');
                $table->dropColumn('liquidation_stock_blocked');
                $table->dropColumn('ecom_size_id');
                $table->dropColumn('price_usd');
                $table->dropColumn('discount_usd');
                $table->dropColumn('regular_price_usd');
                $table->dropColumn('original_price_usd');
                $table->dropColumn('conversion_type_usd');
            });
        }
        Schema::table('variations', function (Blueprint $table) {                    
            $table->unsignedInteger('style_id')->nullable();
            $table->unsignedInteger('jdaSize')->nullable();
            $table->unsignedInteger('size_id')->nullable();
            $table->unsignedInteger('jdaColor')->nullable();            
            $table->string('jdaPriority')->nullable();
            $table->unsignedInteger('priority_id')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variations', function (Blueprint $table) {
            $table->dropColumn('style_id');
            $table->dropColumn('jdaSize');
            $table->dropColumn('size_id');
            $table->dropColumn('jdaColor');            
            $table->dropColumn('jdaPriority');
            $table->dropColumn('priority_id');
            $table->dropColumn('product_id');            
            $table->integer('stock_blocked')->nullable(true);
            $table->integer('discount')->nullable(true);
            $table->decimal('price')->nullable(true);
            $table->decimal('weight')->nullable(true);            
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

        });
    }
}
