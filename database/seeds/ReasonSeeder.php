<?php

use Illuminate\Database\Seeder;

class ReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\ReasonCancel::truncate();
        App\ReasonCancel::create([
            'id' => 1,
            'reason' => 'Equivocación',
        ]);
        App\ReasonCancel::create([
            'id' => 2,
            'reason' => 'Insuficiente personal',
        ]);
        App\ReasonCancel::create([
            'id' => 3,
            'reason' => 'Operativamente inviable',
        ]);
        App\ReasonCancel::create([
            'id' => 4,
            'reason' => 'Otro',
        ]);
    }
}
