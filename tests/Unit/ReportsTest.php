<?php

namespace Tests\Unit;

use App\Repositories\CartonRepository;
use App\Repositories\OrderGroupRepository;
use App\Repositories\WaveRepository;
use App\Wave;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Estadisticas del dia para olas
     *
     * @return void
     */
    public function testGetWavesToday()
    {
        $this->withoutMiddleWare();
        $this->json('GET', 'api/v1/report/wave/today', [], ['Accept' => 'application/json'])
          ->assertStatus(200);
    }

    /**
     * Estadisticas de la semana para olas
     *
     * @return void
     */
    public function testGetWavesWeek()
    {
        $this->withoutMiddleWare();
        $this->json('GET', 'api/v1/report/wave/week', [], ['Accept' => 'application/json'])
          ->assertStatus(200);
    }

    /**
     * Estadistics de carton del dia
     *
     * @return void
     */
    public function testGetCartonsToday()
    {
        $this->withoutMiddleWare();
        $this->json('GET', 'api/v1/report/carton/today', [], ['Accept' => 'application/json'])
          ->assertStatus(200);
    }
}
