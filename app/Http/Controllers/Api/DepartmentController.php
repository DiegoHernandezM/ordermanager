<?php

namespace App\Http\Controllers\Api;

use App\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $mDepartment;

    public function __construct()
    {
        $this->mDepartment = new Department();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $deparments = $this->mDepartment->all();
            return response()->json([
                'departments' => $deparments
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Departments',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }
}
