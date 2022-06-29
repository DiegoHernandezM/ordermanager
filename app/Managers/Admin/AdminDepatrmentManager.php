<?php

namespace App\Managers\Admin;

use App\Department;
use Log;

class AdminDepatrmentManager
{
    protected $mDepartment;

    public function __construct()
    {
        $this->mDepartment = new Department();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewDepartment($oRequest)
    {
        try {
            $departments = json_decode($oRequest);
            foreach ($departments as $department) {
                $find = $this->mDepartment->where('id',$department->id)->first();
                if(!$find) {
                    $this->mDepartment->create([
                        'id' => $department->id,
                        'name' => $department->name,
                        'ranking' => ($department->ranking) ? $department->ranking : 99,
                        'jda_name' => $department->name,
                        'jda_id' => $department->id,
                        'division_id' => $department->division_id,
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
     * @param $oRequest
     * @return bool
     */
    public function updateDepartment($oRequest)
    {
        try {
            $departments = json_decode($oRequest);
            foreach ($departments as $department) {
                $depto = $this->mDepartment->find($department->id);
                if ($depto) {
                    $depto->name = $department->name;
                    $depto->jda_name = $department->name;
                    $depto->division_id = $department->division_id;
                    $depto->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
