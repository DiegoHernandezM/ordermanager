<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\StoreRepository;
use App\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    protected $mStore;
    protected $storeRepository;

    public function __construct(Request $request)
    {
        $this->storeRepository = new StoreRepository();
        $this->mStore = new Store();
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\Response
     */
    // TODO:: borrar esta funcion cuando pase a produccion V2 de front
    public function index(Request $oRequest)
    {
        try {
            $aStores = $this->storeRepository->getAll($oRequest);
            return ApiResponses::okObject($aStores);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getStores(Request $oRequest)
    {
        try {
            return ApiResponses::okObject($this->storeRepository->getAllStores($oRequest));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\Response
     */
    public function list(Request $oRequest)
    {
        try {
            return ApiResponses::okObject(Store::select('id', 'number', 'name')->get());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Guarda una tienda nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $store = $this->storeRepository->createStore($request);
            return ApiResponses::okObject($store);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function findStore($id)
    {
        try {
            $store = $this->mStore->find($id);
            return ApiResponses::okObject($store);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Edita una tienda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $updateStore = $this->storeRepository->updateStore($request);
            if (!$updateStore) {
                ApiResponses::internalServerError();
            }
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $store = $this->storeRepository->deleteStore($id);
            if (!$store) {
                return ApiResponses::internalServerError();
            }
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Get store list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        try {
            return $this->storeRepository->get(['id', 'name']);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function updateStore(Request $oRequest)
    {
        try {
            $updateStore = $this->storeRepository->updateDataStore($oRequest);
            if (!$updateStore) {
                ApiResponses::internalServerError();
            }
            return ApiResponses::ok();
        } catch(\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function uRanking(Request $oRequest)
    {
        try {
            $updateStore = $this->storeRepository->updateRanking($oRequest);
            if (!$updateStore) {
                ApiResponses::internalServerError();
            }
            return ApiResponses::ok();
        } catch(\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
