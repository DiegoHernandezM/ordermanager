<?php

namespace App\Managers\Admin;


use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductPriorityRequest;
use App\Managers\RequestManager;
use App\ProductPriority;
use App\Repositories\Eks\ProductPriorityRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductPriorityManager
{
    protected $rProductPriority;
    protected $mProductPriority;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductPriority = new ProductPriorityRepository();
        $this->mProductPriority = new ProductPriority();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductPriority($oRequest)
    {
        try {
            $priorities = json_decode($oRequest);
            foreach ($priorities as $priority) {
                $productPriority = $this->mProductPriority->find($priority->id);
                if (!$productPriority) {
                    $this->mProductPriority->create([
                        'id' => $priority->id,
                        'jdaId' => $priority->jdaId,
                        'jdaName' => $priority->jdaName
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
    public function updateProductPriority($oRequest)
    {
        try {
            $priorities = json_decode($oRequest);
            foreach ($priorities as $priority) {
                $this->rProductPriority->updateProductPriority($priority->id, $priority);
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
    public function resetPriorities()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/priorities?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductPriority->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductPriority->create([
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