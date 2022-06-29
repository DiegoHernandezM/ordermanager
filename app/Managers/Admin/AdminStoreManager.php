<?php

namespace App\Managers\Admin;

use App\Http\Requests\StoreRequest;
use App\Managers\MessagesManager;
use App\Store;
use Log;

class AdminStoreManager
{

    protected $mStore;

    public function __construct()
    {
        $this->mStore = new Store();
    }

    /**
     * @param StoreRequest $oRequest
     * @return bool
     */
    public function createNewStore($oRequest)
    {
        try{
            $stores = json_decode($oRequest);
            foreach ($stores as $store) {
                $find = $this->mStore->find($store->id);
                if (!$find) {
                    $this->mStore->create([
                        'id' => $store->id,
                        'number' => $store->tdaJda,
                        'ranking' => $store->ranking,
                        'name'   => $store->name,
                        'store_ranking' => 999,
                        'route' => $store->routes[0],
                        'pbl_ranking' => 0,
                        'position' => 0,
                        'status' => $store->status,
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param StoreRequest $oRequest
     * @return bool
     */
    public function updateStore($oRequest)
    {
        try{
            $stores = json_decode($oRequest);
            foreach ($stores as $store) {
              $str = $this->mStore->find($store->id);
              if ($str) {
                  $str->number = $store->tdaJda;
                  $str->name = $store->name;
                  $str->save();
              }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
