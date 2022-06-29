<?php

namespace App\Console\Commands;

use App\StoreDepartment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SyncStoreDepartmentsRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:syncstdps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconstruye tabla de redis para StoreDepartments';

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
        $keys = Redis::keys('stdp:*');
        if (!empty($keys)) {
            $keys = array_map(function ($k) {
                return str_replace('order_manager_system_database_', '', $k);
            }, $keys);
            Redis::del($keys);
        }
        StoreDepartment::where('block_until', '<', date(now()))->delete();
        $stdps = StoreDepartment::all();
        foreach ($stdps as $stdp) {
            Redis::set('stdp:'.$stdp->storeNumber.':'.$stdp->department_id, true);
        }
    }
}
