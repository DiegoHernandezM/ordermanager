<?php

namespace App\Managers\Admin;

use App\Carton;
use App\CartonLine;
use App\Division;
use App\Line;
use App\Log as Logger;
use App\Managers\Admin\AdminSAALMAManager;
use App\Order;
use App\Wave;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use Illuminate\Support\Facades\Redis;

class AdminWamasFileManager
{
    //protected $mColor;

    public function __construct()
    {
        //$this->mColor = new Color();
    }

    public function syncFiles()
    {
        try {
            $disk = Storage::disk('sftpwamas');
            $files = Storage::disk('sftpwamas')->files('from_ssi');
            $files = array_reverse($files);
            $cartons = [];
            $finishedOrders = [];
            $finishedWaves = [];
            // $offset = count($files) > 40 ? 40 : count($files);
            foreach ($files as $key => $file) {
                $rawContent = Storage::disk('sftpwamas')->get($file);
                Storage::disk('local')->put($file, $rawContent);
                $content = json_decode($rawContent);
                if ($content !== null) {
                    if (property_exists($content, 'carton')) {
                        if ($content->carton->routeDescription != 'DEVOLUCION') {
                            $exists = Carton::where('barcode', $content->carton->cartonNumber)->first();
                            if (empty($exists)) {
                                $this->createNewCarton($content);
                            } else { // cuando ya existe un carton con ese boxId
                                $waveId = $content->carton->waveNumber;
                                $isRet = explode('-', $waveId);
                                if (count($isRet) > 1) {
                                    $waveId = $isRet[0];
                                }
                                if ($exists->wave_id != $waveId) {
                                    // save si no es para la misma ola del carton que ya existia con el ultimo digito +1
                                    $num = ltrim($content->carton->cartonNumber, "C-");
                                    $bautizo = substr($num, 0, 10);
                                    $digits = ((int)substr($num, -1, 1) + 1) < 10 ? (int)substr($num, -1, 1) + 1 : 0;
                                    $content->carton->cartonNumber = "C-" . $bautizo . $digits;
                                    $this->createNewCarton($content);
                                } else { // cuando el carton ya no tenga confirmaciones pendientes
                                    if (isset($content->carton->pendingConfirmation)) {
                                        if ($content->carton->pendingConfirmation === false) {
                                            $this->updatePendingCarton($content->carton);
                                        }
                                    }
                                }
                            }
                        }
                        Storage::disk('sftpwamas')->delete($file);
                    } elseif (property_exists($content, 'order-finished')) {
                        if (time() - $disk->lastModified($file) > 1500) {
                            $finishedOrders[] = $file;
                        }
                    } elseif (property_exists($content, 'wave-finished')) {
                        if (time() - $disk->lastModified($file) > 1500) {
                            $finishedWaves[] = $file;
                        }
                    } else {
                        Storage::disk('sftpwamas')->delete($file);
                    }
                } else {
                    $log = Logger::create(['message' => $file . ' could not be parsed.', 'loggable_id' => 1, 'loggable_type' => 'WamasFiles', 'user_id' => 1]);
                }
            }
            if (count($finishedOrders) > 0) {
                foreach ($finishedOrders as $key => $fo) {
                    $rawContent = Storage::disk('sftpwamas')->get($fo);
                    $content = json_decode($rawContent);
                    if ($content !== null) {
                        if (property_exists($content, 'order-finished')) {
                            if ($this->updateOrder($content) === true) {
                                Storage::disk('sftpwamas')->delete($fo);
                            }
                        }
                    }
                }
            }

            if (count($finishedWaves) > 0) {
                foreach ($finishedWaves as $key => $fw) {
                    $rawContent = Storage::disk('sftpwamas')->get($fw);
                    $content = json_decode($rawContent);
                    if ($content !== null) {
                        if (property_exists($content, 'wave-finished')) {
                            if ($this->updateWave($content) === true) {
                                Storage::disk('sftpwamas')->delete($fw);
                            }
                        }
                    }
                }
            }
            $this->registerBoxes();
            return true;
        } catch (\Exception $e) {
            $log = Logger::create(['message' => $e->getMessage() . ' linea ' . $e->getLine(), 'loggable_id' => 1, 'loggable_type' => 'WamasFiles', 'user_id' => 1]);
            return false;
        }
    }

    public function syncFilesDemo()
    {
        try {
            $files = [
                'from_ssi/C-32551578936.json'
            ];
            foreach ($files as $key => $file) {
                $rawContent = Storage::disk('local')->get($file);
                Storage::disk('local')->put($file, $rawContent);
                $content = json_decode($rawContent);
                if (property_exists($content, 'carton')) {
                    if ($content->carton->routeDescription != 'DEVOLUCION') {
                        $exists = Carton::where('barcode', $content->carton->cartonNumber)->first();
                        if (empty($exists)) {
                            $this->createNewCarton($content);
                        } else { // cuando ya existe un carton con ese boxId
                            $waveId = $content->carton->waveNumber;
                            $isRet = explode('-', $waveId);
                            if (count($isRet) > 1) {
                                $waveId = $isRet[0];
                            }
                            if ($exists->wave_id != $waveId) {
                                // save si no es para la misma ola del carton que ya existia con el ultimo digito +1
                                $num = ltrim($content->carton->cartonNumber, "C-");
                                $bautizo = substr($num, 0, 10);
                                $digits = ((int)substr($num, -1, 1) + 1) < 10 ? (int)substr($num, -1, 1) + 1 : 0;
                                $content->carton->cartonNumber = "C-" . $bautizo . $digits;
                                $this->createNewCarton($content);
                            } else { // cuando el carton ya no tenga confirmaciones pendientes
                                if (isset($content->carton->pendingConfirmation)) {
                                    if ($content->carton->pendingConfirmation === false) {
                                        $this->updatePendingCarton($content->carton);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function demoCartons($waveId)
    {
        try {
            $wave = Wave::find($waveId);
            $orders = Order::whereHas('contents', function ($q) use ($waveId) {
                $q->where('wave_id', $waveId);
            })->limit(10)->get();
            $date = new \DateTime();
            $cartonList = [];
            $sortedPieces = 0;
            $businessRules = json_decode($wave->business_rules, true);
            $divisions = $businessRules['divisions'];
            $saalmaDepartment = Division::SAALMA_ALIAS[$divisions[0] - 1];
            foreach ($orders as $key => $ord) {
                $totalPieces = 0;
                $labelDetail = [];
                $labelDetail['dateTime'] = $date->format('y/m/d H:i');
                $labelDetail['division'] = [];
                $labelDetail['priority'] = ['RESURTIDO'];
                $labelDetail['details'] = [];
                $preDetail = [];
                $carton = new Carton;
                $carton->order_id = $ord->id;
                $carton->waveNumber = $wave->id;
                $carton->wave_id = $wave->id;
                $carton->businessName = 'Comercializadora Almacenes Garcia SA de CV';
                $carton->area = 'SORTER1';
                $carton->orderNumber = $ord->id;
                $carton->barcode = 'C-' . rand(10000000000, 99999999999);
                $carton->route = $ord->routeNumber;
                $carton->route_name = $ord->routeDescription;
                $carton->store = $ord->storeNumber;
                $carton->store_name = $ord->storeDescription;

                $carton->save();

                $registerCarton = [];
                $registerCarton['boxId'] = $carton->barcode;
                $registerCarton['fechaCaja'] = $date->format('Y-m-d\TH:i:s');
                $registerCarton['destino'] = $ord->storeNumber;
                $registerCarton['departamento'] = $saalmaDepartment;
                $registerCarton['olaID'] = (int)$waveId;
                $registerCarton['detalleCaja'] = [];

                $lines = $ord->lines()
                    ->where('wave_id', $waveId)
                    ->where('expected_pieces', '>', 0)
                    ->limit(4)
                    ->get();
                foreach ($lines as $key => $ln) {
                    $registerCartonDetail = [];
                    $registerCartonDetail['sku'] = $ln->sku;
                    $registerCartonDetail['cantidadPzasCaja'] = $ln->expected_pieces;
                    $registerCarton['detalleCaja'][] = $registerCartonDetail;

                    $totalPieces += $ln->expected_pieces;
                    $category = explode('-', $ln->style->family->jdaName, 2);
                    $category = $category[1];
                    $division = $ln->style->division->name;
                    if (!in_array($division, $labelDetail['division'])) {
                        $labelDetail['division'][] = $division;
                    }

                    if (isset($preDetail[$category])) {
                        $preDetail[$category]['pieces'] += $ln->expected_pieces;
                    } else {
                        $preDetail[$category] = [];
                        $preDetail[$category]['category'] = $category;
                        $preDetail[$category]['pieces'] = $ln->expected_pieces;
                        $classification = explode('-', $ln->style->classification->jdaName, 2);
                        $preDetail[$category]['classification'] = $classification[1];
                    }

                    $cLine = new CartonLine;
                    $cLine->carton_id = $carton->id;
                    $cLine->line_id = $ln->id;
                    $cLine->pieces = $ln->expected_pieces;
                    $cLine->prepacks = $ln->expected_pieces / $ln->ppk;
                    $cLine->save();

                    $ln->pieces_in_carton = $ln->expected_pieces;
                    $ln->prepacks_in_carton += $ln->expected_pieces / $ln->ppk;

                    $ln->complete = 1;
                    $ln->save();
                }
                foreach ($preDetail as $key => $pD) {
                    $labelDetail['details'][] = $pD;
                }
                $sortedPieces += $totalPieces;
                $carton->labelDetail = json_encode($labelDetail);
                $carton->total_pieces = $totalPieces;
                $carton->save();
                $cartonList[] = $registerCarton;
            }
            $wave->sorted_pieces = $sortedPieces;
            $wave->save();
            return $cartonList;
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return false;
        }
    }

    public function createNewCarton($cartonData)
    {
        try {
            $totalPieces = 0;
            $totalPrepacks = 0;
            $devolution = false;
            $data = $cartonData->carton;
            $waveId = $data->waveNumber;
            $isRet = explode('-', $waveId);
            if (count($isRet) > 1) {
                $waveId = $isRet[0];
            }
            $wave = Wave::find($waveId);
            $sortedPieces = $wave->sorted_pieces;
            $sortedPrepacks = $wave->sorted_prepacks;
            $carton = new Carton;
            $carton->order_id = $data->orderNumber;
            $carton->waveNumber = $waveId;
            $carton->wave_id = $wave->id;
            $carton->businessName = $data->businessName;
            $carton->area = $data->area;
            $carton->orderNumber = $data->orderNumber;
            $carton->barcode = $data->cartonNumber;
            $carton->route = $data->route;
            $carton->route_name = $data->routeDescription;
            $carton->store = $data->store;
            $carton->store_name = $data->storeDescription;
            $carton->pendingConfirmation = $data->pendingConfirmation ?? false;
            if ($devolution === true) {
                $carton->transferNum = "DEVOLUTION";
            }
            $carton->labelDetail = json_encode($data->labelDetail);

            $carton->save();
            $ids = [];
            foreach ($data->contents as $key => $dataLine) {
                if (!empty($ids[$dataLine->lineNumber])) {
                    unset($data->contents[$key]);
                } else {
                    $ids[$dataLine->lineNumber] = 1;
                }
            }

            foreach ($data->contents as $key => $dataLine) {
                $totalPieces += $dataLine->pieces;
                $totalPrepacks += $dataLine->prepacks;
                $line = Line::find($dataLine->lineNumber);
                if ($devolution === false && $carton->pendingConfirmation === false) {
                    $piecesInCarton = $dataLine->pieces + $line->pieces_in_carton;
                    $prepacksInCarton = $dataLine->prepacks + $line->prepacks_in_carton;
                    $line->pieces_in_carton = $piecesInCarton;
                    $line->prepacks_in_carton = $prepacksInCarton;
                    if ($line->expected_pieces >= $piecesInCarton) {
                        $line->complete = 1;
                    }
                    $line->save();
                }

                $cLine = new CartonLine;
                $cLine->carton_id = $carton->id;
                $cLine->line_id = $dataLine->lineNumber;
                $cLine->pieces = $dataLine->pieces;
                $cLine->prepacks = $dataLine->prepacks;
                $cLine->sku = $dataLine->sku;
                $cLine->style = $devolution === false ? $line->style->style : null;
                $cLine->save();
            }
            $sortedPieces += $totalPieces;
            $sortedPrepacks += $totalPrepacks;
            $carton->total_pieces = $totalPieces;
            $carton->save();
            $wave->induction_start = $wave->sorted_boxes == null ? new \DateTime : $wave->induction_start;
            $wave->sorted_pieces = $sortedPieces;
            $wave->sorted_prepacks = $sortedPrepacks;
            $wave->sorted_boxes += 1;
            $wave->status = $wave->status !== Wave::COMPLETED ? Wave::SORTING : $wave->status;
            $wave->area = $carton->area;
            $og = $wave->ordergroup->id;
            $ogSortedPieces = Redis::get('ordergroups:' . $og . ':sorted_pieces');
            Redis::set('ordergroups:' . $og . ':sorted_pieces', $totalPieces + $ogSortedPieces);
            $wave->save();
            if ($devolution === true) {
                return null;
            }
        } catch (\Exception $e) {
            $log = Logger::create(['message' => $e->getMessage() . ' linea ' . $e->getLine(), 'loggable_id' => 1, 'loggable_type' => 'WamasFiles', 'user_id' => 1]);
            return false;
        }
    }

    public function updateWave($waveData)
    {
        try {
            $data = $waveData->{'wave-finished'};
            $waveId = $data->waveNumber;
            $isRet = explode('-', $waveId);
            if (count($isRet) > 1) {
                $waveId = $isRet[0];
            }
            $pendingCartons = Carton::where('wave_id', $waveId)
                ->whereNull('transferNum')
                ->where(function ($q) {
                    $q->whereNull('transferStatus')
                        ->orWhere('transferStatus', 2);
                });
            $send = $pendingCartons->get()->count() == 0 ? true : false;
            if ($send === true) {
                $adminSaalma = new AdminSAALMAManager;
                $registerRequest = [];
                $wave = Wave::find($waveId);
                switch ($data->state) {
                    case 'CANCELLED':
                        $wave->status = Wave::CANCELLED;
                        $wave->save();
                        break;
                    case 'FINISHED':
                        $wave->status = Wave::COMPLETED;
                        $wave->induction_end = new \DateTime;
                        $wave->save();
                        DB::update('update `lines` l set pieces_in_carton =
                            IFNULL((select sum(if(audited_by > 0, pieces_aud, pieces))
                                from carton_lines cl
                                join cartons c on c.id = cl.carton_id
                                where line_id = l.id and wave_id = l.wave_id),0)
                            where wave_id = ?', [$wave->id]);
                        break;
                    default:
                        break;
                }
                $registerRequest["olaID"] = $waveId;
                $result = $adminSaalma->waveFinished($registerRequest);
            }
            return $send;
        } catch (\Exception $e) {
            $log = Logger::create(['message' => $e->getMessage() . ' linea ' . $e->getLine(), 'loggable_id' => 1, 'loggable_type' => 'WamasFiles', 'user_id' => 1]);
            return false;
        }
    }

    public function updatePendingCarton($cartonData)
    {
        $carton = Carton::where('barcode', $cartonData->cartonNumber)->first();
        $previousTotalPieces = $carton->total_pieces;
        $carton->labelDetail = json_encode($cartonData->labelDetail);
        $carton->pendingConfirmation = 0;
        $carton->audited_by = 10000;
        $carton->created_at = new \DateTime;

        $totalPieces = 0;
        //$previousCartonLines = CartonLine::where('carton_id', $carton->id)->delete();

        foreach ($cartonData->contents as $key => $dataLine) {
            $totalPieces += $dataLine->pieces;
            $line = Line::find($dataLine->lineNumber);

            $cLine = CartonLine::where([
                ['carton_id', '=', $carton->id],
                ['line_id', '=', $dataLine->lineNumber]
            ])->first();

            if (empty($cLine)) {
                $cLine = new CartonLine;
                if ($dataLine->pieces > 0) {
                    $line->pieces_in_carton = $line->pieces_in_carton + $dataLine->pieces;
                    $line->prepacks_in_carton = $line->prepacks_in_carton + $dataLine->prepacks;
                    if ($line->pieces_in_carton >= $line->expected_pieces) {
                        $line->complete = 1;
                    }
                    $line->save();
                }
            } else {
                $line->pieces_in_carton = ($line->pieces_in_carton - $cLine->pieces) + $dataLine->pieces;
                $line->prepacks_in_carton = ($line->prepacks_in_carton - $cLine->prepacks) + $dataLine->prepacks;
                if ($line->pieces_in_carton >= $line->expected_pieces) {
                    $line->complete = 1;
                } else {
                    $line->complete = 0;
                }
                $line->save();
                $cLine->carton_id = $carton->id;
                $cLine->line_id = $dataLine->lineNumber;
                $cLine->pieces_aud = $dataLine->pieces;
                $cLine->prepacks_aud = $dataLine->prepacks;
                $cLine->sku = $dataLine->sku;
                $cLine->style = $cLine->line->style->style;
                $cLine->save();
            }
        }
        $carton->total_pieces = $totalPieces;

        if ($totalPieces === 0) {
            $carton->audited_by = 0;
            $carton->total_pieces = $previousTotalPieces;
        }
        $carton->save();
    }

    public function registerBoxes()
    {
        $date = new \DateTime;
        $date->modify('-5 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');
        $date->modify('-5 minutes');
        $formatted_date2 = $date->format('Y-m-d H:i:s');
        $elegibleCartons = Carton::where('created_at', '<=', $formatted_date)
            ->where('created_at', '>=', $formatted_date2)
            ->where('pendingConfirmation', false)
            ->whereNull('transferStatus');

        $registerCartons = $this->getRegisterRequest($elegibleCartons->get());
        if (count($registerCartons) > 0) {
            $adminSaalma = new AdminSAALMAManager;
            $result = $adminSaalma->registerCartons($registerCartons);
            $elegibleCartons->update(['transferStatus' => $result['exito'] === true ? 1 : null]);
            return $result;
        } else {
            return "No hay cajas elegibles para registro";
        }
    }

    public function updateOrder($orderData)
    {
        try {
            $totalPieces = 0;
            $adminSaalma = new AdminSAALMAManager;
            $data = $orderData->{'order-finished'};
            $order = Order::find($data->orderNumber);
            $waveId = $data->waveNumber;
            $isRet = explode('-', $waveId);
            if (count($isRet) > 1) {
                $waveId = $isRet[0];
            }
            $registerRequest = [];
            $registerRequest["olaID"] = $waveId;
            $registerRequest["destino"] = $order->storeNumber;
            $pendingCartons = Carton::where('order_id', $data->orderNumber)
                ->where('wave_id', $waveId)
                ->whereNull('transferNum')
                ->where(function ($q) {
                    $q->whereIn('transferStatus', [2, 3])
                        ->orWhereNull('transferStatus');
                });
            $send = $pendingCartons->get()->count() == 0 ? true : false;
            if (!empty($order)) {
                switch ($data->state) {
                    case 'CANCELLED':
                        $order->status = 0;
                        break;
                    case 'FINISHED':
                        $order->status = 2;
                        if ($send === true) {
                            $result = $adminSaalma->orderFinished($registerRequest);
                            if ($order->allocation) {
                                $unfinishedWaves = Wave::whereHas('lines', function ($q) use ($order) {
                                    $q->where('order_id', $order->id);
                                })->whereNotIn('status', [0, 5])->first();
                                if (empty($unfinishedWaves)) {
                                    $adminSaalma->allocationFinished(["allocationId" => $order->allocation]);
                                }
                            }
                        } else {
                            $registerCartons = $this->getRegisterRequest($pendingCartons->where('pendingConfirmation', false)->where('transferStatus', '!=', 2)->get());
                            if (count($registerCartons) > 0) {
                                $result = $adminSaalma->registerCartons($registerCartons);
                                $pendingCartons
                                    ->where('pendingConfirmation', false)
                                    ->update(['transferStatus' => $result['exito'] === true ? 1 : null]);
                            }
                        }
                        break;
                    default:
                        break;
                }
                $order->save();
            }
            if ($send === true) {
                if (isset($result)) {
                    if ($result['exito'] === true) {
                        $cartonstatus = Carton::where('order_id', $data->orderNumber)
                            ->where('wave_id', $waveId)
                            ->update(['transferStatus' => 5]);
                    }
                }
            }

            return $send;
        } catch (\Exception $e) {
            $log = Logger::create(['message' => $e->getMessage() . ' linea ' . $e->getLine(), 'loggable_id' => 1, 'loggable_type' => 'WamasFiles', 'user_id' => 1]);
            return false;
        }
    }

    public function getRegisterRequest($cartons)
    {
        $registerArray = [];
        $date = new \DateTime();
        foreach ($cartons as $key => $carton) {
            $businessRules = json_decode($carton->wave->business_rules, true);
            $divisions = $businessRules['divisions'];
            $saalmaDepartment = Division::SAALMA_ALIAS[$divisions[0] - 1];
            $registerCarton = [];
            $registerCarton['allocationId'] = $carton->order->allocation ?? null;
            $registerCarton['boxId'] = $carton->barcode;
            $registerCarton['fechaCaja'] = $date->format('Y-m-d\TH:i:s');
            $registerCarton['destino'] = $carton->store;
            $registerCarton['departamento'] = $saalmaDepartment;
            $registerCarton['olaID'] = (int)$carton->wave_id;
            $registerCarton['detalleCaja'] = [];
            $cartonLines = $carton->cartonLines;
            foreach ($cartonLines as $key => $ln) {
                if ($ln->pieces > 0 || $ln->pieces_aud > 0) {
                    if ($carton->audited_by > 0 && $ln->pieces_aud == 0) {
                        continue;
                    }
                    $registerCartonDetail = [];
                    $registerCartonDetail['sku'] = $ln->line->sku;
                    $registerCartonDetail['cantidadPzasCaja'] = $ln->pieces_aud > 0 ? $ln->pieces_aud : $ln->pieces;
                    $registerCarton['detalleCaja'][] = $registerCartonDetail;
                }
            }
            $registerArray[] = $registerCarton;
        }
        return $registerArray;
    }
}
