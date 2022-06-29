<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ZoneControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $this->json('GET', 'api/v1/zones/getallzonetypes', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtiene todas las zonas
     */
    public function testGetAllZoneTypes()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/zones/getallzonetypes', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Crear una zona
     */
    public function testCreateZone()
    {
        $this->withoutMiddleWare();

        $request = [
            'zone_type_id' => 2,
            'pallet_id' => 1,
            'description' => 'Buffer S3',
            'code' => 'B-002'
        ];

        $this->json('POST', 'api/v1/zones/createzone', $request, ['Accept' => 'application/json'])
            ->assertStatus(201)
            ->assertJson([
            ]);
    }

    /**
     * Crear una zona con validacion de datos
     */
    public function testCreateZoneValidate()
    {
        $this->withoutMiddleWare();

        $request = [
            'zone_type_id' => 2,
            'pallet_id' => 1,
            'description' => 'Buffer S3'
            ];

        $this->json('POST', 'api/v1/zones/createzone', $request, ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                'message'   => true
            ]);
    }

    /**
     * Obtiene todas las zonas por staging
     */
    public function testGetZones()
    {
        $this->withoutMiddleWare();

        $request = [
            'staging' => 1,
        ];

        $this->json('GET', 'api/v1/zones/getzones', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtiene todas las zonas paginadas
     */
    public function testGetZonesPaginated()
    {
        $this->withoutMiddleWare();


        $this->json('GET', 'api/v1/zones/getzones', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener una zona
     */
    public function testShow()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/zones/1', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Actualizazion de zona con validacion
     */
    public function testUpdateValidate()
    {
        $this->withoutMiddleWare();

        $request = [
            'var' => "foo",
        ];

        $this->json('POST', 'api/v1/zones/1/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Actualizazion de zona
     */
    public function testUpdate()
    {
        $this->withoutMiddleWare();

        $request = [
            'code' => "B-3432",
            'zone_type_id' => 1,
            'zoneTypeId' => 1
        ];

        $this->json('POST', 'api/v1/zones/1/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Actualizazion de zona no encontrada
     */
    public function testUpdateNotFound()
    {
        $this->withoutMiddleWare();

        $request = [
            'code' => "B-3432",
            'zone_type_id' => 1,
            'zoneTypeId' => 1
        ];

        $this->json('POST', 'api/v1/zones/a/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(404)
            ->assertJson([
                'message' => true
            ]);
    }

    /**
     * Eliminar una zona
     */
    public function testDelete()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/zones/1', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener las olas por zona
     */
    public function testGetNameWaves()
    {
        $this->withoutMiddleWare();

        $request = [
            'zoneType' => 1
        ];

        $this->json('GET', 'api/v1/zones/getnamewavezone', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Obtener los estilos en buffer
     */
    public function testGetStylesBuffer()
    {
        $this->withoutMiddleWare();

        $request = [
            'style' => 1
        ];

        $this->json('GET', 'api/v1/zones/getstylesbuffer', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }
}
