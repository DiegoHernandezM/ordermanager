<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductSizeRequest;
use App\Managers\RequestManager;
use App\ProductSize;
use App\Repositories\Eks\ProductSizeRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductSizeManager
{
    protected $rProductSize;
    protected $mProductSize;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductSize = new ProductSizeRepository();
        $this->mProductSize = new ProductSize();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductSize($oRequest)
    {
        try {
            $sizes = json_decode($oRequest);
            foreach ($sizes as $size) {
                $find = $this->mProductSize->find($size);
                if (!$find) {
                    $this->mProductSize->create([
                        'id' => $size->id,
                        'jdaId' => $size->jdaId,
                        'jdaName' => $size->jdaName
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function updateProductSize($oRequest)
    {
        try {
            $sizes = json_decode($oRequest);
            foreach ($sizes as $size) {
                $this->rProductSize->updateProductSize($size->id, $size);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function resetSizes()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/product-sizes?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductSize->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductSize->create([
                                'jdaId' => $route->jdaId,
                                'jdaName'   => $route->jdaName,
                            ]);
                        }
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
                        return false;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}