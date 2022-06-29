<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductFitManager;
use Illuminate\Console\Command;

class GetProductFitEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_fits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_fits';

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
        $getFits = new AdminProductFitManager();
        $gits = $getFits->resetFit();
        if ($gits) {
            return true;
        } else {
            return false;
        }
    }
}
