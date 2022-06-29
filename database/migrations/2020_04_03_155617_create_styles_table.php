<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStylesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('styles', function (Blueprint $table) {
            $table->bigIncrements('id');    
            $table->timestamps();
            $table->boolean('deleted')->default(false);
            $table->string('style');
            $table->unsignedInteger("jdaDivision")->nullable();
            $table->unsignedInteger("jdaDepartment")->nullable();
            $table->unsignedInteger("jdaClass")->nullable();
            $table->unsignedInteger("jdaType")->nullable();
            $table->unsignedInteger("jdaClassification")->nullable();
            $table->unsignedInteger("jdaFamily")->nullable();
            $table->unsignedInteger("jdaBrand")->nullable();
            $table->unsignedInteger("jdaProvider")->nullable();
            $table->unsignedInteger("division_id")->nullable();
            $table->unsignedInteger("department_id")->nullable();
            $table->unsignedInteger("class_id")->nullable();
            $table->unsignedInteger("type_id")->nullable();
            $table->unsignedInteger("classification_id")->nullable();
            $table->unsignedInteger("family_id")->nullable();
            $table->unsignedInteger("brand_id")->nullable();
            $table->unsignedInteger("provider_id")->nullable();
            $table->string("description")->nullable();
            $table->string("satCode")->nullable();
            $table->string("satUnit")->nullable();
            $table->decimal("publicPrice", 8, 2)->nullable();
            $table->decimal("originalPrice", 8, 2)->nullable();
            $table->decimal("regularPrice", 8, 2)->nullable();
            $table->decimal("publicUsdPrice", 8, 2)->nullable();
            $table->decimal("publicQtzPrice", 8, 2)->nullable();
            $table->decimal("cost", 8, 2)->nullable();
            $table->boolean("active")->default(true);
            $table->boolean("international")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('styles');
    }
}
