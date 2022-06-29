<?php

namespace App\Jobs;

use App\Managers\Admin\AdminProductClassesManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductClassesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $manProductClasses;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oRequest)
    {
        $this->request = $oRequest;
        $this->manProductClasses = new AdminProductClassesManager();
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
                    $this->manProductClasses->createNewProductClasses($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $this->manProductClasses->updateProductClasses($this->request->data);
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
