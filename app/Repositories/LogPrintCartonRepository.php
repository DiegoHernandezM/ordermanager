<?php

namespace App\Repositories;


use App\Carton;
use App\LogPrintCarton;

class LogPrintCartonRepository
{
    protected $mLogCarton;
    protected $mCarton;

    public function __construct()
    {
        $this->mLogCarton = new  LogPrintCarton();
        $this->mCarton = new Carton();
    }

    /**
     * @param $barcode
     * @return mixed
     */
    public function createLog($barcode)
    {
        $carton = $this->mCarton->where('barcode', 'like', 'C-'.$barcode.'%')->orderByDesc('id')->first();
        $log = $this->mLogCarton->create([
            'barcode' => $carton->barcode
        ]);
        return $log;
    }

    /**
     * @param $barcode
     * @return mixed
     */
    public function getLog($barcode)
    {
        $logs = $this->mLogCarton->where('barcode', 'like', '%'.$barcode.'%')->get();
        return $logs;
    }
}
