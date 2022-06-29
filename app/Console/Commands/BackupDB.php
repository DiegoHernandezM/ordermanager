<?php

namespace App\Console\Commands;

use App\Managers\Admin\AdminBackupDBManager;
use Illuminate\Console\Command;

class BackupDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:backup_db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica que las ordenes de surtido esten complatas para hacer backup de la DB';

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
        $backup = new AdminBackupDBManager();
        $backup->runBackup();
        if ($backup) {
            return true;
        } else {
            return false;
        }
    }
}
