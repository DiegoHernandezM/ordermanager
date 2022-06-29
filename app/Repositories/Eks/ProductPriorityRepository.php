<?php

namespace App\Repositories\Eks;


use App\ProductPriority;

class ProductPriorityRepository
{
    protected $mProductPriority;

    public function __construct()
    {
        $this->mProductPriority = new ProductPriority();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductPriority($oRequest)
    {
        $priority = $this->mProductPriority->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $priority;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductPriority($id,$oRequest)
    {
        $priority = $this->mProductPriority->find($id);
        if ($priority) {
            $priority->jdaId = $oRequest->jdaId;
            $priority->jdaName = $oRequest->jdaName;
            $priority->save();
        }
        return $priority;
    }
}