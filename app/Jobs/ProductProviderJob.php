<?php

namespace App\Jobs;

use App\Managers\Admin\AdminProductProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProductProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oRequest)
    {
        $this->request = $oRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->request->operation) {
            case 'new':
                try {
                    $manProductProvider = new AdminProductProviderManager();
                    $manProductProvider->createNewProductProvider($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $manProductProvider = new AdminProductProviderManager();
                    $manProductProvider->updateProductProvider($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            default:
                break;
        }
    }
}
