<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserTeamRequest;
use App\Repositories\UserTeamRepository;
use Validator;

class UserTeamController extends Controller
{

    protected $rUserTeam;
    protected $cApiResponse;

    public function __construct()
    {
        $this->rUserTeam = new UserTeamRepository();
        $this->cApiResponse = new ApiResponses();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($idTeam)
    {
        try {
            $oValidator = Validator::make(['id' => $idTeam], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $usersTeam = $this->rUserTeam->getUsersByTeam($idTeam);
            return $this->cApiResponse->okObject($usersTeam);
        } catch (\Exception $e) {

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserTeamRequest $oRequest)
    {
        try {
            $userTeam = $this->rUserTeam->saveUserTeam($oRequest);
            if ($userTeam) {
                return $this->cApiResponse->created();
            } else {
                return $this->cApiResponse->found('El recurso ya esta asignado en otro equipo');
            }
        } catch(\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
                return $this->cApiResponse->badRequest();
            }
            $userteam = $this->rUserTeam->findUserTeam($id);
            if($userteam == null) {
                return $this->cApiResponse->notFound();
            }
            return $this->cApiResponse->okObject($userteam);
        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserTeamRequest $oRequest, $id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $userTeam = $this->rUserTeam->updateUserTeam($id ,$oRequest);

            return $this->cApiResponse->okObject($userTeam);

        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
            $oValidator = Validator::make(['id' => (int)$id], [
                'id' => 'required|numeric'
            ]);

            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $userTeam = $this->rUserTeam->deleteUserTeam((int)$id);
            return $this->cApiResponse->ok();
        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }
}
