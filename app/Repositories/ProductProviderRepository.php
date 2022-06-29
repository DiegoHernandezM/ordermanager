<?php

namespace App\Repositories;

use App\ProductProvider;
use DB;

class ProductProviderRepository
{
    protected $mProductprovider;

    public function __construct()
    {
        $this->mProductprovider = new ProductProvider();
    }

    /**
     * @param $oRequest
     * @return ProductProvider[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllProviders($oRequest)
    {
        if ($oRequest->order_group > 0) {
            return DB::table('lines')->
                    select('product_providers.id', 'product_providers.jdaId', 'product_providers.jdaName')
                        ->join('orders', 'orders.id', '=', 'lines.order_id')
                        ->join('styles', 'styles.id', '=', 'lines.style_id')
                        ->join('product_providers', 'product_providers.id', '=', 'styles.provider_id')
                        ->where('orders.order_group_id', $oRequest->order_group)
                        ->distinct()
                        ->whereNull('lines.wave_id')
                        ->get();
        }
        $providers = $this->mProductprovider->all();
        
        return $providers;
    }

    /**
     * @param $oRequest
     * @return ProductProvider[]|\Illuminate\Database\Eloquent\Collection
     */
    public function search($oRequest)
    {
        $providers = $this->mProductprovider->where('jdaName', 'like', "%$oRequest->search%")
                                            ->orWhere('jdaId', 'like', "%$oRequest->search%")
                                            ->get();
        return $providers;
    }
}
