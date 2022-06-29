<?php

namespace App\Jobs;

use App\Classes\Eks\EksApi;
use App\Http\Requests\ProductClassesRequest;
use App\Managers\RequestManager;
use ShiftOneLabs\LaravelSqsFifoQueue\Bus\SqsFifoQueueable;
use Illuminate\Contracts\Queue\Job as LaravelJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Log as Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * @param LaravelJob $job
     * @param array $data
     */
    public function handle(LaravelJob $job, $data)
    {
        if ($this->attempts() == 1) {
            try {
                $this->delete();
                $oRequest = $this->getData($data);

                if ($oRequest) {
                    switch ($oRequest->entity) {
                        case 'store':
                            dispatch(new StoresJob($oRequest));
                            break;
                        case 'style':
                            dispatch(new ProductsJob($oRequest));
                            break;
                        case 'variation':
                            dispatch(new VariationJob($oRequest));
                            break;
                            /*
                        case 'size':
                            dispatch(new SizeJob($oRequest));
                            break;
                            */
                        case 'department':
                            dispatch(new DepartmentJob($oRequest));
                            break;
                        case 'route':
                            dispatch(new RouteJob($oRequest));
                            break;
                        case 'color':
                            dispatch(new ColorJob($oRequest));
                            break;
                        case 'division':
                            dispatch(new DivisionJob($oRequest));
                            break;
                        case 'productClassificacion':
                            dispatch(new ProductClassificationJob($oRequest));
                            break;
                        case 'productPriority':
                            dispatch(new ProductPriorityJob($oRequest));
                            break;
                        case 'productColor':
                            dispatch(new ProductColorJob($oRequest));
                            break;
                        case 'productSize':
                            dispatch(new ProductSizeJob($oRequest));
                            break;
                        case 'productClasses':
                            dispatch(new ProductClassesJob($oRequest));
                            break;
                        case 'productType':
                            dispatch(new ProductTypeJob($oRequest));
                            break;
                        case 'productFabric':
                            dispatch(new ProductFabricJob($oRequest));
                            break;
                        case 'productFit':
                            dispatch(new ProductFitJob($oRequest));
                            break;
                        case 'productProvider':
                            dispatch(new ProductProviderJob($oRequest));
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            }
        }
    }

    /**
     * @param $data
     * @return bool|object
     */
    public function getData($data)
    {
        try {
            $resourcesId = json_encode($data['resourceIds']);
            $eks = new EksApi();
            $validToken = $eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $message = new RequestManager();
                $response = $message->send('bearer', 'eks', '/products/'.$data['path'], $data['method'], $token, '', '', $resourcesId, [], []);

                if ($response->status_code == 200) {
                    $dataResponse = (object) [
                        'id' =>  $data['resourceIds'] ?? null,
                        'operation' => $data['operation'] ?? null,
                        'entity' => $data['entity'] ?? null,
                        'data' => $response->response ?? null
                    ];
                    return $dataResponse;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
        }
    }
}
