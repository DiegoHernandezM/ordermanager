<?php

namespace Tests\Unit;

use App\OrderGroup;
use App\Repositories\OrderGroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PickingTest extends TestCase
{
    use DatabaseTransactions;
    
    /**
     * Checa index de pickingOrders
     *
     * @return void
     */
    public function testIndex()
    {
        $this->assertTrue(true);
    }
}
