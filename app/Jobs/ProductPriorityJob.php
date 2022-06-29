<?php

namespace App\Jobs;

use App\Managers\Admin\AdminProductPriorityManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProductPriorityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $manProductPriority;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oRequest)
    {
        $this->request = $oRequest;
        $this->manProductPriority = new AdminProductPriorityManager();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->request->operation){
            case 'new':
                try {
                    $this->manProductPriority->createNewProductPriority($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $this->manProductPriority->updateProductPriority($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            default :
                break;
        }
    }
}
