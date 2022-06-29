<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductFitRequest;
use App\Managers\RequestManager;
use App\ProductFit;
use App\Repositories\Eks\ProductFitRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductFitManager
{
    protected $rProductFit;
    protected $mProductFit;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductFit = new ProductFitRepository();
        $this->mProductFit = new ProductFit();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewProductFit($oRequest)
    {
        try {
            $fits = json_decode($oRequest);
            foreach ($fits as $fit) {
                $find = $this->mProductFit->find($fit->id);
                if (!$fit) {
                    $this->mProductFit->create([
                        'id' => $oRequest->id,
                        'jdaId' => $oRequest->id,
                        'jdaName' => $oRequest->name
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
    public function updateProductFit($oRequest)
    {
        try {
            $fits = json_decode($oRequest);
            foreach ($fits as $fit) {
                $this->rProductFit->updateProductFit($fit->id, $fit);
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
    public function resetFit()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/fits?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductFit->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductFit->create([
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
