<?php

namespace App\Repositories\Eks;

use App\ProductProvider;

class ProductProviderRepository
{
    protected $mProductProvider;

    public function __construct()
    {
        $this->mProductProvider = new ProductProvider();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductProvider($oRequest)
    {
        $provider = $this->mProductProvider->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $provider;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductProvider($id,$oRequest)
    {
        $provider = $this->mProductProvider->find($id);
        $provider->jdaId = $oRequest->jdaId;
        $provider->jdaName = $oRequest->jdaName;
        $provider->save();

        return $provider;
    }
}