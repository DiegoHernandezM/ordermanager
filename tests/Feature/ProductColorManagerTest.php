<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductColorManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductColorManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Crea nuevo product color
     *
     * @return void
     */
    public function testCreateProductColor()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productColor",
            "method" => "GET",
            "path" => "/catalogues/product-colors/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $productColorManager = new AdminProductColorManager();
        $productColorManager->createNewProductColor(json_encode($request->data));
        $this->assertDatabaseHas('product_colors', ['id' => $request->data[0]->id]);
    }

    /**
     * @test
     * Actualiza un product color
     *
     * @return void
     */
    public function testUpdateColor()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productColor",
            "method" => "GET",
            "path" => "/catalogues/product-colors/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $productColorManager = new AdminProductColorManager();
        $productColorManager->updateProductColor(json_encode($request->data));
        $this->assertDatabaseHas('product_colors', [
            'id'      => $request->data[0]->id,
            'jdaId'   => $request->data[0]->jdaId,
            'jdaName' => $request->data[0]->jdaName
        ]);
    }
}
