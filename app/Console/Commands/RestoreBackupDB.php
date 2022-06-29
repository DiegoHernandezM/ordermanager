<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Managers\Admin\AdminBackupDBManager;

class RestoreBackupDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:check_backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revisa si existe mensaje en SQS para cargar backup';

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
        $backup->restoreBackupDB();
        if ($backup) {
            return true;
        } else {
            return false;
        }
    }
}
