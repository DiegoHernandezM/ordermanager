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

class ProcessOrderGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SqsFifoQueueable, SerializesModels;

    protected $file_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_name)
    {
        $this->file_name = $file_name;
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
                $log = Logger::create(['message' => $this->file_name, 'loggable_id' => 1, 'loggable_type' => 'App\User', 'user_id' => 1]);
                $orderManager = new AdminOrderManager();
                $orderManager->processOrderGroup($this->file_name);
            } catch (\Exception $e) {
                Log::error($e);
                throw $e;
            }
        } else {
            $this->delete();
        }
    }
}
