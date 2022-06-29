<?php

namespace App\Repositories;

use App\UserReport;

class UserReportsRepository
{
    protected $mUserReport;

    public function __construct()
    {
        $this->mUserReport = new UserReport();
    }

    /**
     * @param $oRequest
     * @return \Exception
     */
    public function getUsers($oRequest)
    {
        try {
            $sFiltro = $oRequest->input('search', false);
            $aUsers = $this->mUserReport
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('name', 'like', "%$sFiltro%")
                                ->orWhere('email', 'like', "$sFiltro");
                        }
                    }
                )
                ->orderBy($oRequest->input('name', 'id'), $oRequest->input('sort', 'asc'))
                ->paginate((int) $oRequest->input('per_page', 20));

            return $aUsers;
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * @return UserReport[]|\Exception|\Illuminate\Database\Eloquent\Collection
     */
    public function getUsersMails()
    {
        try {
            $users = $this->mUserReport->all();
            return $users;
        } catch (\Exception $e) {
            return $e;
        }
    }


    /**
     * @param $request
     * @return \Exception
     */
    public function createUser($request)
    {
        try {
            $validate = $request->validate([
                'email' => 'required|email',
                'name'  => 'required|max:80',
                'subscrited_to' => 'required'
            ]);
            if ($validate) {
                $user = $this->mUserReport->create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'subscrited_to' => json_encode($request->subscrited_to),
                    'active' => true,
                    'aws' => false,
                ]);
                // $exec = shell_exec('/usr/local/bin/aws ses verify-email-identity --email-address '.$request->email);
                return $user;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function desactivateSubscription($id)
    {
        $user = $this->mUserReport->find($id);
        $user->active = false;
        $user->update();
        return $user;
    }

    public function getSubscription($id)
    {
        $user = $this->mUserReport->find($id);
        return $user;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function updateSuscription($request)
    {
        $user = $this->mUserReport->find($request->id);
        $user->name = $request->name;
        $user->aws = $request->aws;
        $user->active = $request->active;
        $user->subscrited_to = json_encode($request->subscrited_to);
        $user->update();
        return $user;
    }
}
