<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use App\Pallets;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class PalletsControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * @var $pallet
     * @test
     */
    protected $pallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pallet = factory(Pallets::class)->create();
    }

    /**
     * @test
     * No autorizado
     */
    public function testAuthorization()
    {

        $this->json('GET', 'api/v1/pallets/getwavespicking', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * @test
     * Crear orden
     */
   public function testGenerateOrder()
    {
        $user = User::find(1);
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        $request = [
            "token" => $token,
            "wave_id" => 9,
            "buffer" => 1,
            "origin" => 6,
            "cant" => 1,
        ];
        $this->json('POST', 'api/v1/pallets/staging/order', $request, ['Accept' => 'application/json', 'Authorization' => 'Bearer '.$token])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Crear pallet
     */
    public function testCreatePallet()
    {
        $this->withoutMiddleWare();
        $request = [
          "pallets" => [
                0   => [
                    "LPN"                 => "B00000949008",
                    "code"                => "21-004-012-A",
                ],
                1   => [
                    "LPN"                 => "B00001687953",
                    "code"                => "21-004-001-A",
                ],
          ]
        ];
        $this->json('POST', 'api/v1/pallets/staging/store', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Actualiza status a Moving
     */
    public function testStoreMoving(){

        $this->withoutMiddleWare();
        $request = [
            "pallets" => [
                0 => [
                    "LPN" => "D0189178131"
                ],
                1 => [
                    "LPN" => "U0189178127"
                ]
            ]
        ];

        $this->json('POST', 'api/v1/pallets/staging/moving', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * pallets enviados
     */
    public function testpalletsDispatched(){

        $this->withoutMiddleWare();
        $request = [
            "pallets" => [
                0 => 1,
                1 => 2,
                2 => 1234
            ]
        ];

        $this->json('POST', 'api/v1/pallets/staging/dispatch', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * inducción
     */
    public function testInduct(){

        $this->withoutMiddleWare();
        $request = [
            "pallet" => "B00001734862"
        ];

        $this->json('POST', 'api/v1/pallets/staging/induct', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener todos los pallets
     */
    public function testGetAllPallets(){

        $this->withoutMiddleWare();
        $this->json('GET', 'api/v1/pallets/getwavespicking', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallet por id
     */
    public function testShowByWave(){

        $this->withoutMiddleWare();
        $request = [
            "pallet_id" => 1
        ];
        $this->json('GET', 'api/v1/pallets/getinfobywave', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallets en staging por zona
     */
    public function testGetListStaging(){

        $this->withoutMiddleWare();
        $zonetype = 6;
        $this->json('GET', 'api/v1/pallets/staging/getlist?zonetype='.$zonetype, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallets en staging por zona o bin
     */
    public function testGetListStagingAll(){

        $this->withoutMiddleWare();
        $request = [
            "wave" => 5
        ];
        $this->json('GET', 'api/v1/pallets/staging/getliststaging/', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Verificar zona/code
     */
    public function testVerifyZone(){

        $this->withoutMiddleWare();
        $request = [
            "code" => "21-001-030-A",
            "bin" => "B00002651990"
        ];
        $this->json('GET', 'api/v1/pallets/staging/verifyZone/', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            "exists"=>true,
            "occupied"=> false,
            "wave" => 414
        ]);
    }

    /**
     * @test
     * Obtener pallets por orden
     */
    public function testGetPalletsOrder(){

        $this->withoutMiddleWare();
        $request = [
            "search" => "B00002282754"
        ];
        $this->json('GET', 'api/v1/pallets/staging/getpallets/', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallets en staging
     */
    public function testGetPalletsFromStaging(){

        $this->withoutMiddleWare();
        $this->json('GET', 'api/v1/pallets/staging/manager/', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallets por LPN
     */
    public function testGetPalletByLpn(){

        $this->withoutMiddleWare();
        $request = [
            "lpn" => "B00001733567"
        ];
        $this->json('GET', 'api/v1/pallets/staging/pallet/', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    /**
     * @test
     * Obtener pallets por buffer
     */
    public function testGetPalletsBuffer(){

        $this->withoutMiddleWare();
        $request = [
            "zoneType" => 1
        ];

        $this->json('GET', 'api/v1/pallets/staging/buffer/', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data" => []
            ]);
    }

    /**
     * @test
     * Mostrar por id
     */
    public function testShowByStaging(){

        $this->withoutMiddleWare();
        $id = 1;

        $this->json('GET', 'api/v1/pallets/staging/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "data"  => [],
                "total" => 0
            ]);
    }

    /**
     * @test
     * Obtener pallets
     */
    public function testGetNext(){

        $this->withoutMiddleWare();
        $request = [
            "wave" => 226
        ];

        $this->json('GET', 'api/v1/pallets/getnext',$request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Show pallet por id
     */
    public function testShow(){

        $this->withoutMiddleWare();
        $id = 1;

        $this->json('GET', 'api/v1/pallets/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallet por zona
     */
    public function testShowPalletZone(){

        $this->withoutMiddleWare();
        $id = 1;

        $this->json('GET', 'api/v1/pallets/zone/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Obtener pallet por id zona
     */
    public function showPalletByIdZone(){

        $this->withoutMiddleWare();
        $id = 67;

        $this->json('GET', 'api/v1/pallets/getbyzone/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }
}
