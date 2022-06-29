<?php

namespace App\Jobs;

use App\Managers\Admin\AdminStyleManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $oRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->oRequest = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->oRequest->operation) {
            case 'new':
                try {
                    $product = new AdminStyleManager();
                    $product->createNewStyle($this->oRequest->data);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $product = new AdminStyleManager();
                    $product->updateStyle($this->oRequest->data);
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
