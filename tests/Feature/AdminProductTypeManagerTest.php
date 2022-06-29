<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductTypeManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductTypeManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de un tipo de producto
     *
     * @return void
     */
    public function testCreateType()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productType",
            "method" => "GET",
            "path" => "/catalogues/product-types/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductTypeManager();
        $manager->createNewProductType($request->data);
        $this->assertDatabaseHas('product_types', ['id' => $request->data->id]);
    }

    /**
     * Actualizar un tipo de producto
     *
     * @return void
     */
    public function testUpdateType()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productType",
            "method" => "GET",
            "path" => "/catalogues/product-types/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductTypeManager();
        $manager->updateProductType($request->data);
        $this->assertDatabaseHas('product_types', [
            'id'      => $request->data->id,
            'jdaId'   => $request->data->jdaId,
            'jdaName' => $request->data->jdaName
        ]);
    }
}
