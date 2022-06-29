<?php

namespace App\Http\Controllers\Api;

use App\CartonLine;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\CartonLineRepository;
use Illuminate\Http\Request;
use Validator;

class CartonLineController extends Controller
{
    public function __construct(Request $request)
    {
        $this->cartonLineRepository = new CartonLineRepository();
    }

    /**
     * Obtiene la lista de lineas en caja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        return $this->cartonLineRepository->paginate($request->per_page);
    }
    /**
     * Guarda una linea en caja nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $v = Validator::make($data, $this->cartonLineRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest($v->messages());
        }
        try {
            return $this->cartonLineRepository->createCartonLine($request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Edita una linea en caja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        $v = Validator::make($request->all(), $this->cartonLineRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $cartonLine = CartonLine::find($request->carton_id);
            if (empty($cartonLine)) {
                return ApiResponses::notFound('No se encontró la caja');
            }
            return $this->cartonLineRepository->updateCarton($cartonLine, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Borra una linea en caja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {
        try {
            if ($request->has('carton_line_id')) {
                $cartonLine = CartonLine::find($request->carton_line_id);
                if (empty($cartonLine)) {
                    return ApiResponses::notFound('No se encontró la linea de caja');
                }
                return $this->cartonLineRepository->delete($request->carton_line_id);
            } else {
                return ApiResponses::badRequest();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
