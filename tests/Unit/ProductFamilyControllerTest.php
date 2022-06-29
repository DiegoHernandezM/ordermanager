<?php

namespace Tests\Unit;

use App\ProductFamily;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductFamilyControllerTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * @return mixed
     */
    public function createProductFamily()
    {
        return factory(ProductFamily::class)->create();
    }

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            'per_page' => 10,
        ];

        $this->json('GET', 'api/v1/productfamily/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener las familias de producto
     */
    public function testIndex()
    {
        $this->withoutMiddleWare();

        $request = [
            'per_page' => 10,
        ];

        $this->json('GET', 'api/v1/productfamily/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    /**
     * Obtener todos las familias de producto con division
     */
    public function testGetAllWithDivision()
    {
        $this->withoutMiddleWare();

        $request = [
            "divisions" => "6"
        ];

        $this->json('GET', 'api/v1/productfamily/getall', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener todos las familias de producto sin division
     */
    public function testGetAllWithoutDivision()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/productfamily/getall', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Buscar una familia de producto
     */
    public function testShow()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/productfamily/1', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Buscar una familia de producto no encontrada
     */
    public function testShowNotFound()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/productfamily/a', ['Accept' => 'application/json'])
            ->assertStatus(404)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Actualizar una familia de producto
     */
    public function testUpdate()
    {
        $this->withoutMiddleWare();
        $family = $this->createProductFamily();
        $family = $family->toArray();

        $request = [
            'id'    => $family['id'],
            'jdaId' => 'TEST',
            'jdaName'   => 'TEST',
            'label'     => 'TEST',
            'familiesId' => [1]
        ];

        $this->json('POST', 'api/v1/productfamily/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(200);
    }
}
