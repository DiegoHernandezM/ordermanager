<?php

namespace App\Repositories;

use App\Carton;
use App\Http\Controllers\ApiResponses;
use Carbon\Carbon;
use DB;

class ScannerBoxRepository
{
    protected $mCarton;
    protected $cApiResponse;

    public function __construct()
    {
        $this->cApiResponse = new ApiResponses();
        $this->mCarton = new Carton();
    }

    /**
     * @param $barcode
     * @return bool|object
     */
    public function getInfoScan($embedcode = null, $barcode = null)
    {
        $carton = $this->mCarton->where('barcode', 'like', 'C-'.$embedcode.'%')->orderByDesc('id')->first();
        if ($carton != null) {
            try {
                $parseResponse = $this->orderRequestInfo($carton);
            } catch (Exception $e) {
                return ApiResponses::internalServerError($e);
            }
            
            return $parseResponse;
        } else {
            return false;
        }
    }

    /**
     * @param $carton
     * @return object
     */
    public function orderRequestInfo($carton)
    {
        $cartonNumber  = $carton->barcode;
        $cartonsWaveStore = Carton::select('id', 'barcode')
        ->where([
            ['wave_id', '=', $carton->wave_id],
            ['store', '=', $carton->store],
        ])->get();
        $index = 0;
        foreach ($cartonsWaveStore as $key => $c) {
            if ($c->id == $carton->id) {
                $index = $key+1;
                break;
            }
        }
        $string = explode("-", $cartonNumber);
        $barcode = substr($string[1], 0, 6);

        $labelDetail = json_decode($carton->labelDetail);
        $items = [];
        $parts = '';
        $divisions = '';
        $priorities = '';
        $ubicationTicket = 620;
        if ($carton->audited_by > 0) {
            $query = DB::select("select SUBSTRING(pf.jdaName, 5) categoria, substring(pc.jdaName, 8) clasificacion, sum(cl.pieces_aud) piezas from carton_lines cl
                join `lines` l on l.id = cl.line_id
                join styles s on s.id = l.style_id
                join product_families pf on pf.id = s.family_id
                join product_classifications pc on pc.id = s.classification_id
                where cl.carton_id = ? group by s.family_id", [$carton->id]);
            if (count($query) > 0) {
                foreach ($query as $key => $q) {
                    $ubicationTicket += 50;
                    $items[] = '^FO50,'.$ubicationTicket.'^FD'. substr($q->categoria, 0, 18) .' ^FS
                            ^FO400,'.$ubicationTicket.'^FD'. substr($q->clasificacion, 0, 14) .' ^FS
                            ^FO680,'.$ubicationTicket.'^FD'. $q->piezas .'^FS';
                }
            }
        } else {
            foreach ($labelDetail->details as $item) {
                $ubicationTicket += 50;
                $items[] = '^FO50,'.$ubicationTicket.'^FD'. substr($item->category, 0, 18) .' ^FS
                        ^FO400,'.$ubicationTicket.'^FD'. substr($item->classification, 0, 14) .' ^FS
                        ^FO680,'.$ubicationTicket.'^FD'. $item->pieces .'^FS';
            }
        }
        

        if (count($items) >= 4) {
            for ($i = 0; $i <= 3; $i++) {
                $parts .= $items[$i];
            }
        } else {
            foreach ($items as $item) {
                $parts .= $item;
            }
        }

        $partFinal = [
            $parts
        ];
        foreach ($labelDetail->division as $key => $div) {
            $divisions .= $div.', ';
        }
        foreach ($labelDetail->priority as $key => $pri) {
            $priorities .= $pri.', ';
        }
        $divisions = rtrim($divisions, ", ");
        $priorities = rtrim($priorities, ", ");

        $zpl = '^XA
                ^FX Top section with barcode.
                ^LS
                ^BY4,2,200
                ^FO50,50^BC^FD'. $carton->barcode .'^FS

                ^FX Second section with business name.
                ^CF0,35
                ^FO50,310^FD'. $carton->businessName .'^FS

                ^FX Third section with summary.
                ^CFS,30
                ^FO50,380^FDOLA: ^FS
                ^CF0,50
                ^FO230,380^FD'. $carton->waveNumber .' ^FS
                ^CFS,30
                ^FO450,380^FDCB: '. $barcode .' ^FS
                ^FO50,430^FDCREADO: '. $carton->created_at->format('y/m/d H:i') .' ^FS
                ^FO450,430^FDCIERRE: '. $carton->created_at->format('y/m/d H:i') .' ^FS
                ^FO50,480^FDDIVISION: '. substr($divisions, 0, 25) .' ^FS
                ^FO50,530^FDPRIORIDAD: ^FS
                ^CF0,50
                ^FO230,530^FD'. substr($priorities, 0, 23) .' ^FS

                ^FX Fourth section with package details.
                ^CFA,20
                ^FO50,630^FDCATEGORIA ^FS
                ^FO400,630^FDCLASIFICACION ^FS
                ^FO680,630^FDPIEZAS^FS
                ^CFA,25

                '. $partFinal[0] .'

                ^CF0,70
                ^FO50,900^FDCAJA: '.$index.' ^FS

                ^FX Fourth section (the two boxes on the bottom).

                ^CF0,150
                ^FO30,1015^FD'. $carton->store .'^FS
                ^CF0,30
                ^FO60,1155^FD'. substr($carton->store_name, 0, 25) .'^FS
                ^CF0,150
                ^FO570,1015^FD'. $carton->route .'^FS
                ^CF0,30
                ^FO500,1155^FD'. substr($carton->route_name, 0, 15) .'^FS

                ^XZ';

        $response = (object) [
                'waveId' => $carton->wave_id ?? null,
                'zpl' => $zpl
            ];

        return $response;
    }
}
