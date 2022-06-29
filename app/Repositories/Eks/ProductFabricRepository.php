<?php

namespace App\Repositories\Eks;


use App\ProductFabric;

class ProductFabricRepository
{
    protected $mProductFabric;

    public function __construct()
    {
        $this->mProductFabric = new ProductFabric();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductFabric($oRequest)
    {
        $type = $this->mProductFabric->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $type;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductFabric($id,$oRequest)
    {
        $type = $this->mProductFabric->find($id);
        if ($type) {
            $type->jdaId = $oRequest->jdaId;
            $type->jdaName = $oRequest->jdaName;
            $type->save();
        }

        return $type;
    }
}