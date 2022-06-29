<?php

namespace App\Managers\Admin;
use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductProviderRequest;
use App\Managers\RequestManager;
use App\ProductProvider;
use App\Repositories\Eks\ProductProviderRepository;
use Illuminate\Support\Facades\Redis;
use Log;


class AdminProductProviderManager
{
    protected $rProductProvider;
    protected $mProductProvider;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductProvider = new ProductProviderRepository();
        $this->mProductProvider = new ProductProvider();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @return bool
     */
    public function  createNewProductProvider($oRequest)
    {
        try {
            $providers = json_decode($oRequest);
            $this->rProductProvider->createProductProvider($providers);
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return $e;
        }
    }

    /**
     * @param ProductProviderRequest $data
     * @return bool
     */
    public function updateProductProvider(ProductProviderRequest $data)
    {
        try {
            $provider = $this->rProductProvider->updateProductProvider($data->id, $data);
            if ($provider != null) {
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
    public function resetProviders()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/providers?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductProvider->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductProvider->create([
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