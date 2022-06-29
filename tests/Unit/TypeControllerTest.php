<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TypeControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            'classes' => "1",
        ];

        $this->json('GET', 'api/v1/producttypes/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener los tipos de productos con clases
     */
    public function testAllWithClasses()
    {
        $this->withoutMiddleWare();

        $request = [
            "classes" => "1,2,3,4,5",
        ];

        $this->json('GET', 'api/v1/producttypes/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener los tipos de productos sin clases
     */
    public function testName()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/producttypes/all', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

}
