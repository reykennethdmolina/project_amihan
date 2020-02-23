<?php

namespace App\Repositories;

use DB;
use App\Models\Product;
use App\Models\PartnerCommunityProducts;

class ProductRepository extends Repository
{
    public function getCommunityProductSearch($id, $request) 
    {
        $products = PartnerCommunityProducts::where(['status' => 'Y', 'community_id' => $id])->pluck('product_id');

        $builder = Product::whereIn('id', $products)
            ->with(['category'])
            ->orderBy('id', 'DESC')
            ->orderBy('name', 'ASC');  

        return $this->filter($builder, $request);

    }

    public function getProductSearch($id, $request) 
    {
        if ($id) {
            $builder = Product::where(['post_status' => 'Y', 'seller_id' => $id])
                ->with(['category'])
                ->orderBy('id', 'DESC')
                //->orderBy(DB::raw('RAND()'))
                ->orderBy('name', 'ASC'); 
        } else {
            $builder = Product::where(['post_status' => 'Y'])
                ->with(['category'])
                //->orderBy(DB::raw('RAND()'))
                ->orderBy('id', 'DESC')
                ->orderBy('name', 'ASC');    
        }
        
        return $this->filter($builder, $request);
    }

    public function filter($query, $request)
    {
        if ($request->get('category')) {
            $query->where(function ($q) use ($request) {
                $ids = explode(',', $request->get('category'));
                return $q->whereIn('category_id', $ids);
            });
        }

        if ($request->get('q')) {
            $query->where(function ($q) use ($request) {
                return $q->where('name', 'LIKE', '%'.$request->get('q').'%')
                    ->orWhere('description', 'LIKE', '%'.$request->get('q').'%')
                    ->orWhere('variety', 'LIKE', '%'.$request->get('q').'%')
                    ->orWhere('search_key', 'LIKE', '%'.$request->get('q').'%');
            });
        }

        return $query;
    }  
}
