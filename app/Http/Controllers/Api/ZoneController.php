<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\ZoneRepository;
use App\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Milon\Barcode\DNS1D;
use Validator;

class ZoneController extends Controller
{
    protected $zoneRepository;

    public function __construct(Request $request)
    {
        $this->zoneRepository = new ZoneRepository();
    }
    /**
     * Obtiene la lista de tipos de zona.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllZoneTypes()
    {
        return $this->zoneRepository->getAllZoneTypes();
    }

    /**
     * Crear una zona.
     *
     * @return \Illuminate\Http\Response
     */
    public function createZone(Request $request)
    {
        $v = Validator::make($request->all(), $this->zoneRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        return $this->zoneRepository->create($request->all());
    }

    /**
     * Obtener zonas.
     *
     * @return \Illuminate\Http\Response
     */
    public function getZones(Request $request)
    {
        try {
            return $this->zoneRepository->getZones($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtener zonas.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $zone)
    {
        try {
            return Zone::findOrFail($zone);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Actualiza una zona.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $zoneId)
    {
        $v = Validator::make($request->all(), $this->zoneRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $zone = Zone::find($zoneId);
            if (empty($zone)) {
                return ApiResponses::notFound('No se encontró la zona');
            }
            return $this->zoneRepository->update($zone, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Borra una zona.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete($zoneId)
    {
        try {
            $zone = Zone::findOrFail($zoneId);
            if (count($zone) === 0) {
                var_dump('dsadas');
                return ApiResponses::notFound('No se encontró la zona');
            }
            return $this->zoneRepository->delete($zoneId);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Imprime la etiqueta de la zona especificada
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printSticker(Request $request, $zoneId)
    {

        $zone = Zone::find($zoneId);
        if (!empty($zone)) {
            $pdf = App::make('dompdf.wrapper');
            $pdf->setPaper('c7', 'landscape');
            $format = '<body>';
            $barcode = '<div style="display:inline-block;text-align:center;font-size:35px;width:100%;margin-right:20px"><img src="data:image/png;base64,' . DNS1D::getBarcodePNG($zone->code, "C128", 2, 110, array(5,5,5)) . '" alt="barcode"   /><br/>'.$zone->code.'</div><br/><div style="display:inline-block;text-align:center;font-size:15px; width:100%">'.$zone->location.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$zone->description.'</div>';
            $format .= '<div style="font-family: sans-serif;margin-left: 15px">
              <br/>
              <div>
              '.$barcode.'
              </div>
          </div>';
            $format .= '</body>';
            $pdf->loadHTML($format);
            return $pdf->stream();
        } else {
            return ApiResponses::badRequest();
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response<<
     */
    public function getNameWaves(Request $request)
    {
        try {
            $namesWaves = $this->zoneRepository->getWaves($request);
            return ApiResponses::okObject($namesWaves);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getStylesBuffer(Request $request)
    {
        try {
            $sytles = $this->zoneRepository->getSylesPallets($request);
            return ApiResponses::okObject($sytles);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }
}
