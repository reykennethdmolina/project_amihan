<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\MerchantBuyerProducts;
use App\Models\BuyerMerchant;

use DB;

class MerchantBuyerProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getClientProductListing($id)
    {
        return MerchantBuyerProducts::where('partner_id', Auth::id())
                ->where('buyer_id', $id)
                ->where('merchant_buyer_products.status', 'Y')
                ->leftJoin('products', 'products.id', '=', 'merchant_buyer_products.product_id')
                ->select(DB::raw("merchant_buyer_products.price AS dprice"), 'merchant_buyer_products.id', 'merchant_buyer_products.old_price', 'merchant_buyer_products.price',
                        'merchant_buyer_products.price', 'products.sku', 'products.name', 'products.variety')
                ->orderBy('products.name', 'ASC')
                ->get();
    }

    public function getClientAvailProductListing($id)
    {
        $existing = MerchantBuyerProducts::where('partner_id', Auth::id())
                        ->where('buyer_id', $id)
                        ->where('status', 'Y')
                        ->pluck('product_id');

        return Product::where(['seller_id' => Auth::id()])
            ->whereNotIn('id', $existing)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function addToProductListing(Request $request)
    {
        $comm = $request->input('comm');
        $prod = $request->input('prod');

        $list = Product::whereIn('id', $prod)
            ->get();

        foreach ($list as $item) {
            MerchantBuyerProducts::updateOrCreate(
                ['partner_id' => Auth::id(), 'buyer_id' => $comm, 'product_id' => $item->id],
                ['price' => $item->price, 'old_price' => $item->price, 'status' => 'Y']
            );
        }

        return ['message' => 'Success'];
    }

    public function removeToProductListing($id)
    {
        $data =  MerchantBuyerProducts::where('partner_id', Auth::id())
                    ->where('id', $id)
                    ->first();

        $data->update([
            'status' => 'N'
        ]);

        return ['message' => 'Success'];
    }

    public function updateProductListing(Request $request)
    {
        $prod = $request->input('prod');
        
        foreach ($prod as $item) {
            if ($item['dprice'] != $item['price']) {
                MerchantBuyerProducts::where('id', $item['id'])
                    ->update(['price' => $item['price'], 'old_price' => $item['dprice']]);     
            }
        }

        return ['message' => 'Success'];
    }

    public function getMerchantBuyerProduct($id)
    {
        return MerchantBuyerProducts::where(['status' => 'Y', 'partner_id' => $id, 'buyer_id' => Auth::id()])
                ->with(['product.category'])
                ->get();
    }

}
