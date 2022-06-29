<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductSizeManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductSizeManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de una talla de prodcuto
     *
     * @return void
     */
    public function testCreateSize()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productSize",
            "method" => "GET",
            "path" => "/catalogues/product-sizes/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductSizeManager();
        $manager->createNewProductSize($request->data);
        $this->assertDatabaseHas('product_sizes', ['id' => $request->data[0]->id]);
    }

    /**
     * Actualizar una talla de producto
     *
     * @return void
     */
    public function testUpdateSize()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productSize",
            "method" => "GET",
            "path" => "/catalogues/product-sizes/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductSizeManager();
        $manager->updateProductSize($request->data);
        $this->assertDatabaseHas('product_sizes', [
            'id'      => $request->data[0]->id,
            'jdaId'   => $request->data[0]->jdaId,
            'jdaName' => $request->data[0]->jdaName
        ]);
    }
}
