<?php

use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $randomStore = App\Store::inRandomOrder()->select('id')->get()->toArray();
        for ($i=0; $i < 50; $i++) {
            factory(App\Order::class)->create([
                'store_id'    => array_rand($randomStore),
                'ranking'     => random_int(1, 100),
                'order_group_id' => random_int(1, 100),
                'slots'       => random_int(1, 10),
                'label_data'  => '',
                'merc_id'     => random_int(1, 1000)
            ]);
        }
    }
}
