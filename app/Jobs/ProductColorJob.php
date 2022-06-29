<?php

namespace App\Jobs;

use App\Managers\Admin\AdminProductColorManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductColorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $manProductColor;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oRequest)
    {
        $this->request = $oRequest;
        $this->manProductColor = new AdminProductColorManager();
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
                    $this->manProductColor->createNewProductColor($this->request->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $this->manProductColor->updateProductColor($this->request->data);
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
