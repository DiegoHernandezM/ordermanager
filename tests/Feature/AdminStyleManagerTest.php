<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminStyleManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminStyleManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de producto
     *
     * @return void
     */
    public function testCreateProduct()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "style",
            "method" => "GET",
            "path" => "/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminStyleManager();
        $manager->createNewStyle($request->data);
        $this->assertDatabaseHas('styles', ['id' => $request->data[0]->id]);
    }

    /**
     * Actualizar una prioridad
     *
     * @return void
     */
    public function testUpdateProduct()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "style",
            "method" => "GET",
            "path" => "/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminStyleManager();
        $manager->updateStyle($request->data);
        $this->assertDatabaseHas('styles', [
            'id'      => $request->data[0]->id,
            'style'   => $request->data[0]->style,
            'jdaDivision' => $request->data[0]->jdaDivision
        ]);
    }
}
