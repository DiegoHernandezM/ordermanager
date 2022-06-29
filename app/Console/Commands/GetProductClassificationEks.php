<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminProductClassificationManager;
use Illuminate\Console\Command;

class GetProductClassificationEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:reset_product_classification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca y resetea la tabla de product_classifications';

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
        $getClassifications = new AdminProductClassificationManager();
        $classifications = $getClassifications->resetClassifications();
        if ($classifications) {
            return true;
        } else {
            return false;
        }
    }
}
