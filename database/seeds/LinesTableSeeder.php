<?php

use Illuminate\Database\Seeder;

class LinesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $randomVariation = App\Variation::inRandomOrder()->limit(1000)->get();
        $randomOrder = App\Order::inRandomOrder()->select('id')->get()->toArray();
        foreach ($randomVariation as $k => $var) {
            $style = $var->style;
            factory(App\Line::class)->create([
            'variation_id' => $var->id,
            'order_id'     => array_rand($randomOrder),
            'department'   => $style->department,
            'provider'     => $style->provider,
            'barcode'      => $style->internal_reference,
            'style'        => $style->internal_reference,
            'sku'          => $var->sku,
            'description'  => $style->name,
            'size'         => $var->name,
            'style_id'   => $product->id,
            ]);
        }
    }
}
