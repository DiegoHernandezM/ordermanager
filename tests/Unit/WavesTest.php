<?php

namespace Tests\Unit;

use App\OrderGroup;
use App\Repositories\OrderGroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WavesTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * Crea una ola
     *
     * @return void
     */
    public function testCreateWave()
    {
        $this->withoutMiddleWare();
        $request = [
          "order_group_id" => 90,
          "business_rules" => [
            "divisions" => [1],
            "divisionsNames" => ["Mujer"],
            "excludedClassifications" => [1],
            "excludedClassificationsNames" => [1],
            "excludedDepartments" => [],
            "excludedDepartmentsNames" => [],
            "excludedFamilies" => [],
            "excludedFamiliesNames" => [],
            "excludedRoutes" => [],
            "excludedRoutesNames" => [],
          ],
          "description" => "WAVETEST"
        ];
    
        $this->json('POST', 'api/v1/waves/create', $request, ['Accept' => 'application/json'])
          ->assertStatus(201);

        $this->assertDatabaseHas('waves', [
            'description' => 'WAVETEST',
            'order_group_id' => 90
          ]);
    }
}
