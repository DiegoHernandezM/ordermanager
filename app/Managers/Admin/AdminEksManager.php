<?php
/**
 * Created by PhpStorm.
 * User: dhernandezm
 * Date: 1/24/20
 * Time: 4:15 PM
 */

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Managers\RequestManager;
use App\Route;
use Illuminate\Support\Facades\Redis;
use App\Store;
use App\Style;
use App\Variation;
use Log;

class AdminEksManager
{
    protected $mStores;
    protected $mRoutes;
    protected $mStyles;
    protected $mVariations;
    protected $eks;
    protected $message;

    public function __construct()
    {
        $this->mStores = new Store();
        $this->mRoutes = new Route();
        $this->mStyles = new Style();
        $this->mVariations = new Variation();
        $this->eks = new EksApi();
        $this->message = new RequestManager();
    }

    /**
     * @return bool
     */
    public function resetStores()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                
                $response = $this->message->send('bearer', 'eks', '/shops?paginated=false', 'GET', $token, '', '', '', []);

                if ($response->status_code == 200) {
                    try {
                        $this->mStores->truncate();
                        $stores = json_decode($response->response);
                        foreach ($stores->content as $store) {
                            if ($store->status === true || $store->comingSoon == true) {
                                $this->mStores->create([
                                    'number' => $store->tdaJda,
                                    'name'   => $store->name,
                                    'ranking'   => $store->ranking ?? 0 ,
                                    'route_id'  => (count($store->routes) > 0 ) ? $store->routes[0] : 0

                                ]);
                            }
                        }
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
                        return false;
                    }
                } else {
                    return false;
                }
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
    public function resetRoutes()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks', '/shops/routes?paginated=false', 'GET', $token, '', '', '', []);
                if ($response->status_code == 200) {
                    try {
                        $this->mRoutes->truncate();
                        $routes = json_decode($response->response);
                        foreach ($routes->content as $route) {
                            $this->mRoutes->create([
                                'name' => $route->name,
                                'description'   => $route->description,
                                'color'   => $route->color->hexadecimalColor ?? 'Sin Color',
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

    /**
     * @return bool
     */
    public function resetStyles()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks-products', '?paginated=false', 'GET', $token, '', '', '', []);                
                if ($response->status_code == 200) {
                    try {
                        $this->mStyles->truncate();
                        $styles = json_decode($response->response);
                        foreach ($styles->content as $style) {
                            Log::info('Saving ... '.$style->style);
                            $this->mStyles->create([
                                'id'                  => $style->id,
                                'deleted'             => $style->deleted,
                                'style'               => $style->style,
                                'jdaDivision'         => $style->jdaDivision,
                                'division_id'         => isset($style->division) ? $style->division->id : '',
                                'jdaDepartment'       => $style->jdaDepartment,
                                'department_id'       => isset($style->department) ? $style->department->id : '',
                                'jdaClass'            => $style->jdaClass,
                                'class_id'            => isset($style->productClass) ? $style->productClass->id : '',
                                'jdaType'             => $style->jdaType,
                                'type_id'             => isset($style->productType) ? $style->productType->id : '',
                                'jdaClassification'   => $style->jdaClassification,
                                'classification_id'   => isset($style->productClassification) ? $style->productClassification->id : '',
                                'jdaFamily'           => $style->jdaFamily,
                                'family_id'           => isset($style->productFamily) ? $style->productFamily->id : '',
                                'jdaBrand'            => $style->jdaBrand,
                                'brand_id'            => isset($style->brand) ? $style->brand->id : '',
                                'jdaProvider'         => $style->jdaProvider,
                                'provider_id'         => isset($style->provider) ? $style->provider->id : '',
                                'description'         => $style->description,
                                'satCode'             => $style->satCode,
                                'satUnit'             => $style->satUnit,
                                'publicPrice'         => $style->publicPrice,
                                'originalPrice'       => $style->originalPrice,
                                'regularPrice'        => $style->regularPrice,
                                'publicUsdPrice'      => $style->publicUsdPrice,
                                'publicQtzPrice'      => $style->publicQtzPrice,
                                'cost'                => $style->cost,
                                'active'              => $style->active,
                                'international'       => $style->international,
                            ]);
                        }
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
                        return false;
                    }
                } else {
                    Log::error(serialize($response));
                    return false;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function resetVariations()
    {
        try {
            $validToken = $this->eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $response = $this->message->send('bearer', 'eks-products', '/variations?paginated=false', 'GET', $token, '', '', '', []);                
                if ($response->status_code == 200) {
                    try {
                        $this->mVariations->truncate();
                        $variations = json_decode($response->response);
                        foreach ($variations->content as $variation) {                            
                            $this->mVariations->create([
                               'id'             => $variation->id,
                               'style_id'       => isset($variation->styleId) ? $variation->styleId : '',
                               'sku'            => $variation->sku,
                               'jdaSize'        => $variation->jdaSize,
                               'size_id'        => isset($variation->productSize) ? $variation->productSize->id : '',
                               'jdaColor'       => $variation->jdaColor,
                               'color_id'       => isset($variation->productColor) ? $variation->productColor->id : '',
                               'jdaPriority'    => $variation->jdaPriority,
                               'priority_id'    => isset($variation->priority) ? $variation->priority->id : '',
                               'active'         => $variation->active,
                            ]);
                        }
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
                        return false;
                    }
                } else {
                    Log::error(serialize($response));
                    return false;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
