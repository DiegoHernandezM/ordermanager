<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminProductFitManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductFitManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Prueba creacion de product_fit
     *
     * @return void
     */
    public function testCreateProductFit()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "productFit",
            "method" => "GET",
            "path" => "/catalogues/fits/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductFitManager();
        $manager->createNewProductFit($request->data);
        $this->assertDatabaseHas('product_fits', ['id' => $request->data->id]);
    }

    /**
     * Actualizar una talla
     *
     * @return void
     */
    public function testUpdateFit()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "productFit",
            "method" => "GET",
            "path" => "/catalogues/fits/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $manager = new AdminProductFitManager();
        $manager->updateProductFit($request->data);
        $this->assertDatabaseHas('product_fits', [
            'id'      => $request->data->id,
            'jdaId'   => $request->data->id,
            'jdaName' => $request->data->name
        ]);
    }
}
