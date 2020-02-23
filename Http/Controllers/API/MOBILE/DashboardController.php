<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CropProduction;
use App\Models\RiceProduction;

use Auth;
use DB;

class DashboardController extends Controller
{
    public function figures()
    {
        $riceareasize = RiceProduction::where(['owner_id' => Auth::id(), 'status' => 'A'])->sum('hectarage');

        $crop = CropProduction::where('status', 'A')
            ->where('owner_id', Auth::id())
            ->select(DB::raw("SUM(areawidth) as areawidth, SUM(arealength) as arealength, status, 'P' AS typeofproduction"))
            ->groupBy('status', 'typeofproduction')
            ->first();
       
        return ['riceareasize' => $riceareasize, 'crop' => $crop];
    }

    public function activeRiceProduction()
    {
        return RiceProduction::where(['owner_id' => Auth::id(), 'status' => 'A'])
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->get();    
    }

    public function activeCropProduction()
    {
        return CropProduction::where(['owner_id' => Auth::id(), 'status' => 'A'])
        ->orderBy('created_at', 'DESC')
        ->limit(10)
        ->get();    
    }
}
