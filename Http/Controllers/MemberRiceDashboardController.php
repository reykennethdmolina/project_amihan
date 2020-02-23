<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RiceProduction;
use App\Models\User;
use App\Services\PermissionService;

use Carbon\Carbon;
use DB;

class MemberRiceDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function memberCoop()
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }

        $this->authorize('isAdminCoopMember'); 

        $member = User::where('group_id', Auth::id())->pluck('id');

        $today = Carbon::today()->toFormattedDateString();

        $figures = RiceProduction::where('status', 'A')
            ->whereIn('owner_id', $member)
            ->select(DB::raw("SUM(hectarage) AS hectarage, SUM(actualproduction) AS actualproduction, SUM(estproduction) AS estproduction, status"))
            ->groupBy('status')
            ->first();


        $variety = RiceProduction::where('status', 'A')
            ->whereIn('owner_id', $member)
            ->select(DB::raw("rice_name, rice_variety_id, SUM(actualproduction) AS actualproduction, SUM(estproduction) AS estproduction, status"))
            ->groupBy('rice_name','rice_variety_id')
            ->get();

        $ricefarmers = RiceProduction::where('status', 'A')
            ->whereIn('owner_id', $member)
            ->with(['owner.profile'])
            ->orderBy('owner_id')
            ->get();

        $ricefarmers = $ricefarmers->groupBy('owner.profile.business_name');

        $ricefarmers->toArray();

        return view('admin.member-coop-rice-dashboard', compact(['today', 'figures', 'variety', 'ricefarmers']));

        

        // $tdate = Carbon::today();
        // $figures = CropProduction::where('status', 'A')
        //     ->whereIn('owner_id', $member)
        //     ->select(DB::raw("SUM(areawidth) as areawidth, SUM(arealength) as arealength, SUM(totalplant) as totalplant, SUM(actualproduction) as actualproduction, SUM(estproduction) as estproduction, SUM(totalallocation) as totalallocation, status, 'P' AS typeofproduction"))
        //     ->groupBy('status', 'typeofproduction')
        //     ->first();

        // $crops = CropProduction::where('status', 'A')
        //         ->whereIn('owner_id', $member)
        //         ->orderBy('slug')
        //         ->get();

        // $crops = $crops->groupBy('slug');

        // $crops->toArray();

        // $crop_productions = CropProduction::where('status', 'A')
        //     ->whereIn('owner_id', $member)
        //     ->select(DB::raw("slug, SUM(totalplant) as totalplant, SUM(estproduction) as estproduction, status, 'P' AS typeofproduction"))
        //     ->groupBy('slug', 'status', 'typeofproduction')
        //     ->get();

        // $members = CropProduction::where('status', 'A')
        //     ->whereIn('owner_id', $member)
        //     ->with('owner.profile')
        //     ->orderBy('owner_id', 'slug')
        //     ->get();

        // $members = $members->groupBy('owner_id');

        // $members->toArray();

        // $members_location = CropProduction::where('status', 'A')
        //     ->whereIn('owner_id', $member)
        //     ->select('owner_id', 'status', 'typeofproduction') 
        //     ->with('owner.profile')
        //     ->groupBy('owner_id', 'status', 'typeofproduction')
        //     ->get();

        //return view('admin.member-coop-crop-dashboard', compact(['today', 'figures', 'crops', 'crop_productions', 'members', 'members_location']));
        
    }
}
