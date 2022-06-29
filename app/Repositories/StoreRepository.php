<?php

namespace App\Repositories;

use App\Store;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreRepository extends BaseRepository
{
    protected $mStore;

    public function __construct()
    {
        $this->mStore = new Store();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    // TODO::Borrar esta funcion cuando se cambie a V2 el front
    public function getAll($oRequest)
    {
        $sFiltro = $oRequest->input('search', false);
        $aStores = $this->mStore
            ->where(
                function ($q) use ($sFiltro) {
                    if ($sFiltro !== false) {
                        return $q
                            ->orWhere('name', 'like', "%$sFiltro%")
                            ->orWhere('number', '=', "$sFiltro");
                    }
                }
            )
            ->with('route')
            ->orderBy($oRequest->input('order', 'ranking'), $oRequest->input('sort', 'asc'))
            ->paginate((int) $oRequest->input('per_page', 20));

        return $aStores;
    }

    public function getAllStores($oRequest)
    {
        return $this->mStore
            ->with('route')
            ->orderBy($oRequest->input('order', 'ranking'))
            ->get();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createStore($oRequest)
    {
        $ranking = $this->mStore->max('ranking');
        $rankingStore = $this->mStore->max('ranking');
        $store = $this->mStore->create([
            'name' => strtoupper($oRequest->name),
            'number' => $oRequest->number,
            'ranking' => $ranking + 1,
            'route_id' => $oRequest->route_id,
            'pbl_ranking' => $oRequest->pbl_ranking,
            'sorter_ranking' => $rankingStore + 1,
            'rmsId' => $oRequest->rmsId,
            'rmsName' => $oRequest->rmsName,
            'status' => true
        ]);
        return $store;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function updateStore($oRequest)
    {
        try {
            foreach ($oRequest->stores_id_ranking as $key => $item) {
                $storeRanking = $this->mStore->find($item);
                $storeRanking->ranking = $key+1;
                $storeRanking->update();
            }
            foreach ($oRequest->sorter_ranking as $key => $item) {
                $storeRankingSorter = $this->mStore->find($item);
                $storeRankingSorter->sorter_ranking = $key+1;
                $storeRankingSorter->update();
            }

            $store = $this->mStore->find($oRequest->id);
            $store->name = strtoupper($oRequest->name);
            $store->number = $oRequest->number;
            $store->route_id = $oRequest->route_id;
            $store->rmsId = $oRequest->rmsId;
            $store->rmsName = $oRequest->rmsName;
            $store->update();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteStore($id)
    {
        $store = $this->mStore->find($id);
        if ($store != null) {
            $store->status = false;
            $store->update();
            $storesRanking = $this->mStore->orderBy('ranking', 'asc')->where('status', true)->get();
            $storesSorter = $this->mStore->orderBy('sorter_ranking', 'asc')->where('status', true)->get();
            $idOrderRanking = [];
            $idOrderSorer = [];
            foreach ($storesRanking as $item) {
                $idOrderRanking[] = $item->id;
            }
            foreach ($storesSorter as $item) {
                $idOrderSorer[] = $item->id;
            }
            foreach ($idOrderRanking as $key => $item) {
                $store = $this->mStore->find($item);
                $store->ranking = $key+1;
                $store->update();
            }
            foreach ($idOrderSorer as $key => $item) {
                $store = $this->mStore->find($item);
                $store->sorter_ranking = $key+1;
                $store->update();
            }
            return true;
        } else {
            return false;
        }
    }

    public function updateDataStore($oRequest)
    {
        $store = $this->mStore->find($oRequest->id);
        $store->name = strtoupper($oRequest->name);
        $store->number = $oRequest->number;
        $store->route_id = $oRequest->route_id;
        $store->status = (boolean)$oRequest->status ?? true;
        $store->rmsId = $oRequest->rmsId;
        $store->rmsName = $oRequest->rmsName;
        $store->update();
        return true;
    }

    public function updateRanking($oRequest)
    {
        if (count($oRequest->stores_id_ranking) > 0) {
            foreach ($oRequest->stores_id_ranking as $key => $item) {
                $storeRanking = $this->mStore->find($item);
                $storeRanking->ranking = $key+1;
                $storeRanking->update();
            }
        }
        if (count($oRequest->sorter_ranking) > 0) {
            foreach ($oRequest->sorter_ranking as $key => $item) {
                $storeRankingSorter = $this->mStore->find($item);
                $storeRankingSorter->sorter_ranking = $key+1 ?? 200;
                $storeRankingSorter->update();
            }
        }
        return true;
    }
}
