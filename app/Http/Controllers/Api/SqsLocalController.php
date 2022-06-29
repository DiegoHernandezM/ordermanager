<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Jobs\ColorJob;
use App\Jobs\DepartmentJob;
use App\Jobs\DivisionJob;
use App\Jobs\HandlerJob;
use App\Jobs\ProductClassesJob;
use App\Jobs\ProductClassificationJob;
use App\Jobs\ProductColorJob;
use App\Jobs\ProductFabricJob;
use App\Jobs\ProductFitJob;
use App\Jobs\ProductPriorityJob;
use App\Jobs\ProductProviderJob;
use App\Jobs\ProductSizeJob;
use App\Jobs\ProductsJob;
use App\Jobs\ProductTypeJob;
use App\Jobs\RouteJob;
use App\Jobs\StoresJob;
use App\Jobs\VariationJob;
use App\Jobs\ProductProvider;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;

class SqsLocalController extends Controller
{

    protected $handleJob;

    public function __construct()
    {
        $this->handleJob = new HandlerJob();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendMessage(Request $request)
    {
        try {
            $dataText = str_replace(array("\n", "\r", "\t", '\"'), '', $request->message);
            $dataText = (string) $dataText;
            $data = (array)json_decode($dataText);
            $getData = $this->handleJob->getData($data);
            if ($this->handleSimulation((object)$getData)) {
                return ApiResponses::ok();
            } else {
                return ApiResponses::internalServerError();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError();
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function handleSimulation($data)
    {
        $data = (object) $data;
        if ($data) {
            switch ($data->entity) {
                case 'store':
                    dispatch(new StoresJob($data));
                    break;
                case 'style':
                    dispatch(new ProductsJob($data));
                    break;
                case 'variation':
                    dispatch(new VariationJob($data));
                    break;
                case 'department':
                    dispatch(new DepartmentJob($data));
                    break;
                case 'route':
                    dispatch(new RouteJob($data));
                    break;
                case 'color':
                    dispatch(new ColorJob($data));
                    break;
                case 'division':
                    dispatch(new DivisionJob($data));
                    break;
                case 'productClassificacion':
                    dispatch(new ProductClassificationJob($data));
                    break;
                case 'productPriority':
                    dispatch(new ProductPriorityJob($data));
                    break;
                case 'productColor':
                    dispatch(new ProductColorJob($data));
                    break;
                case 'productSize':
                    dispatch(new ProductSizeJob($data));
                    break;
                case 'productClasses':
                    dispatch(new ProductClassesJob($data));
                    break;
                case 'productType':
                    dispatch(new ProductTypeJob($data));
                    break;
                case 'productFabric':
                    dispatch(new ProductFabricJob($data));
                    break;
                case 'productFit':
                    dispatch(new ProductFitJob($data));
                    break;
                case 'productProvider':
                    dispatch(new ProductProviderJob($data));
                    break;
                default:
                    # code...
                    break;
            }
            return true;
        }
    }
}
