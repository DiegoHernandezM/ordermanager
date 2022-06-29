<?php

namespace App\Managers\Admin;

use App\Http\Requests\ProductRequest;
use App\Style;
use Log;

class AdminProductManager
{
    protected $mProduct;
    public function __construct()
    {
        $this->mProduct = new Style();
    }

    /**
     * @param $data
     * @return bool
     */
    public function processNewProduct(ProductRequest $data)
    {
        try {
            $this->mProduct->create([
                'name'              => $data->name,
                'metadescription'   => $data->metadescription,
                'description'       => $data->description,
                'internal_reference'    => $data->internal_reference,
                'active'        => $data->active,
                'vat'           => $data->vat,
                'more_info'     => $data->more_info,
                'brand'         => $data->brand,
                'provider'      => $data->provider,
                'family'        => $data->family,
                'subcategory'   => $data->subcategory,
                'grill_id'      => $data->grill_id,
                'department'    => $data->department,
                'classification'    => $data->classification,
                'expiration_date'   => $data->expiration_date,
                'label_title'       => $data->label_title,
                'label_start_date'  => $data->label_start_date,
                'label_end_date'    => $data->label_end_date,
                'label_background_color'=> $data->label_background_color,
                'label_text_color'      => $data->label_text_color,
                'jda_sync_active'       => $data->jda_sync_active,
                'activation_disabled'   => $data->activation_disabled,
                'last_pieces'           => $data->last_pieces,
                'new_at'                => $data->new_at,
                'discount_percent'      => $data->discount_percent,
                'department_code'       => $data->department_code,
                'year_code'     => $data->year_code,
                'month_code'    => $data->month_code,
                'parent_name'   => $data->parent_name,
                'category_name' => $data->category_name,
                'user_price'    => $data->user_price,
                'user_discount' => $data->user_discount,
                'sat_key'       => $data->sat_key,
                'sat_unity'     => $data->sat_unity,
                'harmonized_tariff' => $data->harmonized_tariff,
                'parent_name_en'    => $data->parent_name_en,
                'category_name_en'  => $data->category_name_en,
                'name_en'   => $data->name_en,
                'colors_en' => $data->colors_en,
                'colors_es' => $data->colors_es,
                'adjusted_price'    => $data->adjusted_price,
                'historic'          => $data->historic,
                'user_price_usd'    => $data->user_price_usd,
                'discount_percent_usd'  => $data->discount_percent_usd,
                'user_discount_usd'     => $data->user_discount_usd,
                'conversion_type_usd'   => $data->conversion_type_usd,
                'price_status'  => $data->price_status,
                'promotion_id'  => $data->promotion_id,
                'created_at'    => $data->created_at,
                'updated_at'    => $data->updated_at,
                'division_id'   => $data->division_id
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    public function updateProduct(ProductRequest $data)
    {
        try {
            $product = $this->mProduct->find($data->id);
            $product->name  = $data->name;
            $product->metadescription   = $data->metadescription;
            $product->description       = $data->description;
            $product->internal_reference    = $data->internal_reference;
            $product->active        = $data->active;
            $product->vat           = $data->vat;
            $product->more_info     = $data->more_info;
            $product->brand         = $data->brand;
            $product->provider      = $data->provider;
            $product->family        = $data->family;
            $product->subcategory   = $data->subcategory;
            $product->grill_id      = $data->grill_id;
            $product->department    = $data->department;
            $product->classification    = $data->classification;
            $product->expiration_date   = $data->expiration_date;
            $product->label_title       = $data->label_title;
            $product->label_start_date  = $data->label_start_date;
            $product->label_end_date    = $data->label_end_date;
            $product->label_background_color    = $data->label_background_color;
            $product->label_text_color      = $data->label_text_color;
            $product->jda_sync_active       = $data->jda_sync_active;
            $product->activation_disabled   = $data->activation_disabled;
            $product->last_pieces       = $data->last_pieces;
            $product->new_at            = $data->new_at;
            $product->discount_percent  = $data->discount_percent;
            $product->department_code   = $data->department_code;
            $product->year_code         = $data->year_code;
            $product->month_code        = $data->month_code;
            $product->parent_name       = $data->parent_name;
            $product->category_name     = $data->category_name;
            $product->user_price        = $data->user_price;
            $product->user_discount     = $data->user_discount;
            $product->sat_key           = $data->sat_key;
            $product->sat_unity         = $data->sat_unity;
            $product->harmonized_tariff = $data->harmonized_tariff;
            $product->parent_name_en    = $data->parent_name_en;
            $product->category_name_en  = $data->category_name_en;
            $product->name_en           = $data->name_en;
            $product->colors_en         = $data->colors_en;
            $product->colors_es         = $data->colors_es;
            $product->adjusted_price    = $data->adjusted_price;
            $product->historic          = $data->historic;
            $product->user_price_usd    = $data->user_price_usd;
            $product->discount_percent_usd  = $data->discount_percent_usd;
            $product->user_discount_usd     = $data->user_discount_usd;
            $product->conversion_type_usd   = $data->conversion_type_usd;
            $product->price_status          = $data->price_status;
            $product->promotion_id          = $data->promotion_id;
            $product->created_at            = $data->created_at;
            $product->updated_at            = $data->updated_at;
            $product->division_id           = $data->division_id;
            $product->save();
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
