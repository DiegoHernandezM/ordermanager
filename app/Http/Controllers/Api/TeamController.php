<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Repositories\TeamRepository;
use Validator;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $rTeam;
    protected $cApiResponse;

    public function __construct()
    {
        $this->rTeam = new TeamRepository();
        $this->cApiResponse = new ApiResponses();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($division)
    {
        try {
            $team = $this->rTeam->getAllTeamsDivision($division);
            return $this->cApiResponse->okObject($team);
        }catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
    public function store(TeamRequest $oRequest)
    {
        try {
            $this->rTeam->createTeam($oRequest);
            return $this->cApiResponse->created();
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
            $team = $this->rTeam->findTeam($id);
            if($team == null) {
             return $this->cApiResponse->notFound();
            }
            return $this->cApiResponse->okObject($team);

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
    public function update(TeamRequest $oRequest, $id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $team = $this->rTeam->updateTeam($id ,$oRequest);

            return $this->cApiResponse->okObject($team);

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
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $team = $this->rTeam->deleteTeam($id);
            if ($team) {
                return $this->cApiResponse->ok();
            } else {
                return $this->cApiResponse->notFound();
            }
        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getAllTeams()
    {
        try {
            $teams = $this->rTeam->getAllTeams();
            return $this->cApiResponse->okObject($teams);
        }catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getDepartmentsFree()
    {
        try {
            $departments = $this->rTeam->getDeptosFree();
            return $this->cApiResponse->okObject($departments);
        }catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }
}
