<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminRouteManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RouteManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Crea nueva ruta
     *
     * @return void
     */
    public function testCreateRoute()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "route",
            "method" => "GET",
            "path" => "/routes/1",
            "withBody" => true
        ];

        $request = $this->getData($array, '/shops');
        $dataRequest[] = $request->data;
        $routeManager = new AdminRouteManager();
        $routeManager->createNewRoute(json_encode($dataRequest));
        $this->assertDatabaseHas('routes', ['id' => $dataRequest[0]->id]);
    }

    /**
     * @test
     * Actualiza una ruta
     *
     * @return void
     */
    public function testUpdateRoute()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "route",
            "method" => "GET",
            "path" => "/routes/1",
            "withBody" => true
        ];

        $request = $this->getData($array, '/shops');
        $dataRequest[] = $request->data;
        $routeManager = new AdminRouteManager();
        $routeManager->updateRoute(json_encode($dataRequest));
        $this->assertDatabaseHas('routes', [
            'id'      => $dataRequest[0]->id,
            'name'   => $dataRequest[0]->name,
            'description' => $dataRequest[0]->description,
            'color'     => $dataRequest[0]->color->hexadecimalColor
        ]);
    }
}
