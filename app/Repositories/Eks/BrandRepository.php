<?php

namespace App\Repositories\Eks;

use App\Brand;

class BrandRepository
{
    protected $mBrand;

    public function __construct()
    {
        $this->mBrand = new Brand();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createBrand($oRequest)
    {
        $brand = $this->mBrand->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $brand;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateBrand($id, $oRequest)
    {
        $brand = $this->mBrand->find($id);
        $brand->jdaId = $oRequest->jdaId;
        $brand->jdaName = $oRequest->jdaName;
        $brand->save();

        return $brand;
    }
}
