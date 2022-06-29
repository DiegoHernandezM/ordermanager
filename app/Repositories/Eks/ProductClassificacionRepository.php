<?php
namespace App\Repositories\Eks;

use App\ProductClassification;

class ProductClassificacionRepository
{
    protected $mProductClassification;

    public function __construct()
    {
        $this->mProductClassification = new ProductClassification();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductClassification($oRequest)
    {
        $classification = $this->mProductClassification->create([
           'jdaId' => $oRequest->jdaId,
           'jdaName' => $oRequest->jdaName
        ]);

        return $classification;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductClassification($id,$oRequest)
    {
        $classification = $this->mProductClassification->find($id);
        if ($classification) {
            $classification->jdaId = $oRequest->jdaId;
            $classification->jdaName = $oRequest->jdaName;
            $classification->save();
        }
        return $classification;
    }
}