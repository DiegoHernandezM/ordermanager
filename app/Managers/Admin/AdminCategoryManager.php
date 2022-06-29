<?php

namespace App\Managers\Admin;


use App\Category;
use App\Http\Requests\CategoryRequest;

class AdminCategoryManager
{

    protected $mCategory;

    public function __construct()
    {
        $this->mCategory = new Category();
    }

    public function createNewCategory(CategoryRequest $data)
    {
        try{
            $this->mCategory->create([
                'name'          => $data->name,
                'active'        => $data->active,
                'order'         => $data->order,
                'depends_id'    => $data->depends_id,
                'promotion_id'  => $data->promotion_id,
                'keywords'      => $data->keywords,
                'description'   => $data->description,
                'meta_product_description' => $data->meta_product_description
            ]);
            return true;
        } catch (\Exception $e)
        {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }

    public function updateCategory(CategoryRequest $data)
    {
        try{
            $category = $this->mCategory->find($data->id);
            if ($category) {
                $category->name         = $data->name;
                $category->active       = $data->active;
                $category->order        = $data->order;
                $category->depends_id   = $data->depends_id;
                $category->promotion_id = $data->promotion_id;
                $category->keywords     = $data->keywords;
                $category->description  = $data->description;
                $category->meta_product_description = $data->meta_product_description;
                $category->save();

                return true;
            } else {
                return false;
            }
        } catch (\Exception $e)
        {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}