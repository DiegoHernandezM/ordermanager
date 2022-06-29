<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $access = DB::table('access_types')->get()->toArray();
        $application = DB::table('application_types')->get()->toArray();
        //
        /*$access =  DB::table('access_types')->join('application_types','access_types.id','=','application_types.access_type')
            ->select('access_types.id','access_types.application_name','application_types.action_name')->get()->toArray();
        */
        $newAccess = [];
        foreach ($access as $values){
            $newValue = [];
            foreach ($application as $k => $appValues){
                if($values->id==$appValues->access_type)
                    $newValue[]=$appValues;

            }
            $newArray = $values;
            $newArray->access_value = $newValue;
            $newAccess[]=$newArray;
        }

        return response($access, 200);
    }

    public function getAccessTypes()
    {
        $application = DB::table('access_types')->get();

        return response($application, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
        /*$access =  DB::table('access_types')->join('application_types','access_types.id','=','application_types.access_type')
            ->where('access_types.id','=',$id)->get();
        */
        $newParameters = [];
        if($request->paginated) {
            $access = DB::table('access_types')->join('application_types', 'access_types.id', '=', 'application_types.access_type')
                ->where('access_types.id', '=', $id)->get()->toArray();
            $newParameters["data"]=$access;
        }else {
            $access = DB::table('access_types')->join('application_types', 'access_types.id', '=', 'application_types.access_type')
                ->where('access_types.id', '=', $id)->paginate($request->size);
            $newParameters = $access;
        }

        if (empty($newParameters)) {
            return ApiResponses::notFound("La acción no existe.");
        }

        return response($newParameters, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
