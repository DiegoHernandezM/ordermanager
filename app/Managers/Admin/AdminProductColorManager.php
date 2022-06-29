<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductColorRequest;
use App\Managers\RequestManager;
use App\ProductColor;
use App\Repositories\Eks\ProductColorRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductColorManager
{
    protected $rProductColor;
    protected $mProductColor;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductColor = new ProductColorRepository();
        $this->mProductColor = new ProductColor();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductColor($oRequest)
    {
        try {
            $colors = json_decode($oRequest);
            foreach ($colors as $color) {
                $find = $this->mProductColor->find($color->id);
                if (!$find) {
                    $this->mProductColor->create([
                        'id' => $color->id,
                        'jdaId' => $color->jdaId,
                        'jdaName' => $color->jdaName
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
     * @param ProductColorRequest $data
     * @return bool
     */
    public function updateProductColor($oRequest)
    {
        try {
            $colors = json_decode($oRequest);
            foreach ($colors  as $color) {
                $this->rProductColor->updateProductColor($color->id, $color);
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
    public function resetColors()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/product-colors?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductColor->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductColor->create([
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