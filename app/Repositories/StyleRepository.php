<?php

namespace App\Repositories;

use App\Http\Controllers\ApiResponses;
use Illuminate\Http\Request;
use App\Managers\RequestManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use DB;

class StyleRepository extends BaseRepository
{

    /**
     * Create a new style
     * @param $oRequest
     */
    public function createStyle($oRequest)
    {
        try {
            $arraySkus = [];
            $skuList = explode(',', $oRequest['skus']);

            foreach ($skuList as $skus) {
                $arraySkus[] = [
                    'sku'      => $skus,
                    'size'     => 0,
                    'color'    => 68,
                    'priority' => 'T'
                ];
            }

            $oRequest['skus'] = $arraySkus;
            $oRequest['class'] = $oRequest['classes'];
            unset($oRequest['classes']);

            $arrayRequest[] = $oRequest;

            $jda_service_user = config('systems.eks.userEksJda');
            $jda_service_password = config('systems.eks.passwordEksJda');

            $message = new RequestManager();
            $response = $message->send('basic', 'eks', '/jda/styles', 'POST', '', $jda_service_user, $jda_service_password, '', [], $arrayRequest);

            if ($response->status_code == 200) {
                    $dataResponse = [
                        'status'  => $response->status_code,
                        'message' =>  'Estilo creado exitosamente' ?? null
                    ];
            } else {
                $dataResponse = [
                    'status'  => $response->status_code,
                    'message' => $response->status_message
                ];
            }

                return $dataResponse;
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
        }
    }

    /**
     * get styles by og id
     * @param $oRequest
     */
    public function getStyles($oRequest)
    {

        $styles = DB::table('lines as l')
                    ->select('s.id', 's.style', DB::raw('sum(l.pieces) as pzas'))
                    ->join('orders as o', 'o.id', '=', 'l.order_id')
                    ->join('styles as s', 's.id', '=', 'l.style_id')
                    ->where('o.order_group_id', $oRequest['orderGroupId'])
                    ->whereIn('l.division_id', $oRequest['divisionId'])
                    ->whereNull('l.wave_id')
                    ->groupBy('s.style')
                    ->orderBy('pzas', 'desc')
                    ->get();

        return $styles;
    }
}
