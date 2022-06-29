<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductClassificationsControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            "divisions" => "1,2,3",
        ];

        $this->json('GET', 'api/v1/classifications/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener clasificaciones con divisiones
     */
    public function testGetWithDivisions()
    {
        $this->withoutMiddleWare();

        $request = [
            "divisions" => "1,2,3",
        ];

        $this->json('GET', 'api/v1/classifications/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener clasificaciones sin divisiones
     */
    public function testGetWithoutDivisions()
    {
        $this->withoutMiddleWare();
        $request = [
        ];

        $this->json('GET', 'api/v1/classifications/all', $request,['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }
}
