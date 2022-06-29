<?php

namespace App\Managers\Admin;


use App\Http\Requests\RouteRequest;
use App\Route;
use App\Log;

class AdminRouteManager
{
    protected $mRoute;

    public function __construct()
    {
        $this->mRoute = new Route();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewRoute($oRequest)
    {
        try {
            $routes = json_decode($oRequest);
            foreach ($routes as $route) {
                $find = $this->mRoute->find($route->id);
                if (!$find) {
                    $this->mRoute->create([
                        'id' => $route->id,
                        'name' => $route->name,
                        'description' => $route->description,
                        'color' => $route->color
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    /**
     * @param $oRequets
     * @return bool
     */
    public function updateRoute($oRequets)
    {
        try {
            $routes = json_decode($oRequets);
            foreach ($routes as $route) {
               $routeFind = $this->mRoute->find($route->id);
               if ($routeFind) {
                   $routeFind->name = $route->name;
                   $routeFind->description = $route->description;
                   $routeFind->color = $route->color->hexadecimalColor;
                   $routeFind->save();
               }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }

    }
}
