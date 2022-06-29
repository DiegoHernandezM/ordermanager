<?php

namespace App\Console\Commands;

use App\Managers\SendMailsManager;
use Illuminate\Console\Command;

class SendReportWave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:send_report_waves';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia por correo el reporte de olas a usuarios suscritos';

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
        $reporrWave = new SendMailsManager();
        $proccess = $reporrWave->sendMailReportWaves();
    }
}
