<?php

namespace App\Jobs;

use App\Managers\Admin\AdminBrandManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BrandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $manBrand;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oRequest)
    {
        $this->request = $oRequest;
        $this->manBrand = new AdminBrandManager();
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
                    $this->manBrand->createNewBrand($this->request);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'modify':
                try {
                    $this->manBrand->updateBrand($this->request);
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
