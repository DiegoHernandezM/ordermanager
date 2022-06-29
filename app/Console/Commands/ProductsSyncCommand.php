<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:productssync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza tablas de productos con locations';

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
        Log::error('Inicio: Se copian las tablas de productos al sistema de oms.');
        $process = new Process('sh config/productos.sh');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        Log::error('Fin: Se copian las tablas de productos al sistema de oms.');
    }
}
