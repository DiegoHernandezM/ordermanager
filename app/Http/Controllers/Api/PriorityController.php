<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Priority;
use Log;
use Validator;

class PriorityController extends Controller
{
    protected $mPriority;

    public function __construct(Priority $priority)
    {
        $this->mPriority = $priority;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'per_page' => 'numeric|between:5,100',
                'order' => 'max:30|in:id,name,created_at,updated_at',
                'search' => 'max:100',
                'name' => 'min:3',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $sFiltro = $oRequest->input('search', false);
            $aPriorities = $this->mPriority
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('label', 'like', "%$sFiltro%")
                                ->orWhere('order', '=', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'order'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 25));

            return response()->json(["data" => ["priorities" => $aPriorities ]],200);
        } catch (\Exception $e) {

            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }

    public function all()
    {
        try {
            $aPriorities = $this->mPriority->orderBy('order')->get();
            return ApiResponses::okObject($aPriorities);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
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
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function store(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'name' => 'required|min:3',
            ]);
            if ($oValidator->fails()) {
                return response()->json(['message' => json_encode($oValidator->errors())], 400);
            }

            $priorities = $this->mPriority->orderBy('order', 'asc')->get();
            $orders = [];
            foreach ($priorities as $priority) {
                $orders[] = $priority->order;
            }

            $lastOrder = end( $orders );

            $priority = $this->mPriority->create([
                'label' => strtoupper($oRequest->name),
                'jda_name' => strtoupper($oRequest->name),
                'jda_id' => $oRequest->jdaId,
                'order' => $lastOrder+1
            ]);

            return response()->json([
                'priority' => $priority
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al crear el recurso: '.$e->getMessage(),
            ]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $priority = $this->mPriority->find($id);

            return response()->json([
                'priority' => $priority ?? 'Recurso no encontrado'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
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
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'id' => 'required|numeric',
                'name' => 'required|min:3',
                'prioritiesId' => 'required',
            ]);
            if ($oValidator->fails()) {
                return response()->json(['message' => json_encode($oValidator->errors())], 400);
            }

            foreach ($oRequest->prioritiesId as $key => $item) {
                $priority = $this->mPriority->find($item);
                $priority->order = $key+1;
                $priority->update();
            }

            $p = $this->mPriority->find($oRequest->id);
            $p->label =  strtoupper($oRequest->name);
            $p->jda_id = $oRequest->jdaId;
            $p->update();

            return response()->json([
                'priority' => $priority
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al actualizar el recurso: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }
            $priority = $this->mPriority->find($id);
            if ($priority != null) {
                $priority->delete();
                $priorities = $this->mPriority->orderBy('order', 'asc')->get();
                $idPriority = [];
                foreach ($priorities as $item) {
                    $idPriority[] = $item->id;
                }
                foreach ($idPriority as $key => $item) {
                    $priority = $this->mPriority->find($item);
                    $priority->order = $key+1;
                    $priority->update();
                }
                return response()->json([
                    'message' => 'Prioridad eliminada'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Recurso no encontrado'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Priority',
                'message' => 'Error al eliminar el recurso: '.$e->getMessage(),
            ]);
        }
    }
}
