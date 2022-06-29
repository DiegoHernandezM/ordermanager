<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminWamasFileManager;
use Illuminate\Console\Command;

class SyncWamasDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wamas:demosync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recibe los archivos almacenados en storage simulando interaccion con el FTP de WAMAS';

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
        $manager = new AdminWamasFileManager();
        $manager->syncFilesDemo();
    }
}
