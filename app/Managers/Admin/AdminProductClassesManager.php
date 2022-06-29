<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductClassesRequest;
use App\Managers\RequestManager;
use App\ProductClasses;
use App\Repositories\Eks\ProductClassesRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductClassesManager
{
    protected $rProductClasses;
    protected $mProductClasses;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductClasses = new ProductClassesRepository();
        $this->mProductClasses = new ProductClasses();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductClasses($oRequest)
    {
        try {
            $classes = json_decode($oRequest);
            foreach ($classes as $class) {
                $find = $this->mProductClasses->find($class->id);
                if (!$find) {
                    $this->mProductClasses->create([
                        'id' => $class->id,
                        'jdaId' => $class->jdaId,
                        'jdaName' => $class->jdaName
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
    public function updateProductClasses($oRequest)
    {
        try {
            $classes = json_decode($oRequest);
            foreach ($classes as $class) {
                $this->rProductClasses->updateProductClasses($class->id, $class);
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
    public function resetClasses()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/classes?paginated=false', 'GET', $token, '', '', '', [],[]);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductClasses->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductClasses->create([
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