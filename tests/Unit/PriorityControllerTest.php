<?php

namespace Tests\Unit;

use App\Priority;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PriorityControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $priority;

    /**
     * @return mixed
     */
    public function createPriority()
    {
        return factory(Priority::class)->create();
    }


    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            'per_page' => 10,
            'order' => 'id',
            'sort' => 'desc',
        ];

        $this->json('GET', 'api/v1/priority/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Validacion de request en index
     */
    public function testValidateIndex()
    {
        $this->withoutMiddleWare();

        $request = [
            'per_page' => 'abc',
            'order' => 'id',
            'sort' => 'desc',
        ];

        $this->json('GET', 'api/v1/priority/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status" => 'fail'
            ]);
    }

    /**
     * Obtener todas las prioridades
     */
    public function testIndex()
    {
        $this->withoutMiddleWare();

        $request = [
            'per_page' => 10,
            'order' => 'id',
            'sort' => 'desc',
        ];

        $this->json('GET', 'api/v1/priority/all', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    /**
     * Validacion en la creacion de una prioridad
     */
    public function testValidateStore()
    {
        $this->withoutMiddleWare();

        $request = [
            'name' => 'T'
        ];

        $this->json('POST', 'api/v1/priority/create', $request, ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Crear una prioridad
     */
    public function testStore()
    {
        $this->withoutMiddleWare();

        $request = [
            'name' => 'TEST'
        ];

        $this->json('POST', "api/v1/priority/create", $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "priority" => true
            ]);
    }

    /**
     * Validacion de mostrar una prioridad
     */
    public function testValidateShow()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/priority/show/abc', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status" => 'fail'
            ]);
    }

    /**
     * Mostrar una prioridad
     */
    public function testShow()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/priority/show/1', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "priority" => true
            ]);
    }

    /**
     * Validacion de modificar una prioridad
     */
    public function testValidateUpdate()
    {
        $this->withoutMiddleWare();

        $request = [
            "id" => 'abc',
            'name' => 'TEST UPDATE',
            'prioritiesId' => [1]
        ];

        $this->json('POST', 'api/v1/priority/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Modificar una prioridad
     */
    public function testUpdate()
    {
        $this->withoutMiddleWare();

        $priority = $this->createPriority();
        $priority = $priority->toArray();

        $request = [
            "id" => $priority['id'],
            'name' => 'TEST UPDATE',
            'prioritiesId' => [1]
        ];

        $this->json('POST', 'api/v1/priority/update', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "priority" => true
            ]);
    }

    /**
     * Validacion de eliminacion de una prioridad
     */
    public function testValidateDestroy()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/priority/delete/abc', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status" => 'fail'
            ]);
    }

    /**
     * Eliminacion de una prioridad
     */
    public function testDestroy()
    {
        $this->withoutMiddleWare();

        $priority = $this->createPriority();
        $priority = $priority->toArray();


        $this->json('GET', 'api/v1/priority/delete/'.$priority['id'], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "message" => true
            ]);
    }


}
