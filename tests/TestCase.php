<?php

namespace Tests;

use App\Classes\Eks\EksApi;
use App\Managers\RequestManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Redis;
use Log;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @param $data
     * @return bool|object
     */
    public function getData($data, $path = null)
    {
        try {
            $baseUrl =  ($path != null) ? $path : '/products/';
            $resourcesId = json_encode($data['resourceIds']);
            $eks = new EksApi();
            $validToken = $eks->testEks();
            if ($validToken) {
                $token =  Redis::get('system:eks:token') ?? '';
                $message = new RequestManager();
                $response = $message->send('bearer', 'eks', $baseUrl.$data['path'], $data['method'], $token, '', '', $resourcesId, [], []);
                if ($response->status_code == 200) {
                    $dataResponse = (object) [
                        'id' =>  $data['resourceIds'] ?? null,
                        'operation' => $data['operation'] ?? null,
                        'entity' => $data['entity'] ?? null,
                        'data' => json_decode($response->response) ?? null
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
