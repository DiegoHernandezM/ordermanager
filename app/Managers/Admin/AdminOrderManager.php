<?php

namespace App\Managers\Admin;

use App\Line;
use App\Log as Logger;
use App\Order;
use App\OrderGroup;
use App\Repositories\LineRepository;
use App\Store;
use App\Style;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class AdminOrderManager
{

    public static function processOrderGroup($fileName)
    {
        $file_n = storage_path('app/'.$fileName);
        $file = fopen($file_n, "r");
        $all_data = array();
        $orders = [];
        while (($data = fgetcsv($file, 1000, ",")) !== false) {
            if (is_numeric($data[0])) {
                if ((int)$data[11] > 0) {
                    $sku = (int)$data[9];
                    if (null !== Redis::get('sku:'.$sku.':id')) {
                        $storeNumber = (int)$data[7];
                        if ($storeNumber < 10000) { // Para arreglar el storeNumber que venga en formato no estándar (ej. 125)
                            $storeNumber += 10000;
                        } elseif ($storeNumber > 100000) {
                            $storeNumber = $storeNumber - 90000; // Para arreglar el storeNumber que venga en formato no estándar (ej. 100125)
                        }
                        
                        $orders[$storeNumber]['lines'][] = [
                            'sku'          => $sku,
                            'pieces'       => (int)$data[11],
                            'ppk'          => (int)$data[13] > 0 ? (int)$data[13] : 1,
                            'style_id'     => (int)Redis::get('sku:'.$sku.':style'),
                            'division_id'  => (int)Redis::get('sku:'.$sku.':division'),
                            'variation_id' => (int)Redis::get('sku:'.$sku.':id'),
                            'prepacks'     => (int)$data[11] / (int)$data[13],
                            'expected_pieces'  => (int)$data[11],
                        ];
                    }
                }
            }
        }
        $orderGroup = new OrderGroup;
        $today = new \DateTime();
        $maxId = OrderGroup::whereDate('created_at', Carbon::today())->max('id');
        $orderGroup->description = 'OS-'.$today->format('ymd').'-'.($maxId ? $maxId+1 : 1);
        $orderGroup->reference = $fileName;
        $orderGroup->save();

        $count = 0;
        foreach ($orders as $key => $ord) {
            $order = new Order;
            $store = Store::where('number', $key)->first();
            if (!empty($store)) {
                $order->store_id = !empty($store) ? $store->id : 0;
                $order->storePriority = $count++;
                $order->routePriority = 1;
                $order->routeNumber = !empty($store) ? $store->route_id : '';
                $order->routeDescription = !empty($store) ? $store->route->description : '';
                $order->storeNumber = !empty($store) ? $store->number : $key;
                $order->storeDescription = !empty($store) ? $store->name : '';
                $order->status = 1;
                $order->order_group_id = $orderGroup->id;
                $order->slots = 1;
                $order->save();
                $order->lines()->createMany($ord['lines']);
            }
            unset($orders[$key]);
            unset($ord['lines']);
            unset($order);
        }
    }

    public static function processOrderLines($orderGroup)
    {
        $orders = $orderGroup->orders()->get();
        foreach ($orders as $key => $order) {
            $lines = $order->lines()->get();
            foreach ($lines as $key => $line) {
                $sku = $line->sku;
                $line->variation_id = (int)Redis::get('sku:'.$sku.':id');
                $line->style_id = (int)Redis::get('sku:'.$sku.':style');
                $line->division_id = (int)Redis::get('sku:'.$sku.':division');
                $line->save();
            }
        }
    }

    public function processWaveRoundings($waveId)
    {
        $lineRepository = new LineRepository();
        return $lines = $lineRepository->findByWaveRulesSummationBoxes($waveId);
    }
}
