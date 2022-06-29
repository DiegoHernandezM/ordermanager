<?php

namespace Tests\Unit;


use App\Carton;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CartonControllerTest extends TestCase
{
    use DatabaseTransactions;


    protected $carton;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carton = factory(Carton::class)->create();
    }

    /**
     * No autorizado
     */
    public function testAuthorization()
    {
        $request = [
            "paginated" => "false",
            "size" => 1,
            "area" => 1
        ];

        $this->json('GET', 'api/v1/cartons/get', $request, ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener todos los cartones
     */
    public function testGetAll()
    {
        $this->withoutMiddleWare();

        $request = [
            "area" => 1
        ];

        $this->json('GET', 'api/v1/cartons/get', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "cartons" => true
            ]);
    }

    /**
     * Test obtener etiqueta
     */
    public function testGetZpl()
    {
        $this->withoutMiddleWare();

        $id = 1;

        $this->json('GET', 'api/v1/cartons/zpl/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    /**
     * Test demo de cartones
     */
    public function testDemo()
    {
        $this->withoutMiddleWare();

        $request = [
            "wave" => 1,
        ];

        $this->json('GET', 'api/v1/cartons/demo', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Test resend cartons con ola
     */
    public function testResendCartons()
    {
        $this->withoutMiddleWare();

        $request = [
            "wave" => 9
        ];

        $this->json('GET', 'api/v1/cartons/resend', $request, ['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    /**
     * Test resend cartons con barcode
     */
    public function testResendCartonsBoxId()
    {
        $this->withoutMiddleWare();

        $request = [
            "boxId" => "C-19395212434",
        ];

        $this->json('GET', 'api/v1/cartons/resend', $request, ['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    /**
     * Obtener detalle
     */
    public function testGetDetailCarton()
    {
        $this->withoutMiddleWare();

        $request = [
            "wave_id" => 9,
        ];

        $this->json('GET', 'api/v1/cartons/find/'.$this->carton->id, $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

    /**
     * Detalle de caton por ola
     */
    public function testGetCartonsWave()
    {
        $this->withoutMiddleWare();

        $request = [
            "per_page" => 1
        ];

        $this->json('GET', 'api/v1/cartons/wave/9', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }
}
