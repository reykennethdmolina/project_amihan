<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\PartnerCommunityProducts;
use App\Models\CommunityPartner;

use DB;

class PartnerCommunityProductController extends Controller
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
    public function getCommunityProductListing($id)
    {
        return PartnerCommunityProducts::where('partner_id', Auth::id())
                ->where('community_id', $id)
                ->where('partner_community_products.status', 'Y')
                ->leftJoin('products', 'products.id', '=', 'partner_community_products.product_id')
                //->select(DB::raw("partner_community_products.price AS dprice"))
                ->select(DB::raw("partner_community_products.price AS dprice"), 'partner_community_products.id', 'partner_community_products.old_price', 'partner_community_products.price',
                        'partner_community_products.price', 'products.sku', 'products.name', 'products.variety')
                ->orderBy('products.name', 'ASC')
                ->get();
    }

    public function getCommunityAvailProductListing($id)
    {
        $existing = PartnerCommunityProducts::where('partner_id', Auth::id())
                        ->where('community_id', $id)
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
            PartnerCommunityProducts::updateOrCreate(
                ['partner_id' => Auth::id(), 'community_id' => $comm, 'product_id' => $item->id],
                ['price' => $item->price, 'old_price' => $item->price, 'status' => 'Y']
            );
        }

        return ['message' => 'Success'];
    }

    public function removeToProductListing($id)
    {
        $data =  PartnerCommunityProducts::where('partner_id', Auth::id())
                    ->where('id', $id)
                    ->first();

        $data->update([
            'status' => 'N'
        ]);

        return ['message' => 'Success'];
    }

    public function updateCommunityDisallowDate(Request $request)
    {
        $comm = $request->input('comm');
        $dates = $request->input('dates');

        $communityPartner = CommunityPartner::where(['partner_id' => Auth::id(), 'community_id' => $comm])->first();

        if ($communityPartner) {
            $communityPartner->update([
                'disallowdates' => $dates,
            ]);
        }

    }

    public function updatePaymentOption(Request $request)
    {
        $comm = $request->input('comm');
        $payopt = $request->input('payopt');

        $communityPartner = CommunityPartner::where(['partner_id' => Auth::id(), 'community_id' => $comm])->first();

        if ($communityPartner) {
            $communityPartner->update([
                'paymentmode' => $payopt,
            ]);
        }

    }

    public function updatePickup(Request $request)
    {
        $comm = $request->input('comm');
        $pickuplist = $request->input('pickuplist');

        $communityPartner = CommunityPartner::where(['partner_id' => Auth::id(), 'community_id' => $comm])->first();

        if ($communityPartner) {
            $communityPartner->update([
                'pickuplocation' => $pickuplist,
            ]);
        }

    }
    
    public function updateProductListing(Request $request)
    {
        $prod = $request->input('prod');
        
        foreach ($prod as $item) {
            if ($item['dprice'] != $item['price']) {
                PartnerCommunityProducts::where('id', $item['id'])
                    ->update(['price' => $item['price'], 'old_price' => $item['dprice']]);     
            }
        }

        return ['message' => 'Success'];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
