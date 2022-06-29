<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Log as Logger;

use App\Managers\Admin\AdminOrderManager;

class ProcessWaveRoundingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SqsFifoQueueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $waveId;

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
                $log = Logger::create(['message' => 'Procesando ola: '.$this->waveId, 'loggable_id' => 1, 'loggable_type' => 'App\User', 'user_id' => 1]);
                $orderManager = new AdminOrderManager();
                $hola = $orderManager->processWaveRoundings($this->waveId);
            } catch (\Exception $e) {
                Log::error($e);
                throw $e;
            }
        } else {
            $this->delete();
        }
    }
}
