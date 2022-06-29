<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminDepatrmentManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


class AdminDepartmentManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Nuevo departamento
     *
     * @return void
     */
    public function testCreateNewDepartment()
    {
        $array = [
          "resourceIds" => [1],
          "operation" => "new",
          "entity" => "department",
          "method" => "GET",
          "path" => "/catalogues/departments/1",
          "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminDepatrmentManager();
        $manager->createNewDepartment($request->data);
        $this->assertDatabaseHas('departments', ['id' => $request->data[0]->id]);

    }

    /**
     * Actualizar departamento
     *
     * @return void
     */
    public function testUpdateDepartment()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "department",
            "method" => "GET",
            "path" => "/catalogues/departments/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminDepatrmentManager();
        $manager->updateDepartment($request->data);
        $this->assertDatabaseHas('departments', [
            'id'      => $request->data[0]->id,
            'jda_id'   => $request->data[0]->jdaId,
            'jda_name' => $request->data[0]->jdaName
        ]);
    }

}
