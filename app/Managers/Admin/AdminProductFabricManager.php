<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductFabricRequest;
use App\Managers\RequestManager;
use App\ProductFabric;
use App\Repositories\Eks\ProductFabricRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductFabricManager
{
    protected $rProductFabric;
    protected $mProductFabric;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductFabric = new ProductFabricRepository();
        $this->mProductFabric = new ProductFabric();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductFabric($oRequest)
    {
        try {
            $fabrics = json_decode($oRequest);
            foreach ($fabrics as $fabric) {
                $find = $this->mProductFabric->find($fabric->id);
                if (!$find) {
                    $this->mProductFabric->create([
                        'id' => $fabric->id,
                        'jdaId' => $fabric->id,
                        'jdaName' => $fabric->name
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
    public function updateProductFabric($oRequest)
    {
        try {
            $fabrics = json_decode($oRequest);
            foreach ($fabrics as $fabric) {
                $this->rProductFabric->updateProductFabric($fabric->id, $fabric);
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
    public function resetFabric()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/fabrics?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductFabric->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductFabric->create([
                                'jdaId' => $route->id,
                                'jdaName'   => $route->name,
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
