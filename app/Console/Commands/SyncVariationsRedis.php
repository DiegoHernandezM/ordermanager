<?php

namespace App\Console\Commands;

use App\Variation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SyncVariationsRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oms:syncVariationsRedis';

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
        $variations = Variation::all();
        foreach ($variations as $key => $var) {
            Redis::set('sku:'.$var->sku.':id', $var->id);
            Redis::set('sku:'.$var->sku.':style', $var->style_id);
            Redis::set('sku:'.$var->sku.':department', $var->department_id);
            Redis::set('sku:'.$var->sku.':division', $var->division_id);
        }
    }
}
