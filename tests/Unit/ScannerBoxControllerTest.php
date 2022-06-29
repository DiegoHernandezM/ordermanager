<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScannerBoxControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * No Encontrado
     */
    public function testGetInfoBarCodeNotFound()
    {
        $this->json('GET', 'api/v1/scannerbox/C-00840784706', ['Accept' => 'application/json'])
            ->assertStatus(404)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Validacion de barcode
     */
    public function testGetInfoBarCodeBadRequest()
    {
        $this->json('GET', 'api/v1/scannerbox/.', ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * Obtener informacion de zpl
     */
    public function testGetInfoBarCode()
    {

        $this->json('GET', 'api/v1/scannerbox/00840784706', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
            ]);
    }

}
