<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductClassificationManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductClassificationManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Crea nueva Clasificación
     *
     * @return void
     */
    public function testCreateProductClassification()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productClassificacion",
            "method" => "GET",
            "path" => "/catalogues/product-classifications/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $productClassificationManager = new AdminProductClassificationManager();
        $productClassificationManager->createNewClassification(json_encode($request->data));
        $this->assertDatabaseHas('product_classifications', ['id' => $request->data->id]);
    }

    /**
     * @test
     * Actualiza una Clasificacón
     *
     * @return void
     */
    public function testUpdateProductClassification()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productClassificacion",
            "method" => "GET",
            "path" => "/catalogues/product-classifications/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $data[] = $request->data;
        $productClassificationManager = new AdminProductClassificationManager();
        $productClassificationManager->updateClassification(json_encode($data));
        $this->assertDatabaseHas('product_classifications', [
            'id'      => $data[0]->id,
            'jdaId'   => $data[0]->jdaId,
            'jdaName' => $data[0]->jdaName
        ]);
    }
}
