<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('metadescription');
            $table->string('description');
            $table->string('internal_reference');
            $table->boolean('active')->default(1);
            $table->tinyInteger('vat')->unsigned();
            $table->text('more_info');
            $table->string('brand');
            $table->string('provider');
            $table->string('family');
            $table->string('subcategory');
            $table->unsignedInteger('grill_id');
            $table->string('department');
            $table->string('classification');
            $table->date('expiration_date');
            $table->string('label_title', 50);
            $table->date('label_start_date');
            $table->date('label_end_date');
            $table->string('label_background_color', 8);
            $table->string('label_text_color', 8);
            $table->boolean('jda_sync_active');
            $table->boolean('activation_disabled');
            $table->integer('last_pieces');
            $table->datetime('new_at');
            $table->integer('discount_percent');
            $table->integer('department_code');
            $table->integer('year_code');
            $table->integer('month_code');
            $table->string('parent_name');
            $table->string('category_name');
            $table->decimal('user_price');
            $table->integer('user_discount');
            $table->string('sat_key');
            $table->string('sat_unity');
            $table->string('harmonized_tariff');
            $table->string('parent_name_en');
            $table->string('category_name_en');
            $table->string('name_en');
            $table->string('colors_en');
            $table->string('colors_es');
            $table->decimal('adjusted_price');
            $table->boolean('historic');
            $table->decimal('user_price_usd');
            $table->decimal('adjusted_price_usd');
            $table->integer('discount_percent_usd');
            $table->integer('user_discount_usd');
            $table->boolean('conversion_type_usd');
            $table->integer('price_status');
            $table->unsignedInteger('promotion_id');
            $table->boolean('available_international');
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
        Schema::dropIfExists('products');
    }
}
