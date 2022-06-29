<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductProviderManager;
use Illuminate\Console\Command;

class GetproductProviderEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_providers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $getProviders = new AdminProductProviderManager();
        $providers = $getProviders->resetProviders();
        if ($providers) {
            return true;
        } else {
            return false;
        }
    }
}
