<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductProviderControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $this->json('GET', 'api/v1/productprovider/all', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener todos los proveedores
     */
    public function testIndex()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/productprovider/all', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }
}
