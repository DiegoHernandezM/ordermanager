<?php

namespace App\Repositories;

use App\Department;
use App\Division;
use App\Team;
use App\Route;
use Illuminate\Http\Request;

class TeamRepository {

    protected $mTeam;
    protected $mDivision;
    protected $mDepartment;

    public function __construct()
    {
        $this->mTeam = new Team();
        $this->mDivision = new Division();
        $this->mDepartment = new Department();
    }

    /**
     * @return Team[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllTeamsDivision($id)
    {
        $division = $this->mDivision->where('id', $id)->first();
        $departments = $division->departments()->get();
        $teams = [];
        foreach ($departments as $department) {
            if ($this->mTeam->where('id_department', $department->id)->first()){
                $teams[] = $this->mTeam->where('id_department', $department->id)->first() ;
            }

        }
        return $teams;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function createTeam($oRequest)
    {
        $team = $this->mTeam->create([
            'name' => $oRequest->name,
            'description' => $oRequest->description ?? null,
            'id_administrator' => $oRequest->administrator,
            'id_department' => $oRequest->department
        ]);
        return $team;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findTeam($id)
    {
        $team = $this->mTeam->find($id);
        return $team;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed|null
     */
    public function updateTeam($id, $oRequest)
    {
        $team = $this->findTeam($id);
        if ($team != null) {
            $team->name = $oRequest->name;
            $team->description = $oRequest->description ?? null;
            $team->id_administrator = $oRequest->administrator;
            $team->id_department = $oRequest->department;
            $team->save();
            return $team;
        } else{
            return null;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteTeam($id)
    {
        $team = $this->mTeam->find($id);
        $team->delete();
        return true;
    }

    /**
     * @return Team[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllTeams()
    {
        $teams = $this->mTeam->all();
        return $teams;
    }

    /**
     * @return mixed
     */
    public function getDeptosFree()
    {
        $deptosInUse = $this->mTeam->all();
        $ids = [];
        foreach ($deptosInUse as $department) {
            $ids[] = [$department->id_department];
        }
        $deptos = $this->mDepartment->whereNotIn('id', $ids)
            ->get();
        return $deptos;
    }
}

