<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductFabricManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductFabricManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de fabrica de producto
     *
     * @return void
     */
    public function testCreateFabric()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productFabric",
            "method" => "GET",
            "path" => "/catalogues/fabrics/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductFabricManager();
        $manager->createNewProductFabric($request->data);
        $this->assertDatabaseHas('product_fabrics', ['id' => $request->data->id]);
    }

    /**
     * Actualizar una fabrica de producto
     *
     * @return void
     */
    public function testUpdateFabric()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productFabric",
            "method" => "GET",
            "path" => "/catalogues/fabrics/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductFabricManager();
        $manager->updateProductFabric($request->data);
        $this->assertDatabaseHas('product_fabrics', [
            'id'      => $request->data->id,
            'jdaId'   => $request->data->id,
            'jdaName' => $request->data->name
        ]);
    }
}
