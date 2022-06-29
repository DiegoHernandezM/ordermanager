<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminDivisionManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DivisionManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Crea nueva Division
     *
     * @return void
     */
    public function testCreateDivision()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "division",
            "method" => "GET",
            "path" => "/catalogues/divisions/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $divisionManager = new AdminDivisionManager();
        $divisionManager->createNewDivision(json_encode($request->data));
        $this->assertDatabaseHas('divisions', ['id' => $request->data[0]->id]);
    }

    /**
     * @test
     * Actualiza una division
     *
     * @return void
     */
    public function testUpdateDivision()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "division",
            "method" => "GET",
            "path" => "/catalogues/divisions/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $divisionManager = new AdminDivisionManager();
        $divisionManager->updateDivision(json_encode($request->data));
        $this->assertDatabaseHas('divisions', [
            'id'      => $request->data[0]->id,
            'jda_id'   => $request->data[0]->jdaId,
            'jda_name' => $request->data[0]->jdaName
        ]);
    }
}
