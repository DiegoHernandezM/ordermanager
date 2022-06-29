<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductFabricManager;
use Illuminate\Console\Command;

class GetProductFabricEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_fabrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_fabrics';

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
        $getFabrics = new AdminProductFabricManager();
        $fabrics = $getFabrics->resetFabric();
        if ($fabrics) {
            return true;
        } else {
            return false;
        }
    }
}
