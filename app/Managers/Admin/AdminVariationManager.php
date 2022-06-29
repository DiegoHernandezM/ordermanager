<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Department;
use App\Division;
use App\Http\Requests\VariationRequest;
use App\Log as Logger;
use App\Managers\RequestManager;
use App\Repositories\LogRepository;
use App\Size;
use App\Style;
use App\Variation;
use Illuminate\Support\Facades\Redis;
use Log;

class AdminVariationManager
{
    protected $mVariation;
    protected $cLog;

    public function __construct()
    {
        $this->mVariation = new Variation();
        $this->cLog = new LogRepository();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewVariation($oRequest)
    {
        try {
            $variations = json_decode($oRequest);
            $notFoundStyles = [];
            foreach ($variations as $key => $variation) {
                $variationFind = $this->mVariation->find($variation->id);
                if (empty($variationFind)) {
                    $additionalCollumns = $this->findStyle($variation->style_id);
                    if (empty($additionalCollumns)) {
                        $log = [
                            'model' => Logger::LOG_SQS,
                            'resourceId' => 'SQSVariations',
                            'message' => __METHOD__. ' Style not found',
                            'user_id' => 1,
                        ];
                        $this->cLog->makeLog($log);
                        $notFoundStyles[] = $variation->style_id;
                    }
                    $this->mVariation->create([
                        'id'    => $variation->id,
                        'sku'   => $variation->sku,
                        'name'  => $variation->jda_size,
                        'color_id'  => $variation->product_color_id,
                        'active'    => true,
                        'style_id'  => $variation->style_id,
                        'jdaSize'   => $variation->jda_size,
                        'size_id'   => null,
                        'jdaColor'  => $variation->jda_color,
                        'jdaPriority' => $variation->jda_priority,
                        'priority_id' => $variation->priority_id,
                        'division_id' => isset($additionalCollumns->division_id) ? $additionalCollumns->division_id : null,
                        'department_id' => isset($additionalCollumns->department_id) ? $additionalCollumns->department_id : null,
                    ]);
                    if (count($notFoundStyles) > 0) {
                        $this->getStyles($notFoundStyles);
                    }
                    Redis::set('sku:'.$variation->sku.':id', $variation->id);
                    Redis::set('sku:'.$variation->sku.':style', $variation->style_id);
                    Redis::set('sku:'.$variation->sku.':department', $additionalCollumns->department_id);
                    Redis::set('sku:'.$variation->sku.':division', $additionalCollumns->division_id);
                }
            }
            return true;
        } catch (\Exception $e) {
            $log = [
                'model' => 'SQSVariations',
                'resourceId' => Logger::LOG_SQS,
                'message' => $e,
                'user_id' => 1,
            ];
            $this->cLog->makeLog($log);
            return false;
        }
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function updateVariation($oRequest)
    {
        try {
            $variations = json_decode($oRequest);
            foreach ($variations as $var) {
                $variationFind = $this->mVariation->find($var->id);
                if (!empty($variationFind)) {
                    $additionalCollumns = $this->findStyle($var->style_id);
                    if (empty($additionalCollumns)) {
                        $log = [
                            'model' => Logger::LOG_SQS,
                            'resourceId' => 'SQSVariations',
                            'message' => __METHOD__. ' Style not found',
                            'user_id' => 1,
                        ];
                        $this->cLog->makeLog($log);
                    }
                    $variationFind->sku = $var->sku;
                    $variationFind->name = $var->jda_size;
                    $variationFind->color_id = $var->product_color_id;
                    $variationFind->active = true;
                    $variationFind->style_id = $var->style_id;
                    $variationFind->jdaSize = $var->jda_size;
                    $variationFind->size_id = null;
                    $variationFind->jdaColor = $var->jda_color;
                    $variationFind->jdaPriority = $var->jda_priority;
                    $variationFind->priority_id = $var->priority_id;
                    $variationFind->division_id = isset($additionalCollumns->division_id) ? $additionalCollumns->division_id : $variationFind->division_id;
                    $variationFind->department_id  = isset($additionalCollumns->department_id) ? $additionalCollumns->department_id : $variationFind->department_id;
                    $variationFind->save();

                    Redis::set('sku:'.$var->sku.':department', $additionalCollumns->department_id);
                    Redis::set('sku:'.$var->sku.':division', $additionalCollumns->division_id);
                }
            }
            return true;
        } catch (\Exception $e) {
            $log = [
                'model' => 'SQSVariations',
                'resourceId' => Logger::LOG_SQS,
                'message' => $e,
                'user_id' => 1,
            ];
            $this->cLog->makeLog($log);
            return false;
        }
    }

    /**
     * @param $data
     * @return bool|object
     */
    public function getStyles($styles)
    {
        try {
            $eks = new EksApi();
            $validToken = $eks->testEks();
            $firstStyle = $styles[0];
            $jsonStyles = json_encode($styles);
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $message = new RequestManager();
                $response = $message->send('bearer', 'eks', '/products/'.$firstStyle, 'GET', $token, '', '', $jsonStyles, [], []);
                if ($response->status_code == 200) {
                    $adminStyle = new AdminStyleManager();
                    $adminStyle->createNewStyle($response->response);
                    $updateVariations = $this->mVariation->whereIn('style_id', $styles)->get();
                    foreach ($updateVariations as $key => $var) {
                        $variationStyle = Style::find($var->style_id);
                        $var->division_id = $variationStyle->division_id;
                        $var->department_id = $variationStyle->department_id;
                        $var->save();
                        Redis::set('sku:'.$var->sku.':department', $var->department_id);
                        Redis::set('sku:'.$var->sku.':division', $var->division_id);
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $log = [
                'model' => 'SQSVariations',
                'resourceId' => Logger::LOG_SQS,
                'message' => $e,
                'user_id' => 1,
            ];
            $this->cLog->makeLog($log);
            return false;
        }
    }

    /**
     * @param $id
     * @return false
     */
    private function findStyle($id) {
        try {
            $style = Style::where('id', $id)->first();
            return $style;
        } catch (\Exception $e) {
            $log = [
                'model' => 'SQSVariations',
                'resourceId' => Logger::LOG_SQS,
                'message' => $e,
                'user_id' => 1,
            ];
            $this->cLog->makeLog($log);
            return false;
        }
    }
}
