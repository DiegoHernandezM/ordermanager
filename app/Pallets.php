<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pallets extends Model
{
    //
    protected $table = 'pallets';

    protected $id;
    protected $wave_id;
    protected $fecha_mov;
    protected $lpn_transportador;
    protected $almacen_dest;
    protected $ubicacion_dest;
    protected $status;

    public $fillable = [
        'wave_id',
        'fecha_mov',
        'lpn_transportador',
        'almacen_dest',
        'ubicacion_dest',
        'status',
        'zone_id',
        'assignated_by',
        'inducted_by',
    ];

    const RECEIVED = 0;
    const STAGING = 1;
    const MOVING = 2;
    const BUFFER = 3;
    const INDUCTION = 4;

    const STAUS = [
        'Picking',
        'Staging',
        'Recibido',
        'Buffer',
        'Inducción',
    ];

    public function palletsSku()
    {
        return $this->hasMany('App\PalletContent', 'pallet_id')->orderBy('id');
    }

    public function ranking()
    {
        $select = [
            'pallet_contents.pallet_id',
            DB::raw('CAST(AVG(ranking) AS INTEGER) AS rank'),
            DB::raw('Count(*) AS rpt')
        ];
        return $this->hasMany('App\PalletContent', 'pallet_id')
            ->select($select)
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id')
            ->groupBy('pallet_contents.pallet_id')
            ->orderBy('rank');
    }

    public function wave()
    {
        return $this->belongsTo('App\Wave');
    }

    public function zone()
    {
        return $this->belongsTo('App\Zone');
    }
}
