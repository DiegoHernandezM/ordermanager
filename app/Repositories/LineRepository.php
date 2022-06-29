<?php

namespace App\Repositories;

use App\Category;
use App\Http\Controllers\ApiResponses;
use App\Line;
use App\Order;
use App\PalletContent;
use App\Variation;
use App\Wave;
use App\OrderGroup;
use App\Log as Logger;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use Validator;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class LineRepository extends BaseRepository
{
    protected $model = 'App\Line';
  /**
   * Crea una linea.
   *
   * @param  Array  $lineData
   * @return \Illuminate\Http\Response
   */
    public function createLine(Array $lineData)
    {
        if (is_numeric($lineData['sku'])) {
            $variation = Variation::where('sku', $lineData['sku'])->first();
            if (empty($variation)) {
                return ApiResponses::notFound('No se encontro SKU');
            }
            $order = Order::find($lineData['order_id']);
            if (empty($order)) {
                return ApiResponses::notFound('No se encontro la Orden.');
            }
            $createLine = array_merge($this->getLineInfo($variation, $lineData['pieces']), $lineData);
            return $this->create($createLine);
        }
    }

    /**
   * Crea muchas lineas.
   *
   * @param  Array  $lineData
   * @return Integer $lineCounter
   */
    public function createLines(Array $lineData, $orderId)
    {
        $lineCounter = 0;
        $incidents = [];
        $result = new \stdClass();
        foreach ($lineData as $key => $line) {
            $line['order_id'] = $orderId;
            $v = Validator::make($line, $this->getRules());
            if ($v->fails()) {
                $incidents[$line['sku']] = $v->errors();
            } else {
                $variation = Variation::where('sku', $line['sku'])->first();
                if (empty($variation)) {
                    $incidents[$line['sku']] = 'Sku not found';
                } else {
                    $line['variation_id'] = $variation->id;
                    $createLine = array_merge($this->getLineInfo($variation), $line);
                    $this->create($createLine);
                    $lineCounter++;
                }
            }
        }
        $result->incidents = $incidents;
        $result->lines = $lineCounter;
        return $result;
    }

    /**
     * Actualiza una linea.
     *
     * @param \App\Line $model    modelo de Line
     * @param Array     $lineData datos de linea para actualizar
     *
     * @return \Illuminate\Http\Response
     */
    public function updateLine($model, Array $lineData)
    {
        if (is_numeric($lineData['sku'])) {
            $variation = Variation::where('sku', $lineData['sku'])->first();
            if (empty($variation)) {
                return ApiResponses::notFound('No se encontro SKU');
            }
            $order = Order::find($lineData['order_id']);
            if (empty($order)) {
                return ApiResponses::notFound('No se encontro la Orden.');
            }
            $updateLine = array_merge($this->getLineInfo($variation), $lineData);
            return $this->update($model, $updateLine);
        }
    }

    /**
     * Actualiza muchas lineas dependiendo solo si no han sido aceptadas en una OLA.
     *
     * @param \App\Line $model    modelo de Line
     * @param Array     $lineData datos de linea para actualizar
     *
     * @return \Illuminate\Http\Response
     */
    public function updateLines($orderId, Array $lineData)
    {
        foreach ($lineData as $key => $line) {
            $line['order_id'] = $orderId;
            $v = Validator::make($line, $this->getRules());
            if ($v->fails()) {
                return ApiResponses::notFound($v->errors());
            } else {
                $variation = Variation::where('sku', $line['sku'])->first();
                if (empty($variation)) {
                    // TODO : agregar en tabla de incidencias...
                } else {
                    $line['variation_id'] = $variation->id;
                    $createLine = array_merge($this->getLineInfo($variation), $line);
                    $this->create($createLine);
                }
            }
        }
    }

  /**
   * Actualiza el estado completado a una linea.
   *
   * @param \App\Line $model modelo de line
   *
   * @return \Illuminate\Http\Response
   */
    public function complete($model)
    {
        $updateLine = [];
        $updateLine['complete'] = 1;
        return $this->update($model, $updateLine);
    }

    /**
     * Actualiza el múltiplo de prepack de la ola y skus definidos
     *
     * @param  Array  $lineData
     * @return \Illuminate\Http\Response
     */
    public function updateWaveLinesPpk($request, $waveId)
    {
        $lines = Line::where('wave_id', $waveId)
                    ->whereIn('sku', array_column($request->skuArray, 'sku'))
                    ->get();
        $skuArray = $request->skuArray;
        foreach ($lines as $key => $ln) {
            $index = array_search($ln->sku, array_column($skuArray, 'sku'));
            $ln->pieces = round($ln->pieces / $skuArray[$index]['ppkreal']) * $skuArray[$index]['ppkreal'];
            $ln->expected_pieces = $ln->pieces;
            $ln->prepacks = $ln->pieces / $skuArray[$index]['ppkreal'];
            $ln->ppk = $skuArray[$index]['ppkreal'];
            $ln->updated_by = Auth::user()->id;
            $ln->status = 0;
            $ln->save();
        }

        $removeLines = Line::where('wave_id', $waveId)
                        ->whereIn('sku', $request->removeSkuArray)
                        ->update(['wave_id' => null, 'updated_by' => Auth::user()->id]);

        $returnLines = Line::with('style:id,style')
          ->select('variation_id', 'ppk', 'style_id', 'sku', DB::raw('COUNT(*) as stores'), DB::raw('SUM(pieces) as ask_pieces'), DB::raw('SUM(expected_pieces) as in_stock'), DB::raw('SUM(prepacks) as prepacks'), DB::raw('SUM(pieces) - SUM(expected_pieces) as difference'), 'division_id')
          ->where('wave_id', $waveId)
          ->whereIn('sku', array_column($request->skuArray, 'sku'))
          ->groupBy('sku')
          ->get();
        return $returnLines->toArray();
    }

    /**
     * Encuentra lineas en base a reglas de negocio.
     *
     * @param  Array  $lineData
     * @return \Illuminate\Http\Response
     */
    public function adjustQuantitiesByPriority($waveId, $skuLines, $actualStock, $resolved = false)
    {
        $adjustedLines = [];
        $noStockSkus = [];
        $incidents = [];
        if ($resolved === false) {
            $updatePrepacks = DB::update('update `lines` set expected_pieces = pieces, prepacks = pieces / ppk where wave_id = ?', [$waveId]);
        } else {
            DB::table('lines')
              ->where(['wave_id' => $waveId])
              ->whereIn('sku', array_column($actualStock, 'sku'))
              ->update(['expected_pieces' => DB::raw('pieces'), 'prepacks' => DB::raw('pieces / ppk')]);
        }
        $fullLines = Line::where('wave_id', $waveId)
                        ->orderBy('priority', 'desc')->get();
        foreach ($skuLines as $key => $skuLine) {
            $stockIndex = array_search($skuLine['sku'], array_column($actualStock, 'sku'));
            if (false !== $stockIndex) {
                $stock = $actualStock[$stockIndex]['catidadPzas'];
                $ppkSaalma = $actualStock[$stockIndex]['prepack'];
                $ppkPlan = $skuLine['ppk'];
                if ($ppkSaalma > 0 && $ppkPlan != $ppkSaalma && $resolved === false) {
                        $skuLine['ppksaalma'] = $ppkSaalma;
                        $incidents[] = $skuLine;
                        continue;
                } else {
                    $ppk = $ppkPlan;
                }
                if ($skuLine['ask_pieces'] > $stock) {
                    $lines = $fullLines->where('variation_id', $skuLine['variation_id']);
                    $secondPass = false;
                    $countLines = count($lines);

                    $removePieces = $skuLine['ask_pieces'] - $stock;
                    $adjustedLines[] = ['sku' => $skuLine['sku'], 'ask_pieces' => $skuLine['ask_pieces'], 'in_stock' => $stock, 'difference' => $removePieces];
                    if ($removePieces > $ppk) {
                        $removePrepacks = ceil($removePieces / $ppk);
                    } else {
                        $removePrepacks = 1;
                    }
                    // calculamos cuantas piezas quitamos por tienda
                    $firstRemoved = true; // bandera para saber si ya removimos prepack extra
                    // si hay mas prepacks a remover que tiendas ...
                    if ($removePrepacks > $countLines) {
                        // si es flotante debemos poner un prepack extra en alguna tienda
                        $eachRemovePpk = $removePrepacks/$countLines;
                        if (floor($eachRemovePpk) !== $eachRemovePpk) {
                            $decimal = $eachRemovePpk - floor($eachRemovePpk);
                            $firstRemovePpk = ceil($eachRemovePpk); // prepack extra
                            $extras = round($countLines * round($decimal, 2));
                            $firstRemove = $firstRemovePpk * $ppk; // piezas
                            $firstRemoved = false; // bandera
                            $eachRemovePpk = floor($eachRemovePpk); // prepacks a remover en cada tienda
                        }
                    } else { // si hay mas tiendas que prepacks a remover solo le quitamos 1 a cada tienda
                        $eachRemovePpk = 1;
                    }
                    $eachRemove = $eachRemovePpk * $ppk; // piezas
                    // ordenamos las lines por ranking de la store de forma descendente (menor prioridad)
                    foreach ($lines as $key2 => $line) {
                        if ($removePrepacks < 1) {
                            break;
                        }
                        if ($firstRemoved === false && $removePrepacks > 0) {
                            // si la orden de piezas supera firstRemove podemos proseguir con la substraccion
                            if ($line->pieces >= $firstRemove) {
                                $line->expected_pieces = $line->pieces - $firstRemove;
                                $line->prepacks = $line->expected_pieces / $ppk;
                                $removePrepacks = $removePrepacks - $firstRemovePpk;
                                $extras = $extras - 1;
                                if ($extras < 1) {
                                    $firstRemoved = true;
                                }
                                $line->save();

                                // si no, intentamos con eachRemove
                            } elseif ($line->pieces >= $eachRemove) {
                                $line->expected_pieces = $line->pieces - $eachRemove;
                                $line->prepacks = $line->expected_pieces / $ppk;
                                $removePieces = $removePieces - $eachRemove;
                                $removePrepacks = $removePrepacks - $eachRemovePpk;
                                $line->save();
                            } // si no se pudieron remover piezas, pasamos a la siguiente tienda ...
                        // si ya se cumplio firstRemove proseguimos con los eachRemove
                        } elseif ($removePrepacks > 0) {
                            if ($line->pieces >= $eachRemove) {
                                $line->expected_pieces = $line->pieces - $eachRemove;
                                $line->prepacks = $line->expected_pieces / $ppk;
                                $removePieces = $removePieces - $eachRemove;
                                $removePrepacks = $removePrepacks - $eachRemovePpk;
                                $line->save();
                            }
                        }

                        if ($key2+1 === $countLines && $removePrepacks > 0) {
                            $secondPass = true;
                        }
                    }
                    if ($secondPass === true) {
                        $lines2 = $lines->where('expected_pieces', '>', 0);
                        foreach ($lines2 as $key3 => $ln) {
                            if ($removePrepacks <= 0) {
                                break;
                            }
                            if ($removePrepacks > ($ln->expected_pieces / $ppk)) {
                                $removePrepacks = $removePrepacks - ($ln->expected_pieces / $ppk);
                                $ln->expected_pieces = 0;
                                $ln->prepacks = 0;
                                $ln->save();
                            } else {
                                $ln->expected_pieces = $ln->expected_pieces - ($removePrepacks * $ppk);
                                $ln->prepacks = $ln->expected_pieces / $ppk;
                                $removePrepacks = 0;
                                $ln->save();
                            }
                        }
                    }
                }
            } else {
                $noStockSkus[] = $skuLine['sku'];
            }
        }
        $calculate = DB::table('lines')
                        ->where('wave_id', $waveId)
                        ->select('sku', 'ppk', DB::raw('sum(expected_pieces) as expected'))
                        ->groupBy('sku')
                        ->get();

        foreach ($calculate as $key => $cal) {
            $stockIndex = array_search($cal->sku, array_column($actualStock, 'sku'));
            if (false !== $stockIndex) {
                $stock = $actualStock[$stockIndex]['catidadPzas'];
                if ($cal->expected > $stock) {
                    $removePieces = $cal->expected - $stock;
                    $removePrepacks = ceil($removePieces / $cal->ppk);
                    $lines3 = $fullLines->where('sku', $cal->sku)->where('expected_pieces', '>', 0);
                    foreach ($lines3 as $key5 => $ln) {
                        if ($removePrepacks <= 0) {
                            break;
                        }
                        if ($removePrepacks > ($ln->expected_pieces / $cal->ppk)) {
                            $removePrepacks = $removePrepacks - ($ln->expected_pieces / $cal->ppk);
                            $ln->expected_pieces = 0;
                            $ln->prepacks = 0;
                            $ln->save();
                        } else {
                            $ln->expected_pieces = $ln->expected_pieces - ($removePrepacks * $cal->ppk);
                            $ln->prepacks = $ln->expected_pieces / $cal->ppk;
                            $removePrepacks = 0;
                            $ln->save();
                        }
                    }
                }
            }
        }

        if (count($noStockSkus) > 0) {
            $lines = Line::where('wave_id', $waveId)
                    ->whereIn('sku', $noStockSkus)
                    ->where('expected_pieces', '>', 0)
                    ->update(['expected_pieces' => 0, 'prepacks' => 0]);
        }
        if (count($incidents) > 0) {
            $lines = Line::where('wave_id', $waveId)
                        ->whereIn('sku', array_column($incidents, 'sku'))
                        ->get();
            foreach ($lines as $key4 => $ln) {
                $index = array_search($ln->sku, array_column($incidents, 'sku'));
                $ln->ppksaalma = $incidents[$index]['ppksaalma'];
                $ln->status = 1;
                $ln->save();
            }
        }
        // $adjustedLines[] = ['sku' => $skuLine['sku'], 'ask_pieces' => $skuLine['ask_pieces'], 'in_stock' => 0, 'difference' => $skuLine['ask_pieces']];
        // $adjustedLines['incidents'] = $incidents;
        return $incidents;
    }

    /**
     * Encuentra lineas en base a reglas de negocio.
     *
     * @param  Wave  $wave
     * @return \Illuminate\Http\Response
     */
    public function adjustQuantitiesBySupply(Wave $wave)
    {
        $adjustedLines = [];
        $noStockSkus = [];
        $waveId = $wave->id;
        $updatePrepacks = DB::update('update `lines` set expected_pieces = pieces, prepacks = pieces / ppk where wave_id = ?', [$waveId]);
        $suppliedSkus = PalletContent::select('variation_id', DB::raw('CAST(SUM(cantidad) AS INTEGER) as pieces'))
                        ->where('wave_id', $waveId)
                        ->groupBy('variation_id')
                        ->get()
                        ->toArray();
        $expectedSkus = Line::where('wave_id', $waveId)
                ->select('variation_id', 'ppk', DB::raw('CAST(SUM(pieces) AS INTEGER) as ask_pieces'))
                ->groupBy('variation_id')
                ->get()
                ->toArray();
        $fullLines = Line::where('wave_id', $waveId)
                        ->with(['order:id,storePriority'])->get();
        foreach ($expectedSkus as $key => $skuLine) {
            $stockIndex = array_search($skuLine['variation_id'], array_column($suppliedSkus, 'variation_id'));
            if (false !== $stockIndex) {
                $stock = $suppliedSkus[$stockIndex]['pieces'];
                if ($skuLine['ask_pieces'] !== $stock) {
                    $lines = $fullLines->where('variation_id', $skuLine['variation_id']);
                    $secondPass = false;
                    $ppk = $skuLine['ppk'];
                    $countLines = count($lines);
                    if ($skuLine['ask_pieces'] > $stock) {
                        $removePieces = $skuLine['ask_pieces'] - $stock;
                        $adjustedLines[] = ['variation_id' => $skuLine['variation_id'], 'ask_pieces' => $skuLine['ask_pieces'], 'in_stock' => $stock, 'difference' => $removePieces];
                        if ($removePieces > $ppk) {
                            $removePrepacks = ceil($removePieces / $ppk);
                        } else {
                            $removePrepacks = 1;
                        }
                        // calculamos cuantas piezas quitamos por tienda
                        $firstRemoved = true; // bandera para saber si ya removimos prepack extra
                        // si hay mas prepacks a remover que tiendas ...
                        if ($removePrepacks > $countLines) {
                            // si es flotante debemos poner un prepack extra en alguna tienda
                            $eachRemovePpk = $removePrepacks/$countLines;
                            if (floor($eachRemovePpk) !== $eachRemovePpk) {
                                $decimal = $eachRemovePpk - floor($eachRemovePpk);
                                $firstRemovePpk = ceil($eachRemovePpk); // prepack extra
                                $extras = round($countLines * round($decimal, 2));
                                $firstRemove = $firstRemovePpk * $ppk; // piezas
                                $firstRemoved = false; // bandera
                                $eachRemovePpk = floor($eachRemovePpk); // prepacks a remover en cada tienda
                            }
                        } else { // si hay mas tiendas que prepacks a remover solo le quitamos 1 a cada tienda
                            $eachRemovePpk = 1;
                        }
                        $eachRemove = $eachRemovePpk * $ppk; // piezas
                        $lines = $lines->sortByDesc('order.storePriority');
                        // ordenamos las lines por ranking de la store de forma descendente (menor prioridad)
                        foreach ($lines as $key2 => $line) {
                            if ($removePrepacks < 1) {
                                break;
                            }
                            if ($firstRemoved === false && $removePrepacks > 0) {
                                // si la orden de piezas supera firstRemove podemos proseguir con la substraccion
                                if ($line->pieces >= $firstRemove) {
                                    $line->expected_pieces = $line->pieces - $firstRemove;
                                    $line->prepacks = $line->expected_pieces / $ppk;
                                    $removePrepacks = $removePrepacks - $firstRemovePpk;
                                    $extras = $extras - 1;
                                    if ($extras < 1) {
                                        $firstRemoved = true;
                                    }
                                    $line->save();

                                    // si no, intentamos con eachRemove
                                } elseif ($line->pieces >= $eachRemove) {
                                    $line->expected_pieces = $line->pieces - $eachRemove;
                                    $line->prepacks = $line->expected_pieces / $ppk;
                                    $removePieces = $removePieces - $eachRemove;
                                    $removePrepacks = $removePrepacks - $eachRemovePpk;
                                    $line->save();
                                } // si no se pudieron remover piezas, pasamos a la siguiente tienda ...
                            // si ya se cumplio firstRemove proseguimos con los eachRemove
                            } elseif ($removePrepacks > 0) {
                                if ($line->pieces >= $eachRemove) {
                                    $line->expected_pieces = $line->pieces - $eachRemove;
                                    $line->prepacks = $line->expected_pieces / $ppk;
                                    $removePieces = $removePieces - $eachRemove;
                                    $removePrepacks = $removePrepacks - $eachRemovePpk;
                                    $line->save();
                                }
                            }

                            if ($key2+1 === $countLines && $removePrepacks > 0) {
                                $secondPass = true;
                            }
                        }
                        if ($secondPass === true) {
                            $lines = $lines->where('expected_pieces', '>', 0)
                                        ->sortByDesc('order.storePriority');
                            foreach ($lines as $key => $ln) {
                                if ($removePrepacks <= 0) {
                                    break;
                                }
                                if ($removePrepacks > ($ln->expected_pieces / $ppk)) {
                                    $removePrepacks = $removePrepacks - ($ln->expected_pieces / $ppk);
                                    $ln->expected_pieces = 0;
                                    $ln->prepacks = 0;
                                    $ln->save();
                                } else {
                                    $ln->expected_pieces = $ln->expected_pieces - ($removePrepacks * $ppk);
                                    $ln->prepacks = $ln->expected_pieces / $ppk;
                                    $removePrepacks = 0;
                                    $ln->save();
                                }
                            }
                        }
                    }
                    // elseif ($skuLine['ask_pieces'] < $stock) {
                    //     $addPieces = $stock - $skuLine['ask_pieces'];
                    //     $adjustedLines[] = ['variation_id' => $skuLine['variation_id'], 'ask_pieces' => $skuLine['ask_pieces'], 'in_stock' => $stock, 'mustAdd' => $addPieces];
                    //     $addPpks = floor($addPieces / $ppk);
                    //     $firstAdded = true;
                    //     if ($addPpks > $countLines) {
                    //         $eachAddPpk = $addPpks/$countLines;
                    //         if (floor($eachAddPpk) !== $eachAddPpk) {
                    //             $decimal = $eachAddPpk - floor($eachAddPpk);
                    //             $firstAddPpk = ceil($eachAddPpk); // prepack extra
                    //             $extras = round($countLines * round($decimal, 2));
                    //             $firstAdd = $firstAddPpk * $ppk;
                    //             $firstAdded = false;
                    //             $eachAddPpk = floor($eachAddPpk);
                    //         }
                    //     } else {
                    //         $eachAddPpk = 1;
                    //     }
                    //     $eachAdd = $eachAddPpk * $ppk;
                    //     foreach ($result->sortBy('order.storePriority') as $key2 => $line) {
                    //         if ($addPpks < 1) {
                    //             break;
                    //         }
                    //         if ($firstAdded === false && $addPpks > 0) {
                    //             $line->expected_pieces = $line->expected_pieces + $firstAdd;
                    //             $line->prepacks = $line->expected_pieces / $ppk;
                    //             $extras = $extras - 1;
                    //             if ($extras < 1) {
                    //                 $firstAdded = true;
                    //             }
                    //             $addPpks = $addPpks - $firstAddPpk;
                    //         } elseif ($addPpks > 0) {
                    //             $line->expected_pieces = $line->expected_pieces + $eachAdd;
                    //             $line->prepacks = $line->expected_pieces / $ppk;
                    //             $addPpks = $addPpks - $eachAddPpk;
                    //         }
                    //         $line->save();
                    //     }
                    // }
                }
            } else {
                $noStockSkus[] = $skuLine['variation_id'];
            }
        }

        $calculate = DB::table('lines')
                        ->where('wave_id', $waveId)
                        ->select('variation_id', 'ppk', DB::raw('sum(expected_pieces) as expected'))
                        ->groupBy('variation_id')
                        ->get();

        foreach ($calculate as $key => $cal) {
            $stockIndex = array_search($cal->variation_id, array_column($suppliedSkus, 'variation_id'));
            if (false !== $stockIndex) {
                $stock = $suppliedSkus[$stockIndex]['pieces'];
                if ($cal->expected > $stock) {
                    $removePieces = $cal->expected - $stock;
                    $removePrepacks = ceil($removePieces / $cal->ppk);
                    $lines3 = $fullLines->where('variation_id', $cal->variation_id)->where('expected_pieces', '>', 0);
                    foreach ($lines3 as $key5 => $ln) {
                        if ($removePrepacks <= 0) {
                            break;
                        }
                        if ($removePrepacks > ($ln->expected_pieces / $cal->ppk)) {
                            $removePrepacks = $removePrepacks - ($ln->expected_pieces / $cal->ppk);
                            $ln->expected_pieces = 0;
                            $ln->prepacks = 0;
                            $ln->save();
                        } else {
                            $ln->expected_pieces = $ln->expected_pieces - ($removePrepacks * $cal->ppk);
                            $ln->prepacks = $ln->expected_pieces / $cal->ppk;
                            $removePrepacks = 0;
                            $ln->save();
                        }
                    }
                }
            }
        }

        if (count($noStockSkus) > 0) {
            $lines = Line::where('wave_id', $waveId)
                    ->whereIn('variation_id', $noStockSkus)
                    ->where('expected_pieces', '>', 0)
                    ->update(['expected_pieces' => 0, 'prepacks' => 0]);
        }
        $adjustedLines[] = ['sku' => $skuLine['variation_id'], 'ask_pieces' => $skuLine['ask_pieces'], 'in_stock' => 0, 'difference' => $skuLine['ask_pieces']];
        $wave->complete = 1;
        $wave->status = Wave::PICKED;
        $wave->picking_end = new \DateTime();
        $wave->pieces = $wave->lines()->sum('expected_pieces');
        $wave->save();
        $result = ['exito' => true, 'mensaje' => 'La notificación se recibió correctamente.'];
        return $result;
    }

    /**
     * Encuentra lineas en base a reglas de negocio.
     *
     * @param  Array  $lineData
     * @return \Illuminate\Http\Response
     */
    public function findByWaveRules(Int $orderGroupId = null, Array $businessRules, $inputSwitch)
    {
        $divisions = $businessRules['divisions'] ?: [];
        $excludedDepartments = $businessRules['excludedDepartments'] ?: [];
        $excludedClassifications = $businessRules['excludedClassifications'] ?: [];
        $excludedRoutes = $businessRules['excludedRoutes'] ?: [];
        $excludedFamilies = $businessRules['excludedFamilies'] ?: [];
        $excludedStores = $businessRules['excludedStores'] ?: [];
        $excludedProviders = $businessRules['excludedProviders'] ?: [];
        $excludedStyles = $businessRules['excludedStyles'] ?: [];
        $includedDepartments = $businessRules['includedDepartments'] ?: [];
        $includedClassifications = $businessRules['includedClassifications'] ?: [];
        $includedRoutes = $businessRules['includedRoutes'] ?: [];
        $includedFamilies = $businessRules['includedFamilies'] ?: [];
        $includedStores = $businessRules['includedStores'] ?: [];
        $includedProviders = $businessRules['includedProviders'] ?: [];
        $includedStyles = $businessRules['includedStyles'] ?: [];
        // $excludedClasses = $businessRules['excludedClasses'] ?: [];
        // $excludedTypes = $businessRules['excludedTypes'] ?: [];
        $lines = [];
        if ($orderGroupId !== null && $inputSwitch == false) {
            $lines = Line::where('wave_id', null)
                ->whereIn('division_id', $divisions)
                ->whereHas('order', function ($q) use ($excludedRoutes, $excludedStores, $orderGroupId) {
                    $q->where('order_group_id', $orderGroupId);
                    $q->whereHas('store', function ($q) use ($excludedRoutes, $excludedStores) {
                        $q->whereNotIn('id', $excludedStores);
                        $q->whereNotIn('route_id', $excludedRoutes);
                    });
                });
            if (count($excludedDepartments) > 0 || count($excludedClassifications) > 0 || count($excludedFamilies) > 0 || count($excludedProviders) > 0 || count($excludedStyles) > 0) {
                $lines->whereHas('style', function ($q) use ($excludedDepartments, $excludedClassifications, $excludedFamilies, $excludedProviders, $excludedStyles) {
                    $q->whereNotIn('department_id', $excludedDepartments);
                    $q->whereNotIn('classification_id', $excludedClassifications);
                    $q->whereNotIn('family_id', $excludedFamilies);
                    $q->whereNotIn('provider_id', $excludedProviders);
                    $q->whereNotIn('id', $excludedStyles);
                    // $q->whereNotIn('type_id', $excludedTypes);
                });
            }
        } elseif ($orderGroupId !== null && $inputSwitch == true) {
            $lines = Line::where('wave_id', null)
                ->whereIn('division_id', $divisions)
                ->whereHas('order', function ($q) use ($includedRoutes, $includedStores, $orderGroupId) {
                    $q->where('order_group_id', $orderGroupId);
                    $q->whereHas('store', function ($q) use ($includedRoutes, $includedStores) {
                        if (count($includedStores) > 0) {
                            $q->whereIn('id', $includedStores);
                        }
                        if (count($includedRoutes) > 0) {
                            $q->whereIn('route_id', $includedRoutes);
                        }
                    });
                });
            if (count($includedDepartments) > 0 || count($includedClassifications) > 0 || count($includedFamilies) > 0 || count($includedProviders) > 0 || count($includedStyles) > 0) {
                $lines->whereHas('style', function ($q) use ($includedDepartments, $includedClassifications, $includedFamilies, $includedProviders, $includedStyles) {
                    if (count($includedDepartments) > 0) {
                        $q->whereIn('department_id', $includedDepartments);
                    }
                    if (count($includedClassifications) > 0) {
                        $q->whereIn('classification_id', $includedClassifications);
                    }
                    if (count($includedFamilies) > 0) {
                        $q->whereIn('family_id', $includedFamilies);
                    }
                    if (count($includedProviders) > 0) {
                        $q->whereIn('provider_id', $includedProviders);
                    }
                    if (count($includedStyles) > 0) {
                        $q->whereIn('id', $includedStyles);
                    }
                });
            }
        }

        return $lines;
    }

  /**
   * Encuentra lineas en base a reglas de negocio.
   *
   * @param  Array  $lineData
   * @return \Illuminate\Http\Response
   */
    public function findByRules(Array $storeRules, Array $productRules, Int $maxPieces = null)
    {
        $lines = Line::whereHas('style', function ($q) use ($productRules) {
            $q->where($productRules);
        })->whereHas('order', function ($q) use ($storeRules) {
            $q->whereHas('store', function ($q) use ($storeRules) {
                $q->where($storeRules);
            });
        })
        ->get();
        $lineArray = [];
        foreach ($lines as $key => $line) {
            $lineArray[] = $line->id;
        }
        $lineArray = implode(',', $lineArray);

        if ($maxPieces > 0 && strlen($lineArray) > 0) {
            $piecesLogic = DB::select('SELECT pieces, @total := @total + pieces AS total, id as line_id
        FROM (`lines`, (select @total := 0) t)
        WHERE id IN ('.$lineArray.') AND @total <= '.$maxPieces);
            foreach ($piecesLogic as $key => $pl) {
                $foundLines[] = $pl->line_id;
            }
            $lines = Line::whereIn('id', $foundLines)->get();
        }

        return ApiResponses::okObject($lines);
    }

    /**
     * Obtiene sumatoria de piezas por cada sku en lineas con ola determinada.
     *
     * @param  Wave  $wave
     * @return Array
     */
    public function WaveLinesSumBySku(Wave $wave)
    {
        $lines = $wave->lines()
          ->with('style:id,style')
            ->select('variation_id', 'ppk', 'style_id', 'sku', DB::raw('COUNT(*) as stores'), DB::raw('SUM(pieces) as ask_pieces'), DB::raw('SUM(expected_pieces) as in_stock'), DB::raw('SUM(prepacks) as prepacks'), DB::raw('SUM(pieces) - SUM(expected_pieces) as difference'), 'division_id', DB::raw('SUM(pieces_in_carton) as sorted_pieces'))
            ->orderBy('style_id', 'asc')
            ->groupBy('sku')
          ->get();
        return $lines->toArray();
    }

    /**
     * Obtiene skus con problema de múltiplo de prepack.
     *
     * @param  Wave  $wave
     * @return Array
     */
    public function getUnresolvedWaveSkus(Wave $wave)
    {
        $lines = $wave->lines()
          ->with('style:id,style')
          ->select('variation_id', 'ppk', 'style_id', 'sku', 'ppksaalma', DB::raw('"" as ppkreal'))
          ->where('status', 1)
          ->groupBy('sku')
          ->get();
        return $lines->toArray();
    }

    /**
     * Obtiene resumen de piezas por unico sku.
     *
     * @param  Wave  $wave
     * @param  Int  $variation
     * @return Array
     */
    public function WaveLinesSku(Wave $wave, Int $variation)
    {
        $lines = $wave->lines()
          ->with('order:id,storePriority,storeNumber')
          ->select('lines.id', 'lines.pieces', 'lines.order_id', 'lines.ppk', 'lines.expected_pieces', 'lines.prepacks', 'lines.sku', DB::raw('lines.expected_pieces - lines.pieces as difference', 'order.storePriority', 'order.storeNumber'))
          ->where('variation_id', $variation)
          ->get();
        return $lines->toArray();
    }

    /**
     * Obtiene sumatoria de piezas esperadas por sku en lineas con ola determinada.
     *
     * @param  Wave  $wave
     * @return Array
     */
    public function waveLinesExpectedSumBySku(Wave $wave)
    {
        $lines = $wave->lines()
          ->orderBy('lines.style_id')
          ->with('department:id,ranking')
          ->select(
              'lines.id',
              'lines.sku',
              'lines.department_id',
              'lines.style_id',
              DB::raw('substr(product_families.jdaName, 5) familiaDescripcion'),
              DB::raw('left(product_families.jdaName, 3) familia'),
              'product_families.ranking as familiaRanking',
              DB::raw('CAST(SUM(lines.expected_pieces) AS INTEGER) as cantidad')
          )
          ->join('departments', 'lines.department_id', '=', 'departments.id')
          ->join('styles', 'styles.id', '=', 'lines.style_id')
          ->join('product_families', 'styles.family_id', '=', 'product_families.id')
          ->where('lines.expected_pieces', '>', 0)
          ->groupBy('lines.sku')
          ->get();
        return $lines;
    }

    /**
     * Encuentra lineas en base a reglas de negocio.
     *
     * @param  Array  $lineData
     * @return \Illuminate\Http\Response
     */
    public function findLines(Array $productRules, Array $storeRules)
    {
        // $categories = Category::where('depends_id', 0)
        //     ->select('id', 'name')
        //     ->get()
        //     ->toArray();

        $orders = Order::with(['lines' => function ($q) use ($productRules) {
            $q->select('id', 'department', 'style', 'description', 'size', 'pieces', 'prepacks', 'sku', 'style_id', 'order_id');
            $q->where('wave_id', null); // buscamos solo los que no tengan una ola asignada
            $q->whereHas('style', function ($q) use ($productRules) {
                $q->where($productRules); // ponemos las reglas de negocio relacionadas al producto
            });
        },
            'store'
        ])
        ->whereHas('store', function ($q) use ($storeRules) {
            $q->where($storeRules); // ponemos las reglas de negocio relacionadas a la tienda
        })
        ->limit(50) /* QUITAR EL LIMITE AL FINAL!!!!!!!!!!!!!! */
        ->get();

        return ApiResponses::okObject($orders);

        // $lookup = array_column($categories, null, 'id');
        // foreach ($orders as $key => $ord) {
        //     foreach ($ord['lines'] as $key => $line) {
        //         $parentCategory = $line['product']['parent_category_ids'][0]['category']['depends_id'];
        //         unset($line['product']);
        //         $lookup[$parentCategory]['orders'][$ord['id']]['id'] = $ord['id'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['merc_id'] = $ord['merc_id'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['storeNumber'] = $ord['store']['number'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['store'] = $ord['store']['name'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['route'] = $ord['store']['route_id'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['store_id'] = $ord['store_id'];
        //         $lookup[$parentCategory]['orders'][$ord['id']]['lines'][] = $line;
        //     }
        // }
        // return ApiResponses::okObject($lookup);
    }

  /**
   * Actualiza una linea.
   *
   * @param  Array  $lineData
   * @return \Illuminate\Http\Response
   */

    protected function getLineInfo($variation, $pieces = null)
    {
        $style = $variation->style;
        $equivalent_boxes = 0;
        $rounded_boxes = 0;
        if ($pieces) {
            $equivalent_boxes = round($pieces / $variation->ppc, 2);
        }
        if ($equivalent_boxes < 1 && $equivalent_boxes > 0.3) {
            $rounded_boxes = 1;
        } elseif ($equivalent_boxes > 1) {
            $rounded_boxes = round($equivalent_boxes);
        }
        $line = [
            'department'  => $style->department,
            'description' => $style->name,
            'style'       => $style->internal_reference,
            'barcode'     => $style->internal_reference,
            'sku'         => $variation->sku,
            'size'        => $variation->name,
            'provider'    => $style->provider,
            'style_id'  => $style->id,
            'variation_id'=> $variation->id,
            'equivalent_boxes' => $equivalent_boxes,
            'rounded_boxes' => $rounded_boxes,
        ];
        return $line;
    }

    public function getPpkCorrections()
    {
        $carbon = new Carbon();

        $dateInit = new Carbon('last sunday');
        $dateInit = $dateInit->format('Y-m-d H:i:s');

        $dateEnd = $carbon->endOfWeek();
        $dateEnd = $dateEnd->format('Y-m-d H:i:s');

        $getPpk = Line::select('sku', 'ppk')->whereNotNull('ppksaalma')->whereBetween('created_at', [$dateInit, $dateEnd])->groupBy('sku')->get();

        if (count($getPpk) > 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet()->setTitle("Correcciones PPK");
            $sheet->setCellValue('A1', 'SKU');
            $sheet->setCellValue('B1', 'PPK');

            $rows = 2;
            foreach ($getPpk as $res) {
                $sheet->setCellValue('A' . $rows, (string)$res->sku);
                $sheet->setCellValue('B' . $rows, (string)$res->ppk);
                $rows++;
            }

            $fileName = 'skus-para-corregir-'.$carbon->endOfWeek()->format('Y-m-d');
            $writer = new Xlsx($spreadsheet);
            $writer->save(public_path('files/'.$fileName.'.xlsx'));
            return $fileName;
        } else {
            return false;
        }
    }

    /**
     * @param $oRequest
     * @return $dataResponse
     */
    public function removePiecesFromWave($oRequest)
    {

        $ogId = Wave::where('id', $oRequest['waveId'])->first();

        Line::whereIn('sku', $oRequest['skus'])->update(['wave_id' => null]);

        $og = OrderGroup::find($ogId['order_group_id']);

        $wave = Wave::find($oRequest['waveId']);
        $wave->pieces = $wave->lines()->sum('expected_pieces');
        $wave->planned_pieces = $wave->lines()->sum('pieces');
        $wave->total_sku = $wave->lines()->distinct('sku')->count();
        $wave->save();

        $recalculate = new OrderGroupRepository();
        $recalculate->calculatePiecesForRedis($og);

        return $response = ['status' => 200, 'message' => 'Se han removido los skus exitosamente'];
    }
}
