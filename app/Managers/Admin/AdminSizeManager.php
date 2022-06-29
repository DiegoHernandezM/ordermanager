<?php

namespace App\Managers\Admin;

use App\Http\Requests\SizeRequest;
use App\Size;

class AdminSizeManager
{
    protected $mSize;
    protected $data;

    public function __construct()
    {
        $this->mSize = new Size();
    }

    /**
     * @param $data
     * @return bool
     */
    public function createNewSize($data)
    {
        try {
            $sizes = json_decode($data);
            foreach ($sizes as $size) {
                $find = $this->mSize->where('name', $size->name)->first();
                if (!$find) {
                    $this->mSize->create([
                        'id' => $size->id,
                        'name' => $size->name
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
     * @param $data
     * @return bool
     */
    public function updateSize($data)
    {
        try {
            $sizes = json_decode($data);
            foreach ($sizes as $size) {
                $find = $this->mSize->find($size->id);
                if ($find){
                    $find->name = $size->name;
                    $find->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }

    }

}