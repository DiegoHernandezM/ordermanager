<?php

namespace Tests\Unit;

use App\OrderGroup;
use App\Repositories\OrderGroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderGroupTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * Crea una orden de surtido
     *
     * @return void
     */
    public function testCreateOrderGroup()
    {
        $this->withoutMiddleWare();
        $request = [
          "reference"    => "OG1",
          "local"        => 10110,
          "allocation"   => "aloc1",
          "orders" => [
            [
              "id"       => 123,
              "store"    => 10111,
              "sku"      => 1700002,
              "pieces"   => 100,
              "prepacks" => 50,
              "ppk"      => 2
            ],
            [
              "id"       => 124,
              "store"    => 10125,
              "sku"      => 1700003,
              "pieces"   => 100,
              "prepacks" => 50,
              "ppk"      => 2
            ],
          ]
        ];
    
        $this->json('POST', 'api/v1/ordergroup', $request, ['Accept' => 'application/json'])
          ->assertStatus(200);
         
        $this->assertDatabaseHas('order_groups', [
            'reference' => 'OG1',
            'allocation' => 'aloc1'
          ]);
    }
    /**
     * Probar lista de ordenes de surtido para wave form
     *
     * @return void
     */
    public function testGetCurrentWeekNoPaging()
    {
        $this->withoutMiddleWare();
        $request = [
          "paginated" => "false"
        ];

        $this->json('GET', 'api/v1/ordergroups/getcurrentweek', $request, ['Accept' => 'application/json'])
          ->assertStatus(200)
          ->assertJsonStructure(OrderGroup::$currentWeekJson);
    }

    public function testGetCurrentWeekPaging()
    {
        $this->withoutMiddleWare();
        $request = [
          "paginated" => "true",
          "size"      => 10,
        ];

        $this->json('GET', 'api/v1/ordergroups/getcurrentweek', $request, ['Accept' => 'application/json'])
          ->assertStatus(200)
          ->assertJsonStructure([
            "current_page",
            "data" => OrderGroup::$currentWeekJson,
            "from",
            "path",
            "per_page",
            "to"
          ]);
    }

    /**
     * Obtiene Lines de ordergroup con filtros por ola y division
     *
     * @return void
     */
    public function testGetLinesFiltered()
    {
        $this->withoutMiddleWare();
        $request = [
          "division" => 1,
          "order_group" => 1
        ];
    
        $this->json('GET', 'api/v1/ordergroups/getlines', $request, ['Accept' => 'application/json'])
          ->assertStatus(200)
          ->assertJsonStructure([[
            "id",
            "store_id",
            "slots",
            "active",
            "created_at",
            "updated_at",
            "order_group_id",
            "merc_id",
            "routeDescription",
            "routePriority",
            "storeDescription",
            "storePriority",
            "storeNumber",
            "routeNumber",
            "status",
            "storePosition",
            "pieces",
            "store",
            "storeRoute"
          ]]);
    }

    /**
     * Prueba funcion de encontrar lines con filtro por ordergroup, sku, estilo y proveedor
     *
     * @return void
     */
    public function testGetOrderGroupSkuDetail()
    {
        $this->withoutMiddleWare();
        $request = [
          "order_group" => 2,
          "sku" => 1877361
        ];
    
        $this->json('GET', 'api/v1/ordergroups/getskudetail', $request, ['Accept' => 'application/json'])
          ->assertStatus(200)
          ->assertJson([["sku" => true]]);
    }
}
