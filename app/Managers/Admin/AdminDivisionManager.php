<?php

namespace App\Managers\Admin;


use App\Division;
use App\Http\Requests\DivisionRequest;

class AdminDivisionManager
{
    protected $mDivision;

    public function __construct()
    {
        $this->mDivision = new Division();
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function createNewDivision($oRequest)
    {
        try {
            $divisions = json_decode($oRequest);
            foreach ($divisions as $division) {
                $find = $this->mDivision->find($division->id);
                if (!$find) {
                    $this->mDivision->create([
                        'id'  => $division->id,
                        'name'  => $division->name,
                        'code'  => $division->code
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
    public function updateDivision($oRequest)
    {
        try {
            $divisions = json_decode($oRequest);
            foreach ($divisions as $division) {
                $div = $this->mDivision->find($division->id);
                if ($div) {
                    $div->jda_id = $division->jdaId;
                    $div->jda_name = $division->jdaName;
                    $div->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
