<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminStoreManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminStoreManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de prioridad
     *
     * @return void
     */
    public function testCreateStore()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "store",
            "method" => "GET",
            "path" => "/1",
            "withBody" => true
        ];

        $request = $this->getData($array, '/shops');
        $manager = new AdminStoreManager();
        $manager->createNewStore($request->data);
        $this->assertDatabaseHas('stores', ['id' => $request->data->id]);
    }
}
