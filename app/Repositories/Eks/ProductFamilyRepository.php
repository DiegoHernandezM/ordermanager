<?php

namespace App\Repositories\Eks;

use App\ProductFamily;

class ProductFamilyRepository
{
    protected $mProductFamily;

    public function __construct()
    {
        $this->mProductPriority = new ProductFamily();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductFamily($oRequest)
    {
        $family = $this->mProductFamily->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $family;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductFamily($id,$oRequest)
    {
        $family = $this->mProductFamily->find($id);
        $family->jdaId = $oRequest->jdaId;
        $family->jdaName = $oRequest->jdaName;
        $family->save();

        return $family;
    }
}