<?php

namespace App\Managers\Admin;


use App\Color;
use App\Http\Requests\ColorRequest;
use Log;

class AdminColorManager
{
    protected $mColor;

    public function __construct()
    {
        $this->mColor = new Color();
    }

    /**
     * @param $oRequets
     * @return bool
     */
    public function createNewColor($oRequets)
    {
        try {
            $colors = json_decode($oRequets);
            foreach ($colors as $color) {
                $find = $this->mColor->find($color->id);
                if (!$find) {
                    $this->mColor->create([
                        'name' => $color->name,
                        'hexadecimal_code' => $color->hexadecimal_code,
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
     * @param $oRequets
     * @return bool
     */
    public function updateColor($oRequets)
    {
        try {
            $colors = json_decode($oRequets);
            foreach ($colors as $color) {
                $clr = $this->mColor->find($color->id);
                if ($clr) {
                    $clr->name = $color->name;
                    $clr->hexadecimal_code = $color->hexadecimal_code;
                    $clr->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}