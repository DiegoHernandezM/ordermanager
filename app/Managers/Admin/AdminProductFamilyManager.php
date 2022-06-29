<?php

namespace App\Managers\Admin;


use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductFamilyRequest;
use App\Managers\RequestManager;
use App\ProductFamily;
use App\Repositories\Eks\ProductFamilyRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductFamilyManager
{
    protected $rProductFamily;
    protected $mProductFamily;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductFamily = new ProductFamilyRepository();
        $this->mProductFamily = new ProductFamily();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param ProductFamilyRequest $data
     * @return bool
     */
    public function createNewProductFamily(ProductFamilyRequest $data)
    {
        try {
            $this->rProductFamily->createProductFamily($data);
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param ProductFamilyRequest $data
     * @return bool
     */
    public function updateProductFamily(ProductFamilyRequest $data)
    {
        try {
            $family = $this->rProductFamily->updateProductFamily($data->id, $data);
            if ($family != null) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function resetFamilies()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/product-families?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductFamily->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductFamily->create([
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