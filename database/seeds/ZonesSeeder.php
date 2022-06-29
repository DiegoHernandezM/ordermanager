<?php

use Illuminate\Database\Seeder;

class ZonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Zone::truncate();
        App\Zone::create([
                'zone_type_id' => 1,
                'description' => '21-PISO',
                'code' => '21-PISO',
            ]);
        $b_one = '21-001-000';
        for ($i = 1; $i <= 30; $i++) {
            ++$b_one;
            App\Zone::create([
                'zone_type_id' => 5,
                'description' => $b_one.'-A',
                'code' => $b_one.'-A',
            ]);
        }
        $b_two = '21-002-000';
        for ($i = 1; $i <= 12; $i++) {
            ++$b_two;
            App\Zone::create([
                'zone_type_id' => 5,
                'description' => $b_two.'-A',
                'code' => $b_two.'-A',
            ]);
        }
        $b_three = '21-003-000';
        for ($i = 1; $i <= 12; $i++) {
            ++$b_three;
            App\Zone::create([
                'zone_type_id' => 5,
                'description' => $b_three.'-A',
                'code' => $b_three.'-A',
            ]);
        }
        $b_four = '21-004-000';
        for ($i = 1; $i <= 12; $i++) {
            ++$b_four;
            App\Zone::create([
                'zone_type_id' => 5,
                'description' => $b_four.'-A',
                'code' => $b_four.'-A',
            ]);
        }
    }
}
