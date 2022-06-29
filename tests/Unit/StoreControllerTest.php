<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Store;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StoreControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var $store
     * @test
     */
    protected $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = factory(Store::class)->create();
    }

    /**
     * @test
     * No autorizado
     */
    public function testAuthorization()
    {

        $this->json('GET', 'api/v1/stores/all', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * @test
     * Obtener todas las tiendas
     */
    public function testGetAllStores()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/stores/all', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener tienda por id
     */
    public function testGetStoreById()
    {
        $this->withoutMiddleWare();

        $id = 1;
        $this->json('GET', 'api/v1/stores/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Crear tienda
     */
    public function testCreateStore()
    {
        $this->withoutMiddleWare();

        $request = $this->store;
        $this->json('POST', 'api/v1/stores/create', $request->toArray(), ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Actualiza tienda
     */
    public function testUpdateStore()
    {
        $this->withoutMiddleWare();

        $request = [
            'id'             => 1,
            'number'         => 1,
            'ranking'        => 1,
            'name'           => 'Store Test Updated',
            'sorter_ranking' => 1,
            'route_id'       => 1,
            'pbl_ranking'    => 0,
            'position'       => 0,
            'status'         => 1,
        ];
        $this->json('POST', 'api/v1/stores/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Elimina tienda
     */
    public function testDeleteStore()
    {
        $this->withoutMiddleWare();

        $id = 1;
        $this->json('GET', 'api/v1/stores/delete/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

}
