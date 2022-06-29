<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\UserReportsRepository;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;

class UserReportsController extends Controller
{
    protected $cUserReportsRepo;

    public function __construct()
    {
        $this->cUserReportRepo = new UserReportsRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        try {
            $users = $this->cUserReportRepo->getUsers($request);
            return ApiResponses::okObject($users);
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
    public function store(Request $request)
    {
        try {
            $user = $this->cUserReportRepo->createUser($request);
            return ApiResponses::okObject($user);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function edit($id)
    {
        try {
            $user = $this->cUserReportRepo->getSubscription($id);
            return ApiResponses::okObject($user);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function desactivateUser($id)
    {
        try {
            $user = $this->cUserReportRepo->desactivateSubscription($id);
            return ApiResponses::okObject($user);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = $this->cUserReportRepo->updateSuscription($request);
            return ApiResponses::okObject($user);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
