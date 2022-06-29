<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\BrandRepository;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->brandRepository = new brandRepository();
    }

    /**
     * Obtiene brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        try {
            return $this->brandRepository->getAllBrands();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

}
