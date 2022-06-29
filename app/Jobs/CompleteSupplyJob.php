<?php

namespace App\Jobs;

use App\Repositories\LineRepository;
use App\Repositories\WaveRepository;
use App\Wave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteSupplyJob implements ShouldQueue
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
            $this->delete();
            $wave = Wave::find($this->waveId);
            $lineRepository = new LineRepository();
            $result = $lineRepository->adjustQuantitiesBySupply($wave);
            $waveRepository = new WaveRepository();
            $rules = json_decode($wave->business_rules, true);
            $divisions = $rules['divisions'];
            if (in_array("6", $divisions)) {
                $area = 'ptl';
            } else {
                $area = 'sorter3';
            }
            $waveRepository->getJson($wave->id, $area);
        }
    }
}
