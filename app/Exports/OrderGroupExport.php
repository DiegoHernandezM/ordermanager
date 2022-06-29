<?php

namespace App\Exports;

use App\OrderGroup;
use App\Repositories\LineRepository;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderGroupExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $lineRepository = new LineRepository();
        return $lines = $lineRepository->findByWaveRulesSummationBoxes(null, 1);
    }
}
