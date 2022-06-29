<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Line;
use App\Managers\Admin\AdminOrderManager;
use App\Repositories\LineRepository;
use App\Route;
use App\Wave;
use Illuminate\Http\Request;
use Validator;

class LineController extends Controller
{
    public function __construct(Request $request)
    {
        $this->lineRepository = new LineRepository();
    }

    /**
     * Obtiene la lista de lineas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $lines = Line::where('id', '>', 1000000)->limit(100000)->get();
        foreach ($lines as $key => $ln) {
            $ln->division = $ln->divisionModel->name;
            $categoriesArray = ['JEANS', 'BLUSAS', 'CALZONES', 'CAMISAS', 'PLAYERAS', 'SUDADERAS', 'VESTIDOS', 'TOPS', 'CHAMARRAS', 'SHORTS', 'LEGGINGS', 'ACCESORIOS', 'FALDAS'];
            $classifications = ['P ALTAS', 'P BAJAS'];
            $priorities = ['NUEVO', 'RESURTIDO', 'COMODIN'];
            $ln->category = $categoriesArray[mt_rand(0, 12)];
            $ln->classification = $classifications[mt_rand(0, 1)];
            $ln->priority = $priorities[mt_rand(0, 2)];
            $ln->save();
        }
        return ApiResponses::ok();
        return $this->lineRepository->paginate($request->per_page);
    }
    /**
     * Guarda una linea nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $v = Validator::make($request->all(), $this->lineRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            return $this->lineRepository->createLine($request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Edita una linea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $lineId)
    {
        $v = Validator::make($request->all(), $this->lineRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $line = Line::find($lineId);
            if (empty($line)) {
                return ApiResponses::notFound('No se encontró la linea');
            }
            return $this->lineRepository->updateLine($line, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Borra una linea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request, $lineId)
    {
        try {
            $line = Line::find($lineId);
            if (empty($line)) {
                return ApiResponses::notFound('No se encontró la linea');
            }
            return $this->lineRepository->delete($line->id);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

     /**
     * Obtiene las lineas definidas en una Ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getFromWave(Request $request)
    {
        try {
            if ($request->has('wave_id')) {
                $routes = Route::with([
                    'stores',
                    'stores.orders' => function ($q) use ($request) {
                        $q->whereHas('lines', function ($q) use ($request) {
                            $q->where('wave_id', $request->wave_id);
                        });
                    }
                ])
                ->withCount('stores')->get();
                return ApiResponses::okObject($routes);
            } else {
                return ApiResponses::badRequest();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Busca lineas y las agrupa por sku para conocer numero de cajas por sku.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function findTest(Request $request)
    {
        try {
            // $adminOrderManager = new AdminOrderManager();
            // return $lines = $adminOrderManager->processWaveRoundings(3);
            return $lines = $this->lineRepository->findByWaveRulesSummationBoxes(null, 1);
            // $bleh['piezas'] = $lines->sum('sumpieces');
            // $bleh['cajas'] = $lines->sum('boxes');
            // foreach ($lines as $key => $line) {
            //     if ($line->eq_boxes > $line->boxes && $line->boxes > 0) {
            //         $substract = $line->sumpieces - round(($line->sumpieces * $line->boxes) / $line->eq_boxes);
            //         $bleh['piezas'] = $bleh['piezas'] - $substract;
            //     } elseif ($line->boxes == 0) {
            //         $bleh['piezas'] = $bleh['piezas'] - $line->sumpieces;
            //     }
            // }
            // return $bleh;
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Busca lineas en base a las reglas de negocio en la ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function findLines(Request $request)
    {
        try {
            if ($request->has('wave_id')) {
                $wave = Wave::find($request->wave_id);
                if (empty($wave)) {
                    return ApiResponses::notFound('No se encontró la ola');
                }
            } else {
                return ApiResponses::badRequest();
            }
            
            $productRules = [];
            $storeRules = [];
            if ($wave->business_rules) {
                $rules = explode('|', $wave->business_rules);
                foreach ($rules as $key => $rule) {
                    $rules = explode(',', $rule);
                    if ($rules[0] == 'product') {
                        $rm = array_shift($rules);
                        $productRules[] = $rules;
                    } elseif ($rules[0] == 'store') {
                        $rm = array_shift($rules);
                        $storeRules[] = $rules;
                    }
                }
            }
            return $this->lineRepository->findByRules($storeRules, $productRules, $wave->pieces);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Busca lineas en base a las reglas de negocio puestas por el admin de olas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getLines(Request $request)
    {
        try {
            $productRules = [];
            $storeRules = [];
            if ($request->business_rules) {
                $rules = explode('|', $request->business_rules);
                foreach ($rules as $key => $rule) {
                    $rules = explode(',', $rule);
                    if ($rules[0] == 'product') {
                        $rm = array_shift($rules);
                        $productRules[] = $rules;
                    } elseif ($rules[0] == 'store') {
                        $rm = array_shift($rules);
                        $storeRules[] = $rules;
                    }
                }
            }
            return $this->lineRepository->findLines($productRules, $storeRules);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function complete(Request $request)
    {
        try {
            if (is_numeric($request->line_id)) {
                $line = Line::find($request->line_id);
                if (!empty($line)) {
                    return $this->lineRepository->complete($line);
                } else {
                    return ApiResponses::notFound('No se encontró la linea');
                }
            } else {
                return ApiResponses::badRequest();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * remueve piezas de la ola
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function removePieces(Request $oRequest)
    {
        try {
            $remove = $this->lineRepository->removePiecesFromWave($oRequest->all());
            return ApiResponses::okObject($remove);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
