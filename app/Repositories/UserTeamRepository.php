<?php

namespace App\Repositories;


use App\Team;
use App\User;
use App\UserTeam;

class UserTeamRepository
{
    protected $mUserTeam;
    protected $mTeam;
    protected $mUser;

    public function __construct()
    {
        $this->mUserTeam = new UserTeam();
        $this->mTeam = new Team();
        $this->mUser = new User();
    }

    /**
     * @param $idTeam
     * @return mixed
     */
    public function getUsersByTeam($idTeam)
    {
        $usersTeam = $this->mUserTeam->where('id_team', $idTeam)->leftJoin('users','user_team.id_operator', '=', 'users.id')->get();
        return $usersTeam;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function saveUserTeam($oRequest)
    {
        $operator = $this->mUserTeam->where('id_operator', $oRequest->operator)->get();
        if ($operator === null) {
            return false;
        } else{
            $userTeam = $this->mUserTeam->create([
                'id_team' => $oRequest->team,
                'id_operator' => $oRequest->operator,
            ]);
            return $userTeam;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findUserTeam($id)
    {
        $user = $this->mUser->find($id);
        $userTeam = $this->mUserTeam->where('id_operator', $id)->rightJoin('users','user_team.id_operator', '=', 'users.id')
            ->select('users.id', 'users.name', 'user_team.id_team', 'user_team.id as id_user_team')->get();
        $operatorTeam = [];

        if (count($userTeam) > 0) {
            foreach ($userTeam as $operator) {
                foreach ($user->roles as $role) {
                    $operatorTeam[]  = [
                        'id' => $operator->id,
                        'idUserTeam' => $operator->id_user_team,
                        'idTeam' => $operator->id_team,
                        'nameUser' => $operator->name,
                        'nameRol' => $role->name
                    ];
                }
            }
        } else {
            foreach ($user->roles as $role) {
                $operatorTeam[]  = [
                    'id' => $user->id,
                    'idUserTeam' => null,
                    'idTeam' => null,
                    'nameUser' => $user->name,
                    'nameRol' => $role->name
                ];
            }
        }

        return $operatorTeam;
    }

    /**
     * @param $id
     * @param $oRequest
     * @return mixed|null
     */
    public function updateUserTeam($id, $oRequest)
    {
        $userTeam = $this->mUserTeam->where('id_operator', $id)->first();
        if ($userTeam != null) {
            $userTeam->id_team = $oRequest->team;
            $userTeam->id_operator = (int)$id;
            $userTeam->save();
            return $userTeam;
        } else{
            $user = $this->saveUserTeam($oRequest);
            return $user;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteUserTeam($id)
    {
        $team = $this->mUserTeam->where('id_operator',$id)->first();
        if ($team === null){
            return true;
        }
        $team->delete();
        return true;
    }



}