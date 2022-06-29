<?php

use App\Pallets;
use Illuminate\Database\Seeder;

class PalletsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        App\Pallets::truncate();
        $letters= range('A', 'Z');
        $wave = App\Wave::with('linesSkuSeeder')->where('id', '23')->get()->toArray();
        $zones = App\Zone::where('zone_type_id', 5)->inRandomOrder()->get();
        $id = Pallets::create([
            'wave_id'           => $wave[0]['id'],
            'fecha_mov'         => now(),
            'lpn_transportador' => $letters[random_int(0, 24)].'01891781'.random_int(10, 99),
            'almacen_dest'      => '110',
            'ubicacion_dest'    => $zones[0]->code,
            'zone_id'           => $zones[0]->id,
            'status'            => Pallets::STAGING
        ])->id;
        foreach ($wave as $k => $var) {
            $folio = 1;
            $boxes = 0;
            $palletNo = 0;
            foreach ($var['lines_sku_seeder'] as $key => $line) {
                $qty = $line['pzas']-random_int(1, ceil($line['pzas']*(random_int(1, 99)/100)));
                $cajas = ceil($qty/40);
                $boxes += $cajas;
                if ($boxes>=24) {
                    $palletNo++;
                    $id = Pallets::create([
                        'wave_id'           => $var['id'],
                        'fecha_mov'         => now(),
                        'lpn_transportador' => $letters[random_int(0, 5)].'01891'.random_int(0, 99999),
                        'almacen_dest'      => '110',
                        'ubicacion_dest'    => $zones[$palletNo]->code,
                        'zone_id'           => $zones[$palletNo]->id,
                        'status'            => Pallets::RECEIVED
                    ])->id;
                    $boxes = 0;
                    $folio = 1;
                }

                factory(App\PalletContent::class)->create([
                    'wave_id'       => $var['id'],
                    'pallet_id'     => $id,
                    'folio_mov'     => $folio,
                    'sku'           => $line['sku'],
                    'cantidad'      => $qty,
                    'cajas'         => $cajas,
                    'variation_id'  => $line['variation_id'],
                    'style_id'      => $line['style_id'],
                    'department_id' => $line['department_id']
                ]);
                $folio++;
            }
        }
    }
}
