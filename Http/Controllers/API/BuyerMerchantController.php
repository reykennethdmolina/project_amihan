<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BuyerMerchant;


class BuyerMerchantController extends Controller
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
    public function index()
    {
        return 'hoy';
        return BuyerMerchant::where('buyer_id', Auth::id())
                ->leftJoin('profiles', 'profiles.user_id', '=', 'buyer_merchants.partner_id')
                ->select('profiles.user_id', 'profiles.business_name')
                ->orderBy('profiles.business_name', 'ASC')
                ->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getClient()
    {
        return BuyerMerchant::where('partner_id', Auth::id())
                ->leftJoin('profiles', 'profiles.user_id', '=', 'buyer_merchants.buyer_id')
                ->select('profiles.user_id', 'profiles.business_name')
                ->orderBy('profiles.business_name', 'ASC')
                ->get();
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
