<?php

namespace App\Console\Commands;

use App\Jobs\SendInfoOrderGruopsJob;
use App\Managers\SendMailsManager;
use Illuminate\Console\Command;

class SendOrderGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:send_order_groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el job SendInfoOrderGroupsJob';

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
        $orderGroup = new SendMailsManager();
        $proccess = $orderGroup->sendMailOrderGroup();
    }
}
