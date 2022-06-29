<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\RouteRepository;
use App\Route;
use Illuminate\Http\Request;
use Validator;

class RouteController extends Controller
{
    protected $routeRepository;

    public function __construct(Request $request)
    {
        $this->routeRepository = new RouteRepository();
    }

    /**
     * Obtiene todas las rutas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        try {
            return Route::withCount('stores')->get();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Guarda una ruta nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $route =  $this->routeRepository->createRoute($request);
            return ApiResponses::okObject($route);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Edita una ruta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        try {
            $route = $this->routeRepository->updateRoute($request);
            if (!$route) {
                return ApiResponses::notFound('No se encontró la ruta');
            }
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete($id)
    {
        try {
            $route = Route::find($id);
            if (empty($route)) {
                return ApiResponses::notFound('No se encontró la ruta');
            }
            $route->delete();

            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAllRoutes(Request $request)
    {
        try{
            $routes = $this->routeRepository->getAllPaginated($request);
            return ApiResponses::okObject($routes);
        }   catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function getRoute($id)
    {
        try {
            $route = Route::find($id);
            return ApiResponses::okObject($route);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
