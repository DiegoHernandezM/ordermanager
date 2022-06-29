<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Division;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class DivisionControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * @var $division
     * @test
     */
    protected $division;

    protected function setUp(): void
    {
        parent::setUp();
        $this->division = factory(Division::class)->create();
    }

    /**
     * @test
     * No autorizado
     */
    public function testAuthorization()
    {

        $this->json('GET', 'api/v1/divisions/all', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * @test
     * Obtener todas las divisiones
     */
    public function testGetAllDivisions()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/divisions/all', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
               ]);
    }

    /**
     * @test
     * Obtener todos los departamentos
     */
    public function testGetAllDepartments()
    {
        $this->withoutMiddleWare();

        $division = 1;
        $this->json('GET', 'api/v1/divisions/'.$division.'/departments', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener departamentos por division
     */
    public function testGetDepartmentsByDivision()
    {
        $this->withoutMiddleWare();
        $request = [
              "divisions" => "1",
              "divisions" => "3"
        ];

        $this->json('GET', 'api/v1/divisions/getdepartments', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

}
