<?php

namespace App\Repositories\Eks;


use App\ProductClasses;

class ProductClassesRepository
{
    protected $mProductClasses;

    public function __construct()
    {
        $this->mProductClasses = new ProductClasses();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProductClasses($oRequest)
    {
        $classes = $this->mProductClasses->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName
        ]);

        return $classes;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed
     */
    public function updateProductClasses($id,$oRequest)
    {
        $class = $this->mProductClasses->find($id);
        if ($class) {
            $class->jdaId = $oRequest->jdaId;
            $class->jdaName = $oRequest->jdaName;
            $class->save();
        }
        return $class;
    }
}