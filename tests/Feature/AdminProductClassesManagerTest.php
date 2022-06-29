<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductClassesManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductClassesManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de clase de producto
     *
     * @return void
     */
    public function testCreateClass()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productClasses",
            "method" => "GET",
            "path" => "/catalogues/classes/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductClassesManager();
        $manager->createNewProductClasses($request->data);
        $this->assertDatabaseHas('classes', ['id' => $request->data->id]);
    }

    /**
     * Actualizar una clase de producto
     *
     * @return void
     */
    public function testUpdateClass()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productClasses",
            "method" => "GET",
            "path" => "/catalogues/classes/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductClassesManager();
        $manager->updateProductClasses($request->data);
        $this->assertDatabaseHas('classes', [
            'id'      => $request->data->id,
            'jdaId'   => $request->data->jdaId,
            'jdaName' => $request->data->jdaName
        ]);
    }

}
