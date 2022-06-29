<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\PickingOrders;
use App\Line;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class PickingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        //
        if($request->paginated=='true')
            $result = PickingOrders::orderBy("id","ASC")->paginate($request->size);
        else
            $result = PickingOrders::all();

        return response($result,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $v = Validator::make($request->all(), [
            'wave_id'   => 'numeric:required',
            'sku'       => 'numeric:required',
            'pieces'    => 'numeric:required',
            'boxes'     => 'numeric',
            'user'      => 'numeric',
            'department_id' => 'numeric',
            'real_pieces'   => 'numeric',
            'real_boxes'    => 'numeric',
            'location'      => 'numeric',
            'status'        => 'string:required'
        ]);

        if ($v->fails()) {
            return ApiResponses::badRequest($v->errors());
        }

        $wave = PickingOrders::where([['wave_id','=',$request->wave_id],['sku','=',$request->sku]]);
        if(!empty($wave)){
            return ApiResponses::notFound('Ya existe una solicitud con la información solicitada');
        }

        $picking = PickingOrders::create([
            'wave_id'   => $request->wave_id,
            'sku'       => $request->sku,
            'pieces'    => $request->pieces,
            'boxes'     => $request->boxes,
            'user_id'   => Auth::id(),
            'department_id' => $request->department_id,
            'real_pieces'   => $request->real_pieces,
            'real_boxes'    => $request->real_boxes,
            'location'      => $request->location,
            'status'        => $request->status
        ]);

        return ApiResponses::okObject($picking->id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
        $result = PickingOrders::find($id);

        if(empty($result)){
            return ApiResponses::notFound('No se encontró la Orden de Picking');
        }

        return ApiResponses::okObject($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
        $picking = PickingOrders::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function getPickingByDept(Request $request,$id){
        $pickingDept = DB::table('lines')
            ->select(DB::raw("SUM(pieces) as Pieces,sku"))
            ->where([['wave_id','=',$request->wave_id],['department_id','=',$id]])
            ->groupBy('sku')
            ->orderBy(DB::raw("SUM(pieces)"),'desc')
            ->get();

        if(empty($pickingDept))
            return ApiResponses::notFound('No se encontró la información con el ID '.$id);

        return ApiResponses::okObject($pickingDept);
    }

    /**
     * @param $wave
     * @return Response
     */
    public function getPickingByWave($wave){
        $picking = DB::table('lines')
            ->select(DB::raw("SUM(pieces) as Pieces,sku"))
            ->where('wave_id','=',$wave)
            ->groupBy('sku')
            ->orderBy(DB::raw("SUM(pieces)"),'desc')
            ->get();
        if(empty($picking))
            return ApiResponses::notFound('No se encontró la información con el ID '.$wave);

        return ApiResponses::okObject($picking);
    }

    public function getFullView($id){
        $result = DB::table('picking_orders')
            ->select(["*",DB::raw('picking_orders.id as ID_KEY')])
            ->join("departments",'picking_orders.department_id',"=","departments.id")
            ->join("divisions",'departments.division_id',"=","divisions.id")
            ->get();

        return ApiResponses::okObject($result);;
    }

    public function forTestOnly(Request $request, $id){
        $randomStore = Line::inRandomOrder()->select('id')->limit(1000)->get()->toArray();
        $superArray = [];
        for ($i=0; $i < 50; $i++) {
            $superArray[] = [
                'store_id'    => array_rand($randomStore),
                'ranking'     => random_int(1, 100),
                'order_group_id' => random_int(1, 100),
                'slots'       => random_int(1, 10),
                'label_data'  => '',
                'merc_id'     => random_int(1, 1000)
            ];
        }

        return ApiResponses::okObject($superArray);
    }
}
