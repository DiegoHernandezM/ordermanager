<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminBrandManager;
use Illuminate\Console\Command;

class GetBrandEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_brand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_brands';

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
        $getBrands = new AdminBrandManager();
        $brand = $getBrands->resetBrands();
        if ($brand) {
            return true;
        } else {
            return false;
        }
    }
}
