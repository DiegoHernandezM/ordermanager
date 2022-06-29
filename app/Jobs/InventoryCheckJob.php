<?php

namespace App\Jobs;

use App\Managers\Admin\AdminSAALMAManager;
use App\Repositories\LineRepository;
use App\Wave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class InventoryCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $waveId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($waveId)
    {
        $this->waveId = $waveId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() == 1) {
            try {
                $this->delete();
                $wave = Wave::find($this->waveId);
                $saalmaManager = new AdminSAALMAManager();
                $lineRepository = new LineRepository();
                $inventoryRequest = [];
                if ($wave->ordergroup->local == '10110') {
                    $almacen = '10';
                } else {
                    $almacen = '07';
                }
                $inventoryRequest['almacen'] = $almacen;
                $skuList = [];
                $lines = $lineRepository->waveLinesSumBySku($wave);
                foreach ($lines as $key => $ln) {
                    $skuList[] = $ln['sku'];
                }
                $inventoryRequest['skuList'] = $skuList;
                $actualStock = $saalmaManager->getInventory($inventoryRequest);
                $adjustedLines = $lineRepository->adjustQuantitiesByPriority(
                    $this->waveId,
                    $lines,
                    $actualStock
                );
                $wave->pieces = $wave->lines()->sum('expected_pieces');
                $wave->verified_stock = 1;
                $wave->save();
            } catch (Exception $e) {
                Log::error($e);
            }
        }
    }
}
