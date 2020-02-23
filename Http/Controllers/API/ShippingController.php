<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\QRSService;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function shippingCost(Request $request)
    {
        $code = $request->get('code');
        $weight = $request->get('weight');
        $originCity = $request->get('originCity');
        $originProvince = $request->get('originProvince');
        $distCity = $request->get('distCity');
        $distProvince = $request->get('distProvince');

        $qrs = new QRSService();

        if ($code === 'QRSECO') {
            return $qrs->compute($code, $weight, $originCity, $originProvince, $distCity, $distProvince);
        } else if ($code === 'QRSEXP') {
            return $qrs->compute($code, $weight, $originCity, $originProvince, $distCity, $distProvince);
        }

    }

}
