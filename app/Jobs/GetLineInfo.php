<?php

namespace App\Jobs;

use App\Managers\Admin\AdminOrderManager;
use App\OrderGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetLineInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderGroupId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderGroupId)
    {
        $this->orderGroupId = $orderGroupId;
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
                $orderGroup = OrderGroup::find($this->orderGroupId);
                $orderManager = new AdminOrderManager();
                $orderManager->processOrderLines($orderGroup);
            } catch (\Exception $e) {
                Log::error($e);
                throw $e;
            }
        } else {
            $this->delete();
        }
    }
}
