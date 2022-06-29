<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminEksManager;
use Illuminate\Console\Command;

class GetStoresEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca la tabla stores y consume endpoint EKS';

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
        $getStores = new AdminEksManager();
        $stores = $getStores->resetStores();
        if ($stores) {
            return true;
        } else {
            return false;
        }
    }
}
