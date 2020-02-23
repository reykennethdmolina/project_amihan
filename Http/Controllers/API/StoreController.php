<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\PartnerCommunityProducts;
use App\Repositories\ProductRepository;

class StoreController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        return view();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function product($id)
    {
        return Product::where(['post_status' => 'Y', 'seller_id' => $id])
                ->with(['category'])
                ->orderBy('name', 'ASC')
                ->orderBy('variety', 'ASC')
                ->paginate(20);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function communityProduct($id)
    {
        return PartnerCommunityProducts::where(['status' => 'Y', 'community_id' => $id])
                ->with(['product.category'])
                ->paginate(20);
    }

    public function search($id, Request $request)
    {
        $category = $request->get('category');
        $q = $request->get('q');
        if ($category || $q) {
            return $this->productRepository->getProductSearch($id, $request)->paginate(20);
            #return $this->productRepository->getProductSearch($id, $request)->paginate(20);
        } else{
            return Product::where(['post_status' => 'Y' , 'seller_id' => $id])
                ->with(['category'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }
    }

    public function productCommunitySearch($id, Request $request)
    {
        $category = $request->get('category');
        $q = $request->get('q');
        if ($category || $q) {
            $find = $this->productRepository->getCommunityProductSearch($id,$request)->pluck('id');
            return PartnerCommunityProducts::where(['status' => 'Y', 'community_id' => $id])
                ->whereIn('product_id', $find)
                ->with(['product.category'])
                ->paginate(20);
        } else{
            return PartnerCommunityProducts::where(['status' => 'Y', 'community_id' => $id])
                ->with(['product.category'])
                ->paginate(20);
        }
    }
}
