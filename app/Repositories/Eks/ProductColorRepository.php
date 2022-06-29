<?php

namespace App\Repositories\Eks;


use App\ProductColor;

class ProductColorRepository
{
    protected $mProductColor;

    public function __construct()
    {
        $this->mProductColor = new ProductColor();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductColor($oRequest)
    {
        $color = $this->mProductColor->create([
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
    public function updateProductColor($id,$oRequest)
    {
        $color = $this->mProductColor->find($id);
        if ($color) {
            $color->jdaId = $oRequest->jdaId;
            $color->jdaName = $oRequest->jdaName;
            $color->save();
        }
        return $color;
    }
}