<?php

namespace App\Console\Commands;

use App\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SyncStoresRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:syncStoresRedis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $stores = Store::all();
        foreach ($stores as $key => $var) {
            Redis::set('stores:'.$var->number.':ranking', $var->ranking);
            Redis::set('stores:'.$var->number.':sorter_ranking', $var->sorter_ranking);
        }
    }
}
