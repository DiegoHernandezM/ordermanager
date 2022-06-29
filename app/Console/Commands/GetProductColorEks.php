<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductColorManager;
use Illuminate\Console\Command;

class GetProductColorEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_color';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_colors';

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
        $getColors = new AdminProductColorManager();
        $colors = $getColors->resetColors();
        if ($colors) {
            return true;
        } else {
            return false;
        }
    }
}
