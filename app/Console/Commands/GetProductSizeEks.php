<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductSizeManager;
use Illuminate\Console\Command;

class GetProductSizeEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_sizes';

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
        $getSizes = new AdminProductSizeManager();
        $size = $getSizes->resetSizes();
        if ($size) {
            return true;
        } else {
            return false;
        }
    }
}
