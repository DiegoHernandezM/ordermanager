<?php

namespace App\Jobs;

use App\Managers\Admin\AdminRouteManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class RouteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
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
                    $route = new AdminRouteManager();
                    $route->createNewRoute($this->request);
                } catch (\Exception $e) {
                    Log::error($e);
                    throw $e;
                }
                break;
            case 'update':
                try {
                    $route = new AdminRouteManager();
                    $route->updateRoute($this->request);
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
