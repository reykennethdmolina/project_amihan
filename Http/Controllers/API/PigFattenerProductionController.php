<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Strain;
use App\Models\PigFattenerProduction;
use App\Models\PigFattenerProductionHarvest;
use App\Models\PigFattenerProductionMonitoring;
use App\Models\PigFattenerProductionFeeding;
use App\Models\PigFattenerProductionMortality;
use App\Models\PigFattenerProductionVaccine;

use Carbon\Carbon;
use DB;

class PigFattenerProductionController extends Controller
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
        return PigFattenerProduction::where(['owner_id' => Auth::id()])
                ->with(['strain'])
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
            'program' => 'required|string|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            'totalpig' => 'required|max:191',
        ]);
        $production = PigFattenerProduction::create([
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
            'totalpig' => $request['totalpig'],
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

        $production = PigFattenerProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->with(['strain'])
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

        $production = PigFattenerProduction::findOrFail($id);

        $this->validate($request, [
            'program' => 'required|string|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            'totalpig' => 'required|max:191',
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
            'totalpig' => $request['totalpig'],   
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
        //
    }

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = PigFattenerProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->first();
        
        $production->update([
            'status' => 'A',
            'originaltotalpig' => $production->totalpig,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Program ended'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            return PigFattenerProduction::where(function($query) use ($search){
                $query->where('housename', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%")
                    ->orWhere('batch', 'LIKE', "%$search%")
                    ->orWhere('poultry', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->with(['strain'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            return PigFattenerProduction::where(['owner_id' => Auth::id()])
                ->with(['strain'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }
    }

    public function move(Request $request, $id) {
        $this->authorize('isAdminCoopMember'); 

        $production = PigFattenerProduction::where('id', $id)
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

        $program = PigFattenerProduction::findOrFail($id);

        $program->update([
            'status' => 'R',
            'retiredate' => Carbon::now(),
            'updated_by' => $this->farm_worker,
        ]);
        
        return ['message' => 'Program ended'];
    }

    public function storeHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'harvestdate' => 'required',
            'type' => 'required',
            'noofpigs' => 'required',
            'aveweight' => 'required',
            'totalproduction' => 'required',
        ]);

        PigFattenerProductionHarvest::create([
            'owner_id' => Auth::id(),
            'pig_fattener_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'type' => $request['type'],
            'noofpigs' => $request['noofpigs'],
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

        $harvest = PigFattenerProductionHarvest::where('pig_fattener_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = PigFattenerProductionHarvest::findOrFail($id);

        $harvest->update([
            'harvestdate' => $request['harvestdate'],
            'type' => $request['type'],
            'noofpigs' => $request['noofpigs'],
            'aveweight' => $request['aveweight'],
            'totalproduction' => $request['totalproduction'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = PigFattenerProductionHarvest::findOrFail($id);

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

        $monitor = PigFattenerProductionMonitoring::where('pig_fattener_production_id', $id)
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

        PigFattenerProductionMonitoring::create([
            'owner_id' => Auth::id(),
            'pig_fattener_production_id' => $id,
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

        $monitor = PigFattenerProductionMonitoring::findOrFail($id);

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

        $monitor = PigFattenerProductionMonitoring::findOrFail($id);

        $monitor->update([
            'monitordate' => $request['monitordate'],
            'samplesize' => $request['samplesize'],
            'aveweight' => $request['aveweight'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function lastFeedingRecord($id) {
        $this->authorize('isAdminCoopMember'); 

        $feeding = PigFattenerProductionFeeding::where('pig_fattener_production_id',$id)
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
            'fppigs' => 'required',
            'population' => 'required',
            'totalfeeds' => 'required',
        ]);

        PigFattenerProductionFeeding::create([
            'owner_id' => Auth::id(),
            'pig_fattener_production_id' => $id,
            'feeddate' => $request['feeddate'],
            'feedbrand' => $request['feedbrand'],
            'type' => $request['type'],
            'fppigs' => $request['fppigs'],
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

        $feeding = PigFattenerProductionFeeding::where('pig_fattener_production_id', $id)
                    ->orderBy('feeddate', 'DESC')
                    ->get();
        
        return $feeding;
    }

    public function updateFeeding(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = PigFattenerProductionFeeding::findOrFail($id);

        $feeding->update([
            'feeddate' => $request['feeddate'],
            'feedbrand' => $request['feedbrand'],
            'type' => $request['type'],
            'fppigs' => $request['fppigs'],
            'population' => $request['population'],
            'totalfeeds' => $request['totalfeeds'],
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyFeeding($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = PigFattenerProductionFeeding::findOrFail($id);

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
            'noofpigs' => 'required',
            'status' => 'required',
            'remarks' => 'required',
        ]);

        PigFattenerProductionMortality::create([
            'owner_id' => Auth::id(),
            'pig_fattener_production_id' => $id,
            'mortalitydate' => $request['mortalitydate'],
            'noofpigs' => $request['noofpigs'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = PigFattenerProduction::findOrFail($id);

        if ($request['status'] == 'loss') {
            $totalpig = $production->totalpig - $request['noofpigs'];
        } else {
            $totalpig = $production->totalpig + $request['noofpigs'];
        }

        $production->update([
            'totalpig' => $totalpig,
            'updated_by' => $this->farm_worker,
        ]);

        return $totalpig;
    }

    public function showMortality($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = PigFattenerProductionMortality::where('pig_fattener_production_id', $id)
                    ->orderBy('mortalitydate', 'DESC')
                    ->get();
        
        return $mortality;
    }

    public function updateMortality(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = PigFattenerProductionMortality::findOrFail($id);

        $mortality->update([
            'mortalitydate' => $request['mortalitydate'],
            'noofpigs' => $request['noofpigs'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        $totalpig = 0;

        $production = PigFattenerProduction::findOrFail($mortality->pig_fattener_production_id);

        $mortalityLoss = PigFattenerProductionMortality::where('pig_fattener_production_id', $mortality->pig_fattener_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofpigs');

        $mortalityAdd = PigFattenerProductionMortality::where('pig_fattener_production_id', $mortality->pig_fattener_production_id)
                                            ->where('status', 'add')
                                            ->sum('noofpigs');
        
        $totalpig = $production->originaltotalpig + ($mortalityAdd - $mortalityLoss);

        $production->update([
            'totalpig' => $totalpig,
            'updated_by' => $this->farm_worker,
        ]);
        
        return $totalpig;
    }

    public function destroyMortality($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = PigFattenerProductionMortality::findOrFail($id);

        if ($mortality) {

            $production = PigFattenerProduction::findOrFail($mortality->pig_fattener_production_id);

            $mortality->delete();

            $mortalityLoss = PigFattenerProductionMortality::where('pig_fattener_production_id', $mortality->pig_fattener_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofpigs');

            $mortalityAdd = PigFattenerProductionMortality::where('pig_fattener_production_id', $mortality->pig_fattener_production_id)
                                                ->where('status', 'add')
                                                ->sum('noofpigs');
            
            $totalpig = $production->originaltotalpig + ($mortalityAdd - $mortalityLoss);

            $production->update([
                'totalpig' => $totalpig,
                'updated_by' => $this->farm_worker,
            ]);
            
            return $totalpig;
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

        PigFattenerProductionVaccine::create([
            'owner_id' => Auth::id(),
            'pig_fattener_production_id' => $id,
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

        $vaccine = PigFattenerProductionVaccine::where('pig_fattener_production_id', $id)
                    ->orderBy('administereddate', 'DESC')
                    ->get();
        
        return $vaccine;
    }

    public function updateVaccine(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $vaccine = PigFattenerProductionVaccine::findOrFail($id);

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

        $vaccine = PigFattenerProductionVaccine::findOrFail($id);

        if ($vaccine) {

            $vaccine->delete();
            return ['message' => 'Vaccine deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }
}
