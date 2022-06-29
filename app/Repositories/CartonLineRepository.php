<?php

namespace App\Repositories;

use App\Carton;
use App\CartonLine;
use App\Variation;
use App\Line;
use DB;

use App\Http\Controllers\ApiResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CartonLineRepository extends BaseRepository
{
    protected $model = 'App\CartonLine';
    protected $mCartonLine;

    public function __construct()
    {
        $this->mCartonLine = new CartonLine();
    }

    /**
   * Crea una caja.
   *
   * @param  $carton_id
   * @param  Array  $cartonData
   * @return void
   */
    public function createCartonLines($wave_id, $carton_id, Array $cartonLineData)
    {
        $total_pieces = 0;
        foreach ($cartonLineData as $key => $line) {
            $lineModel = Line::where([
              ['wave_id', $wave_id],
              ['sku', $line['sku']]
            ])->first();
            if (!empty($lineModel)) {
                $pieces = $lineModel->pieces_per_prepack * $line['prepacks'];
                $total_pieces += $pieces;
                $cartonLine = new CartonLine;
                $cartonLine->carton_id = $carton_id;
                $cartonLine->line_id   = $lineModel->id;
                $cartonLine->prepacks  = $line['prepacks'];
                $cartonLine->pieces    = $pieces;
                $cartonLine->save();
                $lineModel->pieces_in_carton += $pieces;
                $lineModel->prepacks_in_carton += $line['prepacks'];
                $lineModel->save();
            }
        }
        return $total_pieces;
    }

    /**
     * Actualiza una caja.
     *
     * @param \App\Carton $model    modelo de Carton
     * @param Array     $cartonData datos de caja para actualizar
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCartonLines($model, Array $lines)
    {
        $lines = $model->lines();
        foreach ($lines as $key => $ln) {
        }
        if (empty($wave)) {
            return ApiResponses::notFound('La Ola especificada no fue encontrada');
        }
        if (empty($order)) {
            return ApiResponses::notFound('La Orden especificada no fue encontrada');
        }
        return $this->update($model, $cartonData);
    }

    public function getLineByCarton($carton)
    {
        $lines = $this->mCartonLine->where('carton_id', $carton)
            ->with('line')
            ->with('carton')
            ->get();
        return $lines;
    }

  /**
   * Encuentra cajas de determinada ola.
   *
   * @param  Array  $cartonData
   * @return \Illuminate\Http\Response
   */
    public function findByRules()
    {
        //
    }
}
