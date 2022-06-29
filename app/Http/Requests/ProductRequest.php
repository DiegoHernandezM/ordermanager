<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => '',
            'metadescription' => '',
            'description' => '',
            'internal_reference' => '',
            'active' => '',
            'vat' => '',
            'more_info' => '',
            'brand' => '',
            'provider' => '',
            'family' => '',
            'subcategory' => '',
            'grill_id' => '',
            'department' => '',
            'classification' => '',
            'expiration_date' => '',
            'label_title' => '',
            'label_start_date' => '',
            'label_end_date' => '',
            'label_background_color' => '',
            'label_text_color' => '',
            'jda_sync_active' => '',
            'activation_disabled' => '',
            'last_pieces' => '',
            'new_at' => '',
            'discount_percent' => '',
            'department_code' => '',
            'year_code' => '',
            'month_code' => '',
            'parent_name' => '',
            'category_name' => '',
            'user_price' => '',
            'user_discount' => '',
            'sat_key' => '',
            'sat_unity' => '',
            'harmonized_tariff' => '',
            'parent_name_en' => '',
            'category_name_en' => '',
            'name_en' => '',
            'colors_en' => '',
            'colors_es' => '',
            'adjusted_price' => '',
            'historic' => '',
            'user_price_usd' => '',
            'discount_percent_usd' => '',
            'user_discount_usd' => '',
            'conversion_type_usd' => '',
            'price_status' => '',
            'promotion_id' => '',
            'created_at' => '',
            'updated_at' => '',
            'division_id' => '',
        ];
    }
}
