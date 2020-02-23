<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\RiceProduction;
use App\Models\RiceProductionHarvest;
use App\Models\RiceProductionActivity;
//use App\Models\CropProductionAllocation;
use Carbon\Carbon;

class RiceProductionController extends Controller
{
    const farm_worker = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        session_start();
        $this->farm_worker = @$_SESSION['farm_worker'];

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RiceProduction::where(['owner_id' => Auth::id()])
                ->with(['rice'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'season' => 'required|string|max:191',
            'rice_variety_id' => 'required|max:191',
            'program_name' => 'required|max:191',
            'highest_yield' => 'required|max:191',
            'lowest_yield' => 'required|max:191',
            'ave_yield' => 'required|max:191',
            'maturity' => 'required|max:191',
            'start_date' => 'required|max:191',
            'end_date' => 'required|max:191',
            'estproduction' => 'required|max:191',
            'hectarage' => 'required|max:191',
        ]);

        $production = RiceProduction::create([
            'owner_id' => Auth::id(),
            'season' => $request['season'],
            'rice_variety_id' => $request['rice_variety_id'],
            'rice_name' => $request['rice_name'],
            'program_name' => $request['program_name'],
            'highest_yield' => $request['highest_yield'],
            'lowest_yield' => $request['lowest_yield'],
            'ave_yield' => $request['ave_yield'],
            'maturity' => $request['maturity'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'estproduction' => $request['estproduction'],
            'hectarage' => $request['hectarage'],
            'status' => 'P',
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return $production;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $production = RiceProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->with(['rice'])
                    ->first();

        return $production;
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
        $this->authorize('isAdminCoopMember'); 

        $production = RiceProduction::findOrFail($id);

        $this->validate($request, [
            'season' => 'required|string|max:191',
            'rice_variety_id' => 'required|max:191',
            'program_name' => 'required|max:191',
            'highest_yield' => 'required|max:191',
            'lowest_yield' => 'required|max:191',
            'ave_yield' => 'required|max:191',
            'maturity' => 'required|max:191',
            'start_date' => 'required|max:191',
            'end_date' => 'required|max:191',
            'estproduction' => 'required|max:191',
            'hectarage' => 'required|max:191',
        ]);

        $production->update([
            'season' => $request['season'],
            'rice_variety_id' => $request['rice_variety_id'],
            'rice_name' => $request['rice_name'],
            'program_name' => $request['program_name'],
            'highest_yield' => $request['highest_yield'],
            'lowest_yield' => $request['lowest_yield'],
            'ave_yield' => $request['ave_yield'],
            'maturity' => $request['maturity'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'estproduction' => $request['estproduction'],
            'hectarage' => $request['hectarage'],
            'updated_by' => $this->farm_worker,
        ]);

        return $production;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $plot = RiceProduction::findOrFail($id);

        if ($plot->totalallocation == 0) {
            $plot->delete();
            return ['message' => 'Program deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }     
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $po = RiceProduction::where(function($query) use ($search){
                $query->where('program_name', 'LIKE', "%$search%")
                    ->orWhere('rice_name', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->with(['rice'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $po = RiceProduction::where(['owner_id' => Auth::id()])
                ->with(['rice'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $po;
    }

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = RiceProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->first();
        
        $production->update([
            'status' => 'A',
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Program ended'];
    }

    public function endSeason($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $plot = RiceProduction::findOrFail($id);

        $plot->update([
            'status' => 'R',
            'retiredate' => Carbon::now(),
            'updated_by' => $this->farm_worker,
        ]);
        
        return ['message' => 'Program ended'];
    }

    public function showActivity($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = RiceProductionActivity::where('rice_production_id', $id)
                    ->orderBy('activity_date', 'DESC')
                    ->get();
        
        return $activity;
    }

    public function storeActivity(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'activity_date' => 'required',
            'activity' => 'required',
            'amount' => 'required',
            'remarks' => 'required',
        ]);

        RiceProductionActivity::create([
            'owner_id' => Auth::id(),
            'rice_production_id' => $id,
            'activity_date' => $request['activity_date'],
            'activity' => $request['activity'],
            'brand' => $request['brand'],
            'qty' => $request['qty'],
            'amount' => $request['amount'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function updateActivity(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = RiceProductionActivity::findOrFail($id);

        $this->validate($request, [
            'activity_date' => 'required',
            'activity' => 'required',
            'amount' => 'required',
            'remarks' => 'required',
        ]);

        $activity->update([
            'activity_date' => $request['activity_date'],
            'activity' => $request['activity'],
            'brand' => $request['brand'],
            'qty' => $request['qty'],
            'amount' => $request['amount'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyActivity($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = RiceProductionActivity::findOrFail($id);

        if ($activity) {
            $activity->delete();

            return ['message' => 'Activity deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function showHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = RiceProductionHarvest::where('rice_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function storeHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'harvestdate' => 'required',
            'noofbags' => 'required',
            'volume' => 'required',
            'remarks' => 'required',
        ]);

        RiceProductionHarvest::create([
            'owner_id' => Auth::id(),
            'rice_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'noofbags' => $request['noofbags'],
            'volume' => $request['volume'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = RiceProduction::findOrFail($id);

        $harvested = RiceProductionHarvest::where('rice_production_id', $id)->sum('volume');
        $ton = 0;
        $ton = floatVal($harvested) / floatVal(1000);

        $production->update([
            'actualproduction' => $ton,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = RiceProductionHarvest::findOrFail($id);

        $this->validate($request, [
            'harvestdate' => 'required',
            'noofbags' => 'required',
            'volume' => 'required',
            'remarks' => 'required',
        ]);

        $harvest->update([
            'harvestdate' => $request['harvestdate'],
            'noofbags' => $request['noofbags'],
            'volume' => $request['volume'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        $production = RiceProduction::findOrFail($harvest->rice_production_id);

        $harvested = RiceProductionHarvest::where('rice_production_id', $harvest->rice_production_id)->sum('volume');
        $ton = 0;
        $ton = floatVal($harvested) / floatVal(1000);

        $production->update([
            'actualproduction' => $ton,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = RiceProductionHarvest::findOrFail($id);

        if ($harvest) {
            $rice_production_id = $harvest->rice_production_id;
            $harvest->delete();

            $production = RiceProduction::findOrFail($rice_production_id);

            $harvested = RiceProductionHarvest::where('rice_production_id', $rice_production_id)->sum('volume');

            $ton = 0;
            $ton = floatVal($harvested) / floatVal(1000);

            if ($harvested) {
                $production->update([
                    'actualproduction' => $ton,
                    'updated_by' => $this->farm_worker,
                ]);
            } else {
                $production->update([
                    'actualproduction' => 0,
                    'updated_by' => $this->farm_worker,
                ]);
            }

            return ['message' => 'Harvest deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

}
