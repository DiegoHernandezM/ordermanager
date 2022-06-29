<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductClassesManager;
use Illuminate\Console\Command;

class GetProductClassesEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_classes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_classes';

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
        $getClasses = new AdminProductClassesManager();
        $classes = $getClasses->resetClasses();
        if ($classes) {
            return true;
        } else {
            return false;
        }
    }
}
