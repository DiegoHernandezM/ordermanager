<?php

namespace App\Managers\Admin;

use App\Classes\Eks\EksApi;
use App\Department;
use App\Http\Requests\StyleRequest;
use App\Managers\MessagesManager;
use App\ProductFamily;
use App\Repositories\LogRepository;
use App\Style;
use App\Log as Logger;
use App\Variation;
use Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use DB;

class AdminStyleManager
{

    protected $mStyle;
    protected $cLog;
    protected $mFamily;
    protected $mVariation;

    public function __construct()
    {
        $this->mStyle = new Style();
        $this->cLog = new LogRepository();
        $this->mVariation = new Variation();
        $this->mFamily = new ProductFamily();
    }

    /**
     * @return bool
     */
    public function createNewStyle($oRequest)
    {
        try {
            try {
                $styles = json_decode($oRequest);
                foreach ($styles as $key => $style) {
                    $find = $this->mStyle->find($style->id);
                    if (!$find) {
                        if (!isset($style->department)) {
                            if (isset($style->jdaDepartment)) {
                                $department = Department::where('jda_id', $style->jdaDepartment)->first();
                            }
                        }
                        $this->mStyle->create([
                            'id'                  => $style->id,
                            'deleted'             => $style->deleted,
                            'style'               => $style->style,
                            'jdaDivision'         => $style->jdaDivision,
                            'division_id'         => isset($style->division) ? $style->division->id : $style->jdaDivision,
                            'jdaDepartment'       => $style->jdaDepartment,
                            'department_id'       => (!empty($style->department)) ? $style->department->id : (!empty($department) ? $department->id : ''),
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
                }
                return true;
            } catch (Exception $e) {
                $log = [
                    'model' => 'SQSVariations',
                    'resourceId' => Logger::LOG_SQS,
                    'message' => $e,
                    'user_id' => 1,
                ];
                $this->cLog->makeLog($log);
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
     * @return bool
     */
    public function updateStyle($oRequest)
    {
        try {
            $styles = json_decode($oRequest);
            foreach ($styles as $style) {
                if (!isset($style->department)) {
                    $department = Department::where('jda_id', $style->jdaDepartment)->first();
                }
                $product = $this->mStyle->find($style->id);
                if (!empty($product)) {
                    if ($product->department_id != (isset($style->department) ? $style->department->id : $department->id) || $product->division_id != $style->jdaDivision) {
                        $this->updateVariations($style, (isset($style->department) ? $style->department->id : $department->id));
                    }

                    $product->deleted = isset($style->deleted) ? $style->deleted : false;
                    $product->style = $style->style;
                    $product->jdaDivision = $style->jdaDivision;
                    $product->division_id = $style->jdaDivision ?? '';
                    $product->jdaDepartment = $style->jdaDepartment;
                    $product->department_id = isset($style->department) ? $style->department->id : $department->id;
                    $product->jdaClass = $style->jdaClass;
                    $product->class_id = isset($style->productClass) ? $style->productClass->id : '';
                    $product->jdaType = $style->jdaType;
                    $product->type_id = isset($style->productType) ? $style->productType->id : '';
                    $product->jdaClassification = $style->jdaClassification;
                    $product->classification_id = isset($style->productClassification) ? $style->productClassification->id : '';
                    $product->jdaFamily = $style->jdaFamily;
                    $product->family_id = isset($style->productFamily) ? $style->productFamily->id : '';
                    $product->jdaBrand = $style->jdaBrand;
                    $product->brand_id = isset($style->brand) ? $style->brand->id : '';
                    $product->jdaProvider = $style->jdaProvider;
                    $product->provider_id = isset($style->provider) ? $style->provider->id : '';
                    $product->description = $style->description;
                    $product->satCode = $style->satCode;
                    $product->satUnit = $style->satUnit;
                    $product->publicPrice = $style->publicPrice;
                    $product->originalPrice = $style->originalPrice;
                    $product->regularPrice = $style->regularPrice;
                    $product->publicUsdPrice = $style->publicUsdPrice;
                    $product->publicQtzPrice = $style->publicQtzPrice;
                    $product->cost = $style->cost;
                    $product->active = $style->active;
                    $product->international = $style->international;
                    $product->save();
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
     * @param $variations
     * @param $product
     * @return bool
     */
    public function updateVariations($style, $department)
    {
        $variations = $this->mVariation->where('variations.style_id', '=', $style->id)->get();

        if (count($variations) > 0) {
            foreach ($variations as $variation) {
                $variation->department_id = $department;
                $variation->division_id = $style->jdaDivision;
                $variation->save();

                Redis::set('sku:'.$variation->sku.':department', $variation->department_id);
                Redis::set('sku:'.$variation->sku.':division', $variation->division_id);
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function updateFamilyStyle()
    {
        try {
            $styles = $this->mStyle
                ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                ->where('styles.classification_id', '!=', DB::raw('product_families.classification_id'))
                ->whereNotNull('product_families.classification_id')
                ->select('styles.*', 'product_families.classification_id as pfcl_id')->limit(10)->get();
            if (count($styles) > 0) {
                foreach ($styles as $style) {
                    $style->classification_id = $style->pfcl_id;
                    $style->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            $log = [
                'model' => 'UpdateStyleClassification',
                'resourceId' => Logger::LOG_SYSTEM,
                'message' => $e->getMessage(),
                'user_id' => 1,
            ];
            $this->cLog->makeLog($log);
            return false;
        }
    }
}
