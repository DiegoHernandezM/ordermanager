<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductPriorityManager;
use Illuminate\Console\Command;

class GetProductPriorityEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_priority';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_priorities';

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
        $getPriorities = new AdminProductPriorityManager();
        $priorities = $getPriorities->resetPriorities();
        if ($priorities) {
            return true;
        } else {
            return false;
        }
    }
}
