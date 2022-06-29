<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductFamilyManager;
use Illuminate\Console\Command;

class GetProductFamilyEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_family';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_families';

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
        $getFamilies = new AdminProductFamilyManager();
        $families = $getFamilies->resetFamilies();
        if ($families) {
            return true;
        } else {
            return false;
        }
    }
}
