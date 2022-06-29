<?php


namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductTypeRequest;
use App\Managers\RequestManager;
use App\ProductType;
use App\Repositories\Eks\ProductTypeRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductTypeManager
{

    protected $rProductType;
    protected $mProductType;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductType = new ProductTypeRepository();
        $this->mProductType = new ProductType();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductType($oRequest)
    {
        try {
            $types = json_decode($oRequest);
            foreach ($types as $type) {
                $this->mProductType->create([
                    'id' => $type->id,
                    'jdaId' => $type->jdaId,
                    'jdaName' => $type->jdaName
                ]);
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
    public function updateProductType($oRequest)
    {
        try {
            $types = json_decode($oRequest);
            foreach ($types as $type) {
                $this->rProductType->updateProductType($type->id, $type);
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
    public function resetType()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/product-types?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductType->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductType->create([
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