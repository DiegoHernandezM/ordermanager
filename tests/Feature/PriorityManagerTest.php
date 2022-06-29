<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductPriorityManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PriorityManagerTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * Prueba creacion de prioridad
     *
     * @return void
     */
    public function testCreatePriority()
    {
        $array = [
          "resourceIds" => [1],
          "operation" => "new",
          "entity" => "productPriority",
          "method" => "GET",
          "path" => "/catalogues/priorities/1",
          "withBody" => true
        ];

        $request = $this->getData($array);
        $productPriorityManager = new AdminProductPriorityManager();
        $productPriorityManager->createNewProductPriority($request->data);
        $this->assertDatabaseHas('product_priorities', ['id' => $request->data->id]);
    }

    /**
     * Actualizar una prioridad
     *
     * @return void
     */
    public function testUpdatePriority()
    {
        $array = [
          "resourceIds" => [1],
          "operation" => "update",
          "entity" => "productPriority",
          "method" => "GET",
          "path" => "/catalogues/priorities/1",
          "withBody" => true
        ];

        $request = $this->getData($array);
        $productPriorityManager = new AdminProductPriorityManager();
        $productPriorityManager->updateProductPriority($request->data);
        $this->assertDatabaseHas(
            'product_priorities',
            [
              'id'      => $request->data->id,
              'jdaId'   => $request->data->jdaId,
              'jdaName' => $request->data->jdaName
            ]
        );
    }
}
