<?php

namespace App\Repositories;

use App\Route;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RouteRepository extends BaseRepository
{
    protected $model = 'App\Route';
    protected $mRoute;

    public function __construct()
    {
        $this->mRoute = new Route();
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function getAllPaginated($oRequest)
    {
        $sFiltro = $oRequest->input('search', false);
        $aRoutes = $this->mRoute
            ->where(
                function ($q) use ($sFiltro) {
                    if ($sFiltro !== false) {
                        return $q
                            ->orWhere('name', 'like', "%$sFiltro%")
                            ->orWhere('description', 'like', "$sFiltro");
                    }
                }
            )
            ->orderBy($oRequest->input('name', 'id'), $oRequest->input('sort', 'asc'))
            ->paginate((int) $oRequest->input('per_page', 20));

        return $aRoutes;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createRoute($oRequest)
    {
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

        $route = $this->mRoute->create([
           'name' => strtoupper($oRequest->name),
           'description' => strtoupper($oRequest->description),
           'color' => $color
        ]);

        return $route;
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function updateRoute($oRequest)
    {
        $route = $this->mRoute->find($oRequest->id);

        if (empty($route)) {
           return false;
        } else {
            $route->name = strtoupper($oRequest->name);
            $route->description = strtoupper($oRequest->description);
            $route->save();

            return true;
        }
    }
}
