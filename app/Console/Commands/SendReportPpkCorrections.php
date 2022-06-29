<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Managers\SendMailsManager;

class SendReportPpkCorrections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:send_report_ppk_corrections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia por correo el reporte de las correcciones de ppk';

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
        $reportPpk = new SendMailsManager();
        $execute = $reportPpk->sendMailReportPpkCorrections();
    }
}
