<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminDebugTables;
use Illuminate\Console\Command;

class DebugTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:debug_tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Depura las tablas: Lines, Orders, Cartons, Carton_lines';

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
        $debugTables = new AdminDebugTables();
        $debug = $debugTables->deleteOldRecords();
        if ($debug) {
            return true;
        } else {
            return false;
        }
    }
}
