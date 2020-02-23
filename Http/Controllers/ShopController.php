<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Crypt;
use App\Models\Product;

use Auth;

class ShopController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shop');
    }

    public function poformclientele()
    {
        if(Auth::check()) {
            return view('clientele.index');
        } else {
            return redirect('/login/clientele');    
        }        
    }

    public function consortium()
    {
        return view('clientele.consortium');
    }

    public function checkout()
    {
        return view('checkout');
        // $categories = Category::where('is_active', 0)->orderBy('name')->get();
        // return view('welcome', compact('categories'));
    }

    public function precheckout()
    {
        return view('precheckout');
    }

    public function product($slug) 
    {
        $data = explode("&=", $slug);
        $product = Product::where(['post_status' => 'Y'])
            ->where('slug', @$data[0])
            ->where('id', @$data[1])
            ->where('sku', @$data[2])
            ->first();
        if ($product) {
            return view('singleproduct');
        } else {
            return redirect()->route('product.notfound', array('slug' => $slug));
        }
    }
    
    public function productNotFound($slug)
    {
        return view('errors.product-not-found');   
    }
}
