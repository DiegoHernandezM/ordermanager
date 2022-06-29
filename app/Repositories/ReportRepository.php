<?php

namespace App\Repositories;

use App\Carton;
use App\CartonLine;
use App\Http\Controllers\ApiResponses;
use App\Line;
use App\Order;
use App\PalletContent;
use App\Pallets;
use App\Wave;
use Carbon\Carbon;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use function GuzzleHttp\Psr7\str;

class ReportRepository
{

    protected $cWavesRepository;
    protected $cCartonsRepository;
    protected $carbon;
    protected $mWave;
    protected $mLines;
    protected $mPalletContents;

    public function __construct()
    {
        $this->cWavesRepository = new WaveRepository();
        $this->cCartonsRepository =  new CartonRepository();
        $this->carbon = new Carbon();
        $this->mWave = new Wave();
        $this->mLines = new Line();
        $this->mPalletContents = new PalletContent();
    }

    /**
     * @return mixed
     */
    public function getWaveTodayReport()
    {
        $today = $this->carbon->now()->toDateTimeString();
        $wavesToday = $this->cWavesRepository->getAllWaveFinished($today);
        return $this->parseWaveToWrite($wavesToday);
    }

    /**
     * @return mixed
     */
    public function getWaveWeekReport()
    {
        $startOfWeek = $this->carbon->startOfWeek()->toDateTimeString();
        $endOfWeek = $this->carbon->endOfWeek()->toDateTimeString();
        $wavesWeek = $this->cWavesRepository->getAllWaveFinished($startOfWeek, $endOfWeek);

        return $this->parseWaveToWrite($wavesWeek);
    }

    /**
     * @return mixed
     */
    public function getCartonTodayReport()
    {
        $today = $this->carbon->now()->toDateTimeString();
        $cartonsToday  = $this->cCartonsRepository->getNumberCartons(1, $today);
        return $this->parseCartonToWrite($cartonsToday);
    }

    /**
     * @return mixed
     */
    public function getCartonWeekReport()
    {
        $startOfWeek = $this->carbon->startOfWeek()->toDateTimeString();
        $endOfWeek = $this->carbon->endOfWeek()->toDateTimeString();
        $cartonsWeek  = $this->cCartonsRepository->getNumberCartons(1, $startOfWeek, $endOfWeek);
        return $this->parseCartonToWrite($cartonsWeek);
    }

    /**
     * @param $waves
     * @return mixed
     */
    private function parseWaveToWrite($waves)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'OLA');
        $sheet->setCellValue('B1', 'ESTATUS');
        $sheet->setCellValue('C1', 'PIEZAS');
        $sheet->setCellValue('D1', 'PIEZAS PLANEADAS');
        $sheet->setCellValue('E1', 'PIEZAS SURTIDAS');

        $rows = 2;
        foreach ($waves as $wave) {
            $sheet->setCellValue('A' . $rows, $wave->id);
            $sheet->setCellValue('B' . $rows, Wave::STATUS[$wave->status]);
            $sheet->setCellValue('C' . $rows, $wave->pieces);
            $sheet->setCellValue('D' . $rows, $wave->planned_pieces);
            $sheet->setCellValue('E' . $rows, $wave->sorted_pieces);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="your_file.xls"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }

    /**
     * @param $cartons
     * @return mixed
     */
    private function parseCartonToWrite($cartons)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'OLA');
        $sheet->setCellValue('B1', 'PIEZAS');
        $sheet->setCellValue('C1', 'TRANSFERENCIA');
        $sheet->setCellValue('D1', 'AREA');
        $sheet->setCellValue('E1', 'BARCODE');
        $sheet->setCellValue('F1', 'TIENDA');

        $rows = 2;
        foreach ($cartons as $carton) {
            $sheet->setCellValue('A' . $rows, (int)$carton->wave_id);
            $sheet->setCellValue('B' . $rows, (int)$carton->total_pieces);
            $sheet->setCellValue('C' . $rows, (string)$carton->transferNum);
            $sheet->setCellValue('D' . $rows, (string)$carton->area);
            $sheet->setCellValue('E' . $rows, (string)$carton->barcode);
            $sheet->setCellValue('E' . $rows, (string)$carton->store_name);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="your_file.xls"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }

    /**
     * @return mixed
     */
    public function getWaveDataJson()
    {
        $cartonsPerPallet = '16';
        $waves = Wave::all()->where('status', Wave::COMPLETED);
        $countWaves = count($waves);
        $wavesArray = [];

        foreach ($waves as $data) {
            $wavesArray[] = $data->id;
        }

        $countCartons = Carton::whereIn('wave_id', $wavesArray)->count();
        $totalPieces = Carton::whereIn('wave_id', $wavesArray)->sum('total_pieces');
        $totalPallets = $countCartons / $cartonsPerPallet;
        $arrayResponse = [
            'total_waves'   => (int)$countWaves,
            'total_cartons' => (int)$countCartons,
            'total_pieces'  => (int)$totalPieces,
            'total_pallets' => (int)$totalPallets
        ];

        return array($arrayResponse);
    }

    /**
     * @return mixed
     */
    public function getPlannedWaves($request, $save = false)
    {
        $initDate = $request->initDate;
        $endDate = $request->endDate;

        $query = DB::select('select date(created_at) fecha, (select count(id) from waves where created_at between "' . $initDate . '" and "' . $endDate . '" and status <> 0) olas_planeadas, sum(prepacks) prepacks_planeados, sum(pieces) piezas_planeadas from `lines` l where created_at between "' . $initDate . '" and "' . $endDate . '" and wave_id is not null group by fecha');

        $query2 = DB::select('select week(pc.created_at) semana, date(pc.created_at) fecha, count(distinct pc.wave_id) olas, count(distinct pc.pallet_id) bines, sum(pc.cajas) cajas, sum(pc.cantidad) piezas,TIMEDIFF((select created_at from pallets where date(created_at) = date(pc.created_at) and created_at between "' . $initDate . '" and "' . $endDate . '" and status = 4 order by id desc limit 1), (select created_at from pallets where date(created_at) = date(pc.created_at) and status = 4 and created_at between "' . $initDate . '" and "' . $endDate . '" order by id limit 1)) horas, (select count(id) from pallets where status = 4 and date(created_at) = date(pc.created_at) and created_at between "' . $initDate . '" and "' . $endDate . '") bines_ubicados  from pallet_contents pc where pc.created_at between "' . $initDate . '" and "' . $endDate . '" group by date(pc.created_at)');

        $query3 = DB::select('select week(c.created_at) semana, date(c.created_at) fecha, count(distinct cl.carton_id) cajas, sum(cl.pieces) piezas, sum(cl.prepacks) prepacks, count(distinct c.area) sorters_empleados from carton_lines cl join cartons c on c.id = cl.carton_id where c.area <> "ptl" and c.created_at between "' . $initDate . '" and "' . $endDate . '" group by fecha');

        $query4 = DB::select('select t.fecha, timediff(last, first) horas_surtiendo from (SELECT date(created_at) fecha, min(created_at) first, max(created_at) last from cartons where area <> "ptl" and created_at between "' . $initDate . '" and "' . $endDate . '" group by fecha) t');

        $query5 = DB::select('select count(distinct wave_id) olas_surtidas from cartons where area <> "ptl" and created_at between "' . $initDate . '" and "' . $endDate . '" group by date(created_at)');

        $query6 = DB::select('select week(c.created_at) semana, date(c.created_at) fecha, count(distinct cl.carton_id) cajas, sum(cl.pieces) piezas, sum(cl.prepacks) prepacks, count(distinct c.area) sorters_empleados from carton_lines cl join cartons c on c.id = cl.carton_id where c.area = "ptl" and c.created_at between "' . $initDate . '" and "' . $endDate . '" group by fecha');

        $query7 = DB::select('select t.fecha, timediff(last, first) horas_surtiendo from (SELECT date(created_at) fecha, min(created_at) first, max(created_at) last from cartons where area = "ptl" and created_at between "' . $initDate . '" and "' . $endDate . '" group by fecha) t');

        $query8 = DB::select('select count(distinct wave_id) olas_surtidas from cartons where area = "ptl" and created_at between "' . $initDate . '" and "' . $endDate . '" group by date(created_at)');


        if (count($query) > 0 || count($query2) > 0 || count($query3) > 0 || count($query4) > 0 || count($query5) > 0 || count($query6) > 0 || count($query7) > 0 || count($query8) > 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet()->setTitle("Plan olas");
            $sheet->setCellValue('A1', 'Fecha');
            $sheet->setCellValue('B1', 'Olas planeadas');
            $sheet->setCellValue('C1', 'Prepacks planeados');
            $sheet->setCellValue('D1', 'Piezas planeadas');

            $rows = 2;
            foreach ($query as $res) {
                $sheet->setCellValue('A' . $rows, (string)$res->fecha);
                $sheet->setCellValue('B' . $rows, (int)$res->olas_planeadas);
                $sheet->setCellValue('C' . $rows, (int)$res->prepacks_planeados);
                $sheet->setCellValue('D' . $rows, (int)$res->piezas_planeadas);
                $rows++;
            }

            $sheet2 = $spreadsheet->createSheet()->setTitle("Staging");
            $sheet2->setCellValue('A1', 'Semana');
            $sheet2->setCellValue('B1', 'Fecha');
            $sheet2->setCellValue('C1', 'Olas');
            $sheet2->setCellValue('D1', 'Bines');
            $sheet2->setCellValue('E1', 'Cajas');
            $sheet2->setCellValue('F1', 'Piezas');
            $sheet2->setCellValue('G1', 'Horas');
            $sheet2->setCellValue('H1', 'Bines Ubicados');

            $rows = 2;

            foreach ($query2 as $res) {
                $sheet2->setCellValue('A' . $rows, (int)$res->semana);
                $sheet2->setCellValue('B' . $rows, (string)$res->fecha);
                $sheet2->setCellValue('C' . $rows, (int)$res->olas);
                $sheet2->setCellValue('D' . $rows, (int)$res->bines);
                $sheet2->setCellValue('E' . $rows, (int)$res->cajas);
                $sheet2->setCellValue('F' . $rows, (int)$res->piezas);
                $sheet2->setCellValue('G' . $rows, (string)$res->horas);
                $sheet2->setCellValue('H' . $rows, (int)$res->bines_ubicados);
                $rows++;
            }

            $sheet3 = $spreadsheet->createSheet()->setTitle("Sorter");
            $sheet3->setCellValue('A1', 'Semana');
            $sheet3->setCellValue('B1', 'Fecha');
            $sheet3->setCellValue('C1', 'Olas');
            $sheet3->setCellValue('D1', 'Cajas');
            $sheet3->setCellValue('E1', 'Piezas');
            $sheet3->setCellValue('F1', 'Prepacks');
            $sheet3->setCellValue('G1', 'Sorters');
            $sheet3->setCellValue('H1', 'Horas');

            $rows = 2;

            foreach ($query3 as $key => $res) {
                $sheet3->setCellValue('A' . $rows, (int)$res->semana);
                $sheet3->setCellValue('B' . $rows, (string)$res->fecha);
                $sheet3->setCellValue('C' . $rows, (int)$query5[$key]->olas_surtidas);
                $sheet3->setCellValue('D' . $rows, (int)$res->cajas);
                $sheet3->setCellValue('E' . $rows, (int)$res->piezas);
                $sheet3->setCellValue('F' . $rows, (int)$res->prepacks);
                $sheet3->setCellValue('G' . $rows, (int)$res->sorters_empleados);
                $sheet3->setCellValue('H' . $rows, (string)$query4[$key]->horas_surtiendo);
                $rows++;
            }

            $sheet4 = $spreadsheet->createSheet()->setTitle("PBL");
            $sheet4->setCellValue('A1', 'Semana');
            $sheet4->setCellValue('B1', 'Fecha');
            $sheet4->setCellValue('C1', 'Olas');
            $sheet4->setCellValue('D1', 'Cajas');
            $sheet4->setCellValue('E1', 'Piezas');
            $sheet4->setCellValue('F1', 'Prepacks');
            $sheet4->setCellValue('G1', 'Sorters');
            $sheet4->setCellValue('H1', 'Horas');

            $rows = 2;

            foreach ($query6 as $key => $res) {
                $sheet4->setCellValue('A' . $rows, (int)$res->semana);
                $sheet4->setCellValue('B' . $rows, (string)$res->fecha);
                $sheet4->setCellValue('C' . $rows, (int)$query8[$key]->olas_surtidas);
                $sheet4->setCellValue('D' . $rows, (int)$res->cajas);
                $sheet4->setCellValue('E' . $rows, (int)$res->piezas);
                $sheet4->setCellValue('F' . $rows, (int)$res->prepacks);
                $sheet4->setCellValue('G' . $rows, (int)$res->sorters_empleados);
                $sheet4->setCellValue('H' . $rows, (string)$query7[$key]->horas_surtiendo);
                $rows++;
            }

            if ($save == false) {
                $response = response()->streamDownload(function () use ($spreadsheet) {
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                });
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="reporte_' . $initDate . '.xlsx"');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response->send();
            } else {
                $fileNmae = uniqid();
                $writer = new Xlsx($spreadsheet);
                $writer->save(public_path('files/' . $fileNmae . '.xlsx'));
                return $fileNmae;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function getWaveDataJsonWithParams($request)
    {
        $arrayIds = [];
        if ($request->wave == 0) {
            $dateInit = ($request->init != "") ? Carbon::parse($request->init)->format('Y-m-d') : Wave::where('status', Wave::COMPLETED)->select('created_at')->orderBy('created_at', 'asc')->first();
            $dateEnd = ($request->end != "") ? Carbon::parse($request->end)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $subConsult = "(SELECT TIMEDIFF((SELECT created_at FROM pallets WHERE date(created_at) = '" . $dateInit . "' ORDER BY id DESC LIMIT 1), (SELECT created_at FROM pallets WHERE date(created_at) ='" . $dateEnd . "' ORDER BY id ASC LIMIT 1))) AS horas_picking";
            $ids = $this->cWavesRepository->getIdsWaves($request->area, $request->init, $request->end);
            foreach ($ids as $id) {
                $arrayIds[] = $id->id;
            }
        } else {
            $arrayIds = ($request->wave) ? [$request->wave] : [];
            $subConsult = "(SELECT TIMEDIFF((SELECT created_at FROM pallets WHERE wave_id = '" . $request->wave . "' ORDER BY id DESC LIMIT 1), (SELECT created_at FROM pallets WHERE wave_id ='" . $request->wave . "' ORDER BY id ASC LIMIT 1))) AS horas_picking";
        }
        if (count($arrayIds) > 0) {
            $parseIds = implode(",", $arrayIds);
            $firstId = (int)$arrayIds[0];
            $reverse = array_reverse($arrayIds);
            $lastId = (int)$reverse[0];
            $cartonsPerPallet = 25;
            $totalPercentage = 100;
            $palletsInStaging = 480;
            $query = [
                DB::raw("COUNT(id) AS olas_planeadas"),
                DB::raw("SUM(planned_pieces) AS piezas_planeadas"),
                DB::raw("SUM(total_sku) AS skus"),
                DB::raw("SUM(picked_boxes) AS cajas_pickeadas"),
                DB::raw("SUM(picked_pieces) AS piezas_pickeadas"),
                DB::raw("SUM(sorted_pieces) AS piezas_surtidas"),
                DB::raw("SUM(prepacks) AS prepacks_procesados"),
                DB::raw("SUM(sorted_boxes) AS cajas_surtidas"),
                DB::raw("SUM(picked_boxes) AS total_cajas_picking"),
                DB::raw("ROUND(SUM(picked_pieces) * " . $totalPercentage . " / SUM(planned_pieces)) AS piezas_planeadas_vs_piezas_pickeadas"),
                DB::raw("SUM(sorted_boxes) AS total_cajas_sorter"),
                DB::raw("ROUND(SUM(pieces) * " . $totalPercentage . " / SUM(picked_pieces)) AS piezas_surtidas_vs_piezas_pickeadas"),
                DB::raw("(SELECT SUM(cajas) FROM waves w JOIN pallets p ON p.wave_id = w.id JOIN pallet_contents pc ON pc.pallet_id = p.id WHERE w.id IN(" . $parseIds . ") AND p.status IN(" . Pallets::STAGING . ", " . Pallets::BUFFER . ", " . Pallets::INDUCTION . ")) AS cajas_ubicadas"),
                DB::raw("(SELECT SUM(b.cantidad) FROM pallets AS a JOIN pallet_contents AS b ON a.id = b.pallet_id WHERE a.wave_id IN (" . $parseIds . ") AND a.zone_id != 67 AND (a.status = " . Pallets::RECEIVED . " OR a.status = " . Pallets::INDUCTION . ")) AS piezas_ubicadas"),
                DB::raw("(SELECT TIMEDIFF((SELECT created_at FROM cartons WHERE wave_id = '" . $lastId . "' ORDER BY created_at ASC LIMIT 1), (SELECT updated_at FROM pallets WHERE wave_id = '" . $firstId . "' ORDER BY updated_at ASC limit 1))) AS horas_staging"),
                DB::raw("ROUND((SELECT COUNT(id) FROM `pallets` WHERE wave_id IN(" . $parseIds . "))* " . $totalPercentage . " / " . $palletsInStaging . ",2) AS capacidad_almacenaje"),
                DB::raw("ROUND((SELECT SUM(b.cantidad) FROM pallets AS a JOIN pallet_contents AS b ON a.id = b.pallet_id WHERE a.wave_id IN (" . $parseIds . ") AND a.zone_id != 67 AND (a.status = " . Pallets::RECEIVED . " OR a.status = " . Pallets::INDUCTION . ")) * " . $totalPercentage . " / (SELECT SUM(cantidad) FROM pallet_contents WHERE wave_id IN(" . $parseIds . ")),2) as piezas_pickeadas_vs_piezas_ubicadas"),
                DB::raw("(SELECT TIMEDIFF((SELECT created_at FROM cartons WHERE date(created_at) between '" . $request->init . "' and '" . $request->end . "' or wave_id = '" . $lastId . "' ORDER BY created_at DESC LIMIT 1), (SELECT created_at FROM cartons WHERE date(created_at) between '" . $request->init . "' and '" . $request->end . "' or wave_id = '" . $firstId . "' ORDER BY created_at ASC LIMIT 1))) AS horas_surtido"),
                DB::raw($subConsult),
            ];
            $data["scorecard"] = $this->mWave
                ->select($query)
                ->whereIn('waves.id', $arrayIds)
                ->get();
            return ApiResponses::okObject($data);
        } else {
            return ApiResponses::notFound();
        }
    }

    /**
     * Compara los skus enviados con la tabla lines para verificar los ppk
     * @param $skuArray
     * @return mixed
     */
    public function checkPpk($skuArray)
    {
        foreach ($skuArray as $key => $item) {
            $skuArray[$key] =  array_change_key_case($item, CASE_LOWER);
        }

        $aParseArray = [];
        foreach ($skuArray as $sku) {
            if (count($sku) > 1) {
                $aParseArray[] = [
                    'sku' => $sku['sku'] ?? 100000,
                    'ppk' => $sku['ppk'] ?? 0,
                ];
            }
        }

        $skus = array_column($aParseArray, 'sku');
        $skus = implode(',', $skus);
        $lines = DB::select('WITH rank AS (
          SELECT m.*, ROW_NUMBER() OVER (PARTITION BY sku ORDER BY id DESC) AS rn
          FROM `lines` AS m join waves w on w.id = m.wave_id where w.status > 2
        )
        SELECT wave_id, sku, ppk FROM rank WHERE sku in (' . $skus . ') and wave_id is not null group by sku;');
        //$lines = $this->mLines->whereIn('sku', $skus)->groupBy('sku', 'DESC')->select('wave_id', 'sku', 'ppk')->get()->toArray();

        foreach ($aParseArray as $item => $value) {
            $key = array_search((int)$value['sku'], array_column($lines, 'sku'));
            if ($key !== false) {
                $aParseArray[$item]['wave_id'] = $lines[$key]->wave_id;
                $aParseArray[$item]['ppkOMS'] = $lines[$key]->ppk;
                $aParseArray[$item]['match'] = ($lines[$key]->ppk === $value['ppk']) ? true : false;
            } else {
                $aParseArray[$item]['wave_id'] = null;
                $aParseArray[$item]['ppkOMS'] = null;
                $aParseArray[$item]['match'] =  false;
            }
        }

        return $aParseArray;
    }

    public function getDataWavesOrderFinished($request)
    {
        $dataPicking = $this->mPalletContents
            ->join('departments', 'departments.id', '=', 'pallet_contents.department_id')
            ->join('styles', 'styles.id', '=', 'pallet_contents.style_id')
            ->join('pallets', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->where('pallet_contents.wave_id', $request->wave)
            ->select(
                'pallet_contents.*',
                'departments.name',
                'styles.style',
                'pallets.lpn_transportador',
                'pallets.status',
                'pallets.assignated_by',
                'pallets.inducted_by'
            )
            ->get();

        $dataDistribution = DB::table('lines')
            ->select(
                'lines.wave_id',
                'styles.style',
                'lines.sku',
                DB::raw('sum(pieces) pz_solicitadas'),
                DB::raw('sum(lines.expected_pieces) pz_stock'),
                DB::raw('IFNULL( cast((select sum(cantidad) from pallet_contents where wave_id = lines.wave_id and sku = lines.sku group by sku) as integer), 0) pz_pickeadas'),
                DB::raw('IFNULL( cast((select sum(cantidad) from pallet_contents where wave_id = lines.wave_id and sku = lines.sku group by sku) as integer), 0) - sum(lines.expected_pieces) as diff')
            )
            ->join('styles', 'styles.id', '=', 'lines.style_id')
            ->whereNotNull('wave_id')
            ->where('lines.wave_id', $request->wave)
            ->groupBy('lines.wave_id', 'lines.sku')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Distribucion');
        $sheet->setCellValue('A1', 'Ola');
        $sheet->setCellValue('B1', 'Estilo');
        $sheet->setCellValue('C1', 'Sku');
        $sheet->setCellValue('D1', 'Pz Solicitadas');
        $sheet->setCellValue('E1', 'Pz Stock');
        $sheet->setCellValue('F1', 'Pz Pickeadas');
        $sheet->setCellValue('G1', 'Diferencia');

        $rows = 2;

        foreach ($dataDistribution as $key => $wave) {
            $sheet->setCellValue('A' . $rows, (int)$wave->wave_id);
            $sheet->setCellValueExplicit(
                'B' . $rows,
                $wave->style,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValueExplicit(
                'C' . $rows,
                $wave->sku,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $sheet->setCellValue('D' . $rows, (int)$wave->pz_solicitadas);
            $sheet->setCellValue('E' . $rows, (int)$wave->pz_stock);
            $sheet->setCellValue('F' . $rows, (int)$wave->pz_pickeadas);
            $sheet->setCellValue('G' . $rows, (int)$wave->diff);
            $rows++;
        }
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $worksheet1 =  $spreadsheet->createSheet();
        $worksheet1->setTitle('Picking');
        $worksheet1->setCellValue('A1', 'Fecha y Hora');
        $worksheet1->setCellValue('B1', 'Ola');
        $worksheet1->setCellValue('C1', 'Bin');
        $worksheet1->setCellValue('D1', 'Ultimo estado');
        $worksheet1->setCellValue('E1', 'Estilo');
        $worksheet1->setCellValue('F1', 'Departamento');
        $worksheet1->setCellValue('G1', 'Sku');
        $worksheet1->setCellValue('H1', 'Cajas');
        $worksheet1->setCellValue('I1', 'Piezas');
        $worksheet1->setCellValue('J1', 'Ubicó');
        $worksheet1->setCellValue('K1', 'Inducción por');

        $rowsW = 2;

        foreach ($dataPicking as $key => $pallet) {
            $worksheet1->setCellValue('A' . $rowsW, (string)$pallet->created_at);
            $worksheet1->setCellValue('B' . $rowsW, (int)$pallet->wave_id);
            $worksheet1->setCellValue('C' . $rowsW, (string)$pallet->lpn_transportador);
            $worksheet1->setCellValue('D' . $rowsW, Pallets::STAUS[(int)$pallet['status']]);
            $worksheet1->setCellValueExplicit(
                'E' . $rowsW,
                $pallet->style,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $worksheet1->setCellValue('F' . $rowsW, (string)$pallet->name);
            $worksheet1->setCellValueExplicit(
                'G' . $rowsW,
                $pallet['sku'],
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $worksheet1->setCellValue('H' . $rowsW, (int)$pallet['cajas']);
            $worksheet1->setCellValue('I' . $rowsW, (int)$pallet['cantidad']);
            $worksheet1->setCellValue('J' . $rowsW, (string)$pallet->assignated_by);
            $worksheet1->setCellValue('K' . $rowsW, (string)$pallet->inducted_by);
            $rowsW++;
        }

        $worksheet1->getColumnDimension('E')->setAutoSize(true);
        $worksheet1->getColumnDimension('G')->setAutoSize(true);

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $dataDistribution[0]->wave_id . '_surtido.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response->send();
    }

    public function getShipmentsByOrderGroup($orderGroup)
    {
        $spreadsheet = new Spreadsheet();
        $orders = Order::where('order_group_id', $orderGroup)
            ->select('id', 'storeDescription', 'storeNumber')
            ->with(['cartons' => function ($q) {
                $q->select('wave_id', 'updated_at as fecha', 'order_id', DB::raw("GROUP_CONCAT(distinct IFNULL(shipment, 'CEDIS') SEPARATOR ' ') as folios"))->groupBy('wave_id', 'order_id');
            }])
            ->get();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Folios de embarque');
        $sheet->setCellValue('A1', 'Ola');
        $sheet->setCellValue('B1', 'No. Tienda');
        $sheet->setCellValue('C1', 'Descripcion');
        $sheet->setCellValue('D1', 'Folio (s)');
        $sheet->setCellValue('E1', 'Fecha');
        $rows = 2;
        foreach ($orders as $ord) {
            foreach ($ord->cartons as $fo) {
                $sheet->setCellValue('A' . $rows, $fo->wave_id);
                $sheet->setCellValue('B' . $rows, $ord->storeNumber);
                $sheet->setCellValue('C' . $rows, $ord->storeDescription);
                $sheet->setCellValue('D' . $rows, $fo->folios);
                $sheet->setCellValue('E' . $rows, $fo->fecha);
                $rows++;
            }
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="folio_embarque.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response->send();
    }

    public function getReportShipmentWaveStores($shipmentStores, $shipmentCartons)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Folios por tienda');
        $sheet->setCellValue('A1', 'No. Tienda');
        $sheet->setCellValue('B1', 'Tienda');
        $sheet->setCellValue('C1', 'Folios');

        $rows = 2;

        foreach ($shipmentStores as $key => $store) {
            $sheet->setCellValue('A' . $rows, $store->number);
            $sheet->setCellValue('B' . $rows, $store->name);
            $sheet->setCellValue('C' . $rows, $this->getStringShipments($store->cartons));

            $rows++;
        }

        $worksheet1 =  $spreadsheet->createSheet();
        $worksheet1->setTitle('Cartones');
        $worksheet1->setCellValue('A1', 'Area');
        $worksheet1->setCellValue('B1', 'Box ID');
        $worksheet1->setCellValue('C1', 'Ola');
        $worksheet1->setCellValue('D1', 'Transferencia');
        $worksheet1->setCellValue('E1', 'Piezas');
        $worksheet1->setCellValue('F1', 'Destino');
        $worksheet1->setCellValue('G1', 'Folio embarque');
        $worksheet1->setCellValue('H1', 'Fecha modificación');

        $rowsW = 2;

        foreach ($shipmentCartons as $key => $carton) {
            $worksheet1->setCellValue('A' . $rowsW, (string)$carton->area);
            $worksheet1->setCellValue('B' . $rowsW, (string)$carton->barcode);
            $worksheet1->setCellValue('C' . $rowsW, (string)$carton->wave_id);
            $worksheet1->setCellValue('D' . $rowsW, (string)$carton->transferNum);
            $worksheet1->setCellValue('E' . $rowsW, (int)$carton->total_pieces);
            $worksheet1->setCellValue('F' . $rowsW, (string)$carton->store);
            $worksheet1->setCellValue('G' . $rowsW, (string)$carton->shipment);
            $worksheet1->setCellValue('H' . $rowsW, (string)$carton->updated_at);
            $rowsW++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="folio_embarque.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response->send();
    }

    public function getReportAuditedCartons($request)
    {
        $cartonLines = CartonLine::join('cartons', 'cartons.id', '=', 'carton_lines.carton_id')
            ->leftJoin('users', 'users.id', '=', 'cartons.audited_by')
            ->leftJoin('users as ua', 'ua.id', '=', 'cartons.authorized_by')
            ->select(
                'cartons.wave_id',
                'cartons.barcode',
                'cartons.transferNum',
                'cartons.store',
                'users.name',
                'ua.name as autoriza',
                'cartons.audit_init',
                'cartons.audit_end',
                'carton_lines.sku',
                'carton_lines.pieces',
                'carton_lines.pieces_aud',
                'carton_lines.prepacks',
                'carton_lines.prepacks_aud',
            )
            ->where(function ($q) use ($request) {
                if ($request->dateInit !== null && $request->dateEnd !== null) {
                    $q->whereBetween('cartons.audit_init', [Carbon::parse($request->dateInit)->format('Y-m-d') . ' 00:00:00', Carbon::parse($request->dateEnd)->format('Y-m-d') . ' 23:59:59']);
                } else {
                    $q->where('cartons.audit_init', '>', date('Y-m-d'));
                }
                if ($request->wave > 0) {
                    $q->where('wave_id', '=', $request->wave);
                }
                if ($request->status == 0) {
                    $q->where('transferStatus', 2);
                }
                if ($request->status != 0) {
                    $q->whereNotNull('audit_end');
                }
            })
            ->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cartones auditados');
        $sheet->setCellValue('A1', 'Ola');
        $sheet->setCellValue('B1', 'Carton');
        $sheet->setCellValue('C1', 'Transferencia');
        $sheet->setCellValue('D1', 'Destino');
        $sheet->setCellValue('E1', 'Auditado por');
        $sheet->setCellValue('F1', 'Autorizado por');
        $sheet->setCellValue('G1', 'Inicio Auditoria');
        $sheet->setCellValue('H1', 'Fin Auditoria');
        $sheet->setCellValue('I1', 'Sku');
        $sheet->setCellValue('J1', 'Piezas orig.');
        $sheet->setCellValue('K1', 'Piezas aud.');
        $sheet->setCellValue('L1', 'Prepacks orig.');
        $sheet->setCellValue('M1', 'Prepacks aud.');

        $rows = 2;

        foreach ($cartonLines as $key => $cl) {
            $sheet->setCellValue('A' . $rows, $cl->wave_id);
            $sheet->setCellValue('B' . $rows, $cl->barcode);
            $sheet->setCellValue('C' . $rows, $cl->transferNum);
            $sheet->setCellValue('D' . $rows, $cl->store);
            $sheet->setCellValue('E' . $rows, $cl->name);
            $sheet->setCellValue('F' . $rows, $cl->autoriza);
            $sheet->setCellValue('G' . $rows, $cl->audit_init);
            $sheet->setCellValue('H' . $rows, $cl->audit_end);
            $sheet->setCellValue('I' . $rows, $cl->sku);
            $sheet->setCellValue('J' . $rows, $cl->pieces);
            $sheet->setCellValue('K' . $rows, $cl->pieces_aud);
            $sheet->setCellValue('L' . $rows, $cl->prepacks);
            $sheet->setCellValue('M' . $rows, $cl->prepacks_aud);

            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Cartones auditados.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response->send();
    }

    public function getPalletsReport($wave_id)
    {
        $pallets = PalletContent::join('pallets', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->select(
                'pallets.created_at',
                'pallets.wave_id',
                'pallets.lpn_transportador',
                'sku',
                'cantidad',
                'cajas',
                'pallets.status',
            )
            ->where('pallets.wave_id', '=', $wave_id)
            ->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cartones auditados');
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Ola');
        $sheet->setCellValue('C1', 'BIN');
        $sheet->setCellValue('D1', 'Sku');
        $sheet->setCellValue('E1', 'Piezas');
        $sheet->setCellValue('F1', 'Cajas');
        $sheet->setCellValue('G1', 'Última ubicación');

        $rows = 2;

        foreach ($pallets as $key => $pc) {
            $sheet->setCellValue('A' . $rows, (string)$pc->created_at);
            $sheet->setCellValue('B' . $rows, (int)$pc->wave_id);
            $sheet->setCellValue('C' . $rows, (string)$pc->lpn_transportador);
            $sheet->setCellValue('D' . $rows, (int)$pc["sku"]);
            $sheet->setCellValue('E' . $rows, (int)$pc["cantidad"]);
            $sheet->setCellValue('F' . $rows, (int)$pc["cajas"]);
            $sheet->setCellValue('G' . $rows, Pallets::STAUS[$pc->status]);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="Cartones auditados.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response->send();
    }

    private function getStringShipments($cartons)
    {
        $shipments = '';
        foreach ($cartons as $carton) {
            if ($carton->shipment != null) {
                $shipments .=  ' ' . $carton->shipment . ' ';
            } else {
                $shipments .=  ' CEDIS ';
            }
        }
        return $shipments;
    }
}
