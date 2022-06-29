<?php

use Illuminate\Database\Seeder;

class SetClassificationToFamilies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $families = \App\ProductFamily::all();
        foreach ($families as $family) {
            if ($this->getClassificationFamily($family->id) != 0) {
                $family->classification_id = $this->getClassificationFamily($family->id);
                $family->save();
            }
        }
    }

    public function getClassificationFamily($id) {
        $classifications = [
            1 => 1,
            2 => 17,
            3 => 12,
            4 => 16,
            5 => 16,
            6 => 12,
            7 => 16,
            8 => 16,
            9 => 16,
            10 => 12,
            11 => 1,
            12 => 1,
            13 => 0,
            14 => 0,
            15 => 12,
            16 => 18,
            17 => 20,
            18 => 21,
            19 => 22,
            20 => 1,
            21 => 1,
            22 => 1,
            23 => 1,
            24 => 1,
            25 => 1,
            26 => 1,
            27 => 2,
            28 => 0,
            29 => 2,
            30 => 2,
            31 => 2,
            32 => 2,
            33 => 17,
            34 => 2,
            35 => 2,
            36 => 2,
            37 => 3,
            38 => 0,
            39 => 0,
            40 => 0,
            41 => 0,
            42 => 1,
            43 => 0,
            44 => 0,
            45 => 9,
            46 => 0,
            47 => 4,
            48 => 11,
            49 => 7,
            50 => 7,
            51 => 7,
            52 => 12,
            53 => 7,
            54 => 7,
            55 => 7,
            56 => 7,
            57 => 7,
            58 => 0,
            59 => 0,
            60 => 0,
            61 => 14,
            62 => 12,
            63 => 15,
            64 => 13,
            65 => 12,
            66 => 12,
            67 => 2,
            68 => 19,
            69 => 2,
            70 => 7,
            71 => 7,
            72 => 12,
        ];

        $value = $classifications[$id] ?? 23;
        return $value;
    }
}
