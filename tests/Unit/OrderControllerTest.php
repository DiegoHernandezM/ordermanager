<?php

namespace Tests\Unit;

use App\Order;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = factory(Order::class)->create();
    }


    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            "per_page" => 10
        ];

        $this->json('GET', 'api/v1/orders/get', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener todas las ordenes paginadas
     */
    public function testGetAll()
    {
        $this->withoutMiddleWare();

        $request = [
            "per_page" => 10
        ];

        $this->json('GET', 'api/v1/orders/get', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }
}
