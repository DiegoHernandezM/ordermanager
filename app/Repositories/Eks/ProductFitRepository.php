<?php

namespace App\Repositories\Eks;


use App\ProductFit;

class ProductFitRepository
{
    protected $mProductFit;

    public function __construct()
    {
        $this->mProductFit = new ProductFit();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductFit($oRequest)
    {
        $type = $this->mProductFit->create([
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
    public function updateProductFit($id,$oRequest)
    {
        $type = $this->mProductFit->find($id);
        if ($type) {
            $type->jdaId = $oRequest->jdaId;
            $type->jdaName = $oRequest->jdaName;
            $type->save();
        }
        return $type;
    }
}