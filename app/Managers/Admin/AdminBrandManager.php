<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\BrandRequest;
use App\Managers\RequestManager;
use App\Brand;
use App\Repositories\Eks\BrandRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminBrandManager
{
    protected $rBrand;
    protected $mBrand;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rBrand = new BrandRepository();
        $this->mBrand = new Brand();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param BrandRequest $data
     * @return bool
     */
    public function createNewBrand(BrandRequest $data)
    {
        try {
            $this->rBrand->createBrand($data);
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param BrandRequest $data
     * @return bool
     */
    public function updateBrand(BrandRequest $data)
    {
        try {
            $brand = $this->rBrand->updateBrand($data->id, $data);
            if ($brand != null) {
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
    public function resetBrands()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/brands?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mBrand->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mBrand->create([
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
