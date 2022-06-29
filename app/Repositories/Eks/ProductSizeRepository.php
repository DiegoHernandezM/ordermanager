<?php

namespace App\Repositories\Eks;


use App\ProductSize;

class ProductSizeRepository
{
    protected $mProductSize;

    public function __construct()
    {
        $this->mProductSize = new ProductSize();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductSize($oRequest)
    {
        $color = $this->mProductSize->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $color;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductSize($id,$oRequest)
    {
        $color = $this->mProductSize->find($id);
        if ($color) {
            $color->jdaId = $oRequest->jdaId;
            $color->jdaName = $oRequest->jdaName;
            $color->save();
        }
        return $color;
    }
}