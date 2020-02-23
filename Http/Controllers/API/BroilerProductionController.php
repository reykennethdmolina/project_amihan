<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Strain;
use App\Models\BroilerProduction;
use App\Models\BroilerProductionHarvest;
use App\Models\BroilerProductionMonitoring;
use App\Models\BroilerProductionFeeding;
use App\Models\BroilerProductionMortality;
use App\Models\BroilerProductionVaccine;

use Carbon\Carbon;
use DB;

class BroilerProductionController extends Controller
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
        return BroilerProduction::where(['owner_id' => Auth::id()])
                ->with(['strain'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
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
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'program' => 'required|string|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            'totalbird' => 'required|max:191',
        ]);

        #return Carbon::now()->format('mdYhmi');

        $production = BroilerProduction::create([
            'owner_id' => Auth::id(),
            'program' => $request['program'],
            'housename' => $request['housename'],
            'nurseryname' => $request['nurseryname'],
            'batch' => $request['batch'],
            'poultry' => $request['poultry'],
            'strain_id' => $request['strain_id'],
            'startdate' => $request['startdate'],
            'houselen' => $request['houselen'],
            'housewid' => $request['housewid'],
            'nurserylen' => $request['nurserylen'],
            'nurserywid' => $request['nurserywid'],
            'totalbird' => $request['totalbird'],
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

        $production = BroilerProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->with(['strain'])
                    ->first();

        return $production;
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
        $this->authorize('isAdminCoopMember'); 

        $production = BroilerProduction::findOrFail($id);

        $this->validate($request, [
            'program' => 'required|string|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            'totalbird' => 'required|max:191',
        ]);

        $production->update([
            'owner_id' => Auth::id(),
            'program' => $request['program'],
            'housename' => $request['housename'],
            'nurseryname' => $request['nurseryname'],
            'batch' => $request['batch'],
            'poultry' => $request['poultry'],
            'strain_id' => $request['strain_id'],
            'startdate' => $request['startdate'],
            'houselen' => $request['houselen'],
            'housewid' => $request['housewid'],
            'nurserylen' => $request['nurserylen'],
            'nurserywid' => $request['nurserywid'],
            'totalbird' => $request['totalbird'],    
            'updated_by' => $this->farm_worker,
        ]);

        return $production;
    }

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = BroilerProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->first();
        
        $production->update([
            'status' => 'A',
            'originaltotalbird' => $production->totalbird,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Program ended'];
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

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            return BroilerProduction::where(function($query) use ($search){
                $query->where('housename', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%")
                    ->orWhere('batch', 'LIKE', "%$search%")
                    ->orWhere('poultry', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->with(['strain'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            return BroilerProduction::where(['owner_id' => Auth::id()])
                ->with(['strain'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }
    }

    public function move(Request $request, $id) {
        $this->authorize('isAdminCoopMember'); 

        $production = BroilerProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->first();
        
        $production->update([
            'housename' => $request['housename'],
            'batch' => $request['batch'],
            'houselen' => $request['houselen'],
            'housewid' => $request['housewid'],
            'movement_status' => 'R',
            'movementdate' => Carbon::now(),
            'updated_by' => $this->farm_worker,
        ]);

        return $production;
    }

    public function retireProgram($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $program = BroilerProduction::findOrFail($id);

        $program->update([
            'status' => 'R',
            'retiredate' => Carbon::now(),
            'updated_by' => $this->farm_worker,
        ]);
        
        return ['message' => 'Program ended'];
    }

    public function lastFeedingRecord($id) {
        $this->authorize('isAdminCoopMember'); 

        $feeding = BroilerProductionFeeding::where('broiler_production_id',$id)
                                        ->where(['owner_id' => Auth::id()])
                                        ->orderBy('feeddate', 'DESC')
                                        ->first();

        return $feeding;
    }

    public function storeFeeding(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'feeddate' => 'required',
            'type' => 'required',
            'feedbrand' => 'required',
            'fpbirds' => 'required',
            'population' => 'required',
            'totalfeeds' => 'required',
        ]);

        BroilerProductionFeeding::create([
            'owner_id' => Auth::id(),
            'broiler_production_id' => $id,
            'feeddate' => $request['feeddate'],
            'feedbrand' => $request['feedbrand'],
            'type' => $request['type'],
            'fpbirds' => $request['fpbirds'],
            'population' => $request['population'],
            'totalfeeds' => $request['totalfeeds'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function showFeeding($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = BroilerProductionFeeding::where('broiler_production_id', $id)
                    ->orderBy('feeddate', 'DESC')
                    ->get();
        
        return $feeding;
    }

    public function updateFeeding(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = BroilerProductionFeeding::findOrFail($id);

        $feeding->update([
            'feeddate' => $request['feeddate'],
            'feedbrand' => $request['feedbrand'],
            'type' => $request['type'],
            'fpbirds' => $request['fpbirds'],
            'population' => $request['population'],
            'totalfeeds' => $request['totalfeeds'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyFeeding($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = BroilerProductionFeeding::findOrFail($id);

        if ($feeding) {

            $feeding->delete();
            return ['message' => 'Feeding deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function storeMortality(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'mortalitydate' => 'required',
            'noofbirds' => 'required',
            'status' => 'required',
            'remarks' => 'required',
        ]);

        BroilerProductionMortality::create([
            'owner_id' => Auth::id(),
            'broiler_production_id' => $id,
            'mortalitydate' => $request['mortalitydate'],
            'noofbirds' => $request['noofbirds'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = BroilerProduction::findOrFail($id);

        if ($request['status'] == 'loss') {
            $totalbird = $production->totalbird - $request['noofbirds'];
        } else {
            $totalbird = $production->totalbird + $request['noofbirds'];
        }

        $production->update([
            'totalbird' => $totalbird,
            'updated_by' => $this->farm_worker,
        ]);

        return $totalbird;
    }

    public function showMortality($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = BroilerProductionMortality::where('broiler_production_id', $id)
                    ->orderBy('mortalitydate', 'DESC')
                    ->get();
        
        return $mortality;
    }

    public function updateMortality(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = BroilerProductionMortality::findOrFail($id);

        $mortality->update([
            'mortalitydate' => $request['mortalitydate'],
            'noofbirds' => $request['noofbirds'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        $totalbird = 0;

        $production = BroilerProduction::findOrFail($mortality->broiler_production_id);

        $mortalityLoss = BroilerProductionMortality::where('broiler_production_id', $mortality->broiler_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofbirds');

        $mortalityAdd = BroilerProductionMortality::where('broiler_production_id', $mortality->broiler_production_id)
                                            ->where('status', 'add')
                                            ->sum('noofbirds');
        
        $totalbird = $production->originaltotalbird + ($mortalityAdd - $mortalityLoss);

        $production->update([
            'totalbird' => $totalbird,
            'updated_by' => $this->farm_worker,
        ]);
        
        return $totalbird;
    }

    public function destroyMortality($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = BroilerProductionMortality::findOrFail($id);

        if ($mortality) {

            $production = BroilerProduction::findOrFail($mortality->broiler_production_id);

            $mortality->delete();

            $mortalityLoss = BroilerProductionMortality::where('broiler_production_id', $mortality->broiler_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofbirds');

            $mortalityAdd = BroilerProductionMortality::where('broiler_production_id', $mortality->broiler_production_id)
                                                ->where('status', 'add')
                                                ->sum('noofbirds');
            
            $totalbird = $production->originaltotalbird + ($mortalityAdd - $mortalityLoss);

            $production->update([
                'totalbird' => $totalbird,
                'updated_by' => $this->farm_worker,
            ]);
            
            return $totalbird;
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function storeVaccine(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'administereddate' => 'required',
            'vaccine' => 'required',
            'brand' => 'required',
            'remarks' => 'required',
        ]);

        BroilerProductionVaccine::create([
            'owner_id' => Auth::id(),
            'broiler_production_id' => $id,
            'administereddate' => $request['administereddate'],
            'vaccine' => $request['vaccine'],
            'brand' => $request['brand'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Success'];
    }

    public function showVaccine($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $vaccine = BroilerProductionVaccine::where('broiler_production_id', $id)
                    ->orderBy('administereddate', 'DESC')
                    ->get();
        
        return $vaccine;
    }

    public function updateVaccine(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $vaccine = BroilerProductionVaccine::findOrFail($id);

        $vaccine->update([
            'administereddate' => $request['administereddate'],
            'vaccine' => $request['vaccine'],
            'brand' => $request['brand'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Success'];
    }

    public function destroyVaccine($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $vaccine = BroilerProductionVaccine::findOrFail($id);

        if ($vaccine) {

            $vaccine->delete();
            return ['message' => 'Vaccine deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function storeHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'harvestdate' => 'required',
            'type' => 'required',
            'noofbirds' => 'required',
            'aveweight' => 'required',
            'totalproduction' => 'required',
        ]);

        BroilerProductionHarvest::create([
            'owner_id' => Auth::id(),
            'broiler_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'type' => $request['type'],
            'noofbirds' => $request['noofbirds'],
            'aveweight' => $request['aveweight'],
            'totalproduction' => $request['totalproduction'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function showHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = BroilerProductionHarvest::where('broiler_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = BroilerProductionHarvest::findOrFail($id);

        $harvest->update([
            'harvestdate' => $request['harvestdate'],
            'type' => $request['type'],
            'noofbirds' => $request['noofbirds'],
            'aveweight' => $request['aveweight'],
            'totalproduction' => $request['totalproduction'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = BroilerProductionHarvest::findOrFail($id);

        if ($harvest) {

            $harvest->delete();
            return ['message' => 'Harvest deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function showMonitor($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $monitor = BroilerProductionMonitoring::where('broiler_production_id', $id)
                    ->orderBy('monitordate', 'DESC')
                    ->get();
        
        return $monitor;
    }

    public function storeMonitor(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'monitordate' => 'required',
            'samplesize' => 'required',
            'aveweight' => 'required',
            'remarks' => 'required',
        ]);

        BroilerProductionMonitoring::create([
            'owner_id' => Auth::id(),
            'broiler_production_id' => $id,
            'monitordate' => $request['monitordate'],
            'samplesize' => $request['samplesize'],
            'aveweight' => $request['aveweight'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyMonitor($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $monitor = BroilerProductionMonitoring::findOrFail($id);

        if ($monitor) {

            $monitor->delete();
            return ['message' => 'Monitor deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function updateMonitor(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $monitor = BroilerProductionMonitoring::findOrFail($id);

        $monitor->update([
            'monitordate' => $request['monitordate'],
            'samplesize' => $request['samplesize'],
            'aveweight' => $request['aveweight'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

}
