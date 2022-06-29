<?php

namespace App\Repositories\Eks;


use App\ProductType;

class ProductTypeRepository
{
    protected $mProductType;

    public function __construct()
    {
        $this->mProductType = new ProductType();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductType($oRequest)
    {
        $type = $this->mProductType->create([
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
    public function updateProductType($id,$oRequest)
    {
        $type = $this->mProductType->find($id);
        if ($type) {
            $type->jdaId = $oRequest->jdaId;
            $type->jdaName = $oRequest->jdaName;
            $type->save();
        }
        return $type;
    }
}