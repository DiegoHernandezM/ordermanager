<?php

use Illuminate\Database\Seeder;

class ZonesTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\ZoneType::truncate();

        $zonesTypes = [
            ['id' => 1, 'name' => 'Buffer S1'],
            ['id' => 2, 'name' => 'Buffer S3'],
            ['id' => 3, 'name' => 'Buffer PBL'],
            ['id' => 4, 'name' => 'Inducción'],
            ['id' => 5, 'name' => 'Staging'],
        ];

        App\ZoneType::insert($zonesTypes);
    }
}
