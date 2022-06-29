<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Route;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RouteControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * @var $route
     * @test
     */
    protected $route;

    protected function setUp(): void
    {
        parent::setUp();
        $this->route = factory(Route::class)->create();
    }

    /**
     * @test
     * No autorizado
     */
    public function testAuthorization()
    {

        $this->json('GET', 'api/v1/routes/getall', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * @test
     * Obtener todas las rutas
     */
    public function testGetAllRoutes()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/routes/getall', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener todas las rutas con paginación
     */
    public function testGetAllRoutesPaginated()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/routes/getallroutes', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener ruta por Id
     */
    public function testGetRouteById()
    {
        $this->withoutMiddleWare();
        $id = 1;

        $this->json('GET', 'api/v1/routes/'.$id.'', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Crear ruta
     */
    public function testCreateRoute()
    {
        $this->withoutMiddleWare();

        $request = factory(Route::class)->create();
        $this->json('POST', 'api/v1/routes/create', $request->toArray(), ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Actualiza ruta
     */
    public function testUpdateRoute()
    {
        $this->withoutMiddleWare();

        $request = [
            "id"           => 1,
            "name"         => "Route test updated",
            "description"  => "is a test updated",
            "color"        => "#ffffff",
        ];
        $this->json('POST', 'api/v1/routes/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Elimina una ruta
     */
    public function testDeleteRoute()
    {
        $this->withoutMiddleWare();

        $id = 1;
        $this->json('GET', 'api/v1/routes/delete/'.$id.'', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }
}
