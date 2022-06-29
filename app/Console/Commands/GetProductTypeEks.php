<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductTypeManager;
use Illuminate\Console\Command;

class GetProductTypeEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_types';

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
        $getTypes = new AdminProductTypeManager();
        $types = $getTypes->resetType();
        if ($types) {
            return true;
        } else {
            return false;
        }
    }
}
