<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminEksManager;
use Illuminate\Console\Command;

class GetStylesEks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:getstyles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca la tabla y obtiene todos los estilos';

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
        $getStyles = new AdminEksManager();
        $styles = $getStyles->resetStyles();
        if ($styles) {
            return true;
        } else {
            return false;
        }
    }
}
