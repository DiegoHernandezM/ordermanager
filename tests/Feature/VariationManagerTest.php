<?php

namespace Tests\Feature;

use App\Managers\Admin\AdminVariationManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VariationManagerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * Crea nueva variation
     *
     * @return void
     */
    public function testCreateVariation()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "new",
            "entity" => "variation",
            "method" => "GET",
            "path" => "/variations/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $variationManager = new AdminVariationManager();
        $variationManager->createNewVariation(json_encode($request->data));
        $this->assertDatabaseHas('variations', ['id' => $request->data[0]->id]);
    }

    /**
     * @test
     * Actualiza una variation
     *
     * @return void
     */
    public function testUpdateVariation()
    {
        $array = [
            "resourceIds" => [1],
            "operation" => "update",
            "entity" => "variation",
            "method" => "GET",
            "path" => "/variations/1",
            "withBody" => true
        ];

        $request = $this->getData($array);
        $variationManager = new AdminVariationManager();
        $variationManager->updateVariation(json_encode($request));
        $this->assertDatabaseHas('variations', [
            'id'            => $request->data[0]->id,
            'sku'           => $request->data[0]->sku,
            'style_id'      => $request->data[0]->style_id,
            'size_id'       => $request->data[0]->product_size_id,
            'jdaSize'       => $request->data[0]->jda_size,
            'color_id'      => $request->data[0]->product_color_id,
            'jdaColor'      => $request->data[0]->jda_color,
            'priority_id'   => $request->data[0]->priority_id,
            'jdaPriority'   => $request->data[0]->jda_priority,
        ]);
    }
}
