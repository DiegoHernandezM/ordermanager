<?php
/**
 * Created by PhpStorm.
 * User: dhernandezm
 * Date: 4/6/20
 * Time: 9:54 AM
 */

namespace App\Managers\Admin;


use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductClassificationRequest;
use App\Managers\RequestManager;
use App\ProductClassification;
use App\Repositories\Eks\ProductClassificacionRepository;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminProductClassificationManager
{
    protected $rProductClassification;
    protected $mProductClassification;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->rProductClassification = new ProductClassificacionRepository();
        $this->mProductClassification = new ProductClassification();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewClassification($oRequest)
    {
        try {
            $clasifications = json_decode($oRequest);
            foreach ($clasifications as $clasification) {
                $find = $this->mProductClassification->find($clasification->id);
                if (!$find) {
                    $this->mProductClassification->create([
                        'id' => $clasification->id,
                        'jdaId' => $clasification->jdaId,
                        'jdaName' => $clasification->jdaName
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
    public function updateClassification($oRequest)
    {
        try {
            $classifications = json_decode($oRequest);
            foreach ($classifications as $classification) {
                $this->rProductClassification->updateProductClassification($classification->id, $classification);
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
    public function resetClassifications()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token = Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/products/catalogues/product-classifications?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mProductClassification->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mProductClassification->create([
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