<?php

namespace App\Repositories;


use App\ProductFamily;
use Illuminate\Http\Request;

class ProductFamilyRepository
{
    protected $mProductFamily;

    public function __construct()
    {
        $this->mProductFamily = new ProductFamily();
    }

    /**
     * @param Request $oRequest
     * @return mixed
     */
    public function getAllProductsFamily(Request $oRequest)
    {
        $families = $this->mProductFamily->orderBy('ranking', 'asc')->paginate((int) $oRequest->input('per_page', 40));
        return $families;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductFamily($id)
    {
        $family = $this->mProductFamily->find($id);
        return $family;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createProducFamily($oRequest)
    {
        $families = $this->mProductFamily->orderBy('ranking', 'asc')->get();
        $orders = [];
        foreach ($families as $family) {
            $orders[] = $family->ranking;
        }

        $lastOrder = end( $orders );

        $family = $this->mProductFamily->create([
            'jdaId' => $oRequest->jdaId,
            'jdaName' => $oRequest->jdaName,
            'label' => $oRequest->label,
            'ranking' => $lastOrder+1
        ]);

        if ($family) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function updateProductFamily($oRequest)
    {
        foreach ($oRequest->familiesId as $key => $item) {
            $family = $this->mProductFamily->find($item);
            $family->ranking = $key+1;
            $family->update();
        }
        $productFamily = $this->mProductFamily->find($oRequest->id);
        $productFamily->jdaId = $oRequest->jdaId;
        $productFamily->jdaName = $oRequest->jdaName;
        $productFamily->label = $oRequest->label;
        $productFamily->update();

        return true;
    }

}