<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminEksManager;
use Illuminate\Console\Command;

class GetVariations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:getVariations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca la tabla y obtiene todas las variaciones';

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
        $getVariations = new AdminEksManager();
        $variations = $getVariations->resetVariations();
        if ($variations) {
            return true;
        } else {
            return false;
        }
    }
}
