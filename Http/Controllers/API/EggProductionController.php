<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Strain;
use App\Models\EggProduction;
use App\Models\EggProductionHarvest;
use App\Models\EggProductionFeeding;
use App\Models\EggProductionMortality;
use App\Models\EggProductionVaccine;

use Carbon\Carbon;
use DB;

class EggProductionController extends Controller
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
        return EggProduction::where(['owner_id' => Auth::id()])
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
            //'batch' => 'required|max:20',
            //'housename' => 'required|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            //'housewid' => 'required|max:191',
            //'houselen' => 'required|max:191',
            'totalbird' => 'required|max:191',
        ]);

        #return Carbon::now()->format('mdYhmi');

        $production = EggProduction::create([
            'owner_id' => Auth::id(),
            'program' => $request['program'],
            'housename' => $request['housename'],
            'nurseryname' => $request['nurseryname'],
            //'batch' => Carbon::now()->format('mdYhmi').''.$request['batch'],
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

        if ($production->program == 'RTL') {
            $production->update([
                'movement_status' => 'R',
                'movementdate' => Carbon::now(),
            ]);
        }

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

        $production = EggProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->with(['strain'])
                    ->first();

        $day1 = Carbon::now()->startOfWeek()->format('Y-m-d');
        $day7 = Carbon::now()->endOfWeek()->format('Y-m-d');

        $weekly = EggProductionHarvest::groupBy('egg_production_id', 'owner_id')
                                    ->select(DB::raw('AVG(assorted) AS assorted, AVG(jumbo) AS jumbo, AVG(xlarge) AS xlarge, 
                                                    AVG(large) AS large, AVG(medium) AS medium, AVG(small) AS small,
                                                    AVG(peewee) AS peewee, AVG(rejected) AS rejected, AVG(good) AS dailyprod, SUM(total) AS totalprod'))
                                    ->where('egg_production_id', $id)
                                    ->whereDate('harvestdate','>=', $day1)
                                    ->whereDate('harvestdate','<=', $day7)
                                    ->first();
        if (empty($weekly)) {
            $weekly = ['dailyprod' => "0", 'totalprod' => "0", 'assorted' => "0", 
                       'jumbo' => "0", 'xlarge' => "0", 'large' => "0",
                       'peewee' => "0", 'rejected' => "0", 'medium' => "0"];

        }

        return [$production, $day1, $day7, $weekly];
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

        $production = EggProduction::findOrFail($id);

        $this->validate($request, [
            'program' => 'required|string|max:191',
            'poultry' => 'required|max:191',
            'strain_id' => 'required|max:191',
            'startdate' => 'required|max:191',
            'totalbird' => 'required|max:191',
        ]);

        if ($request['program'] == 'DAY') {
            $request['housename'] = '';
            $request['houselen'] = 0;
            $request['housewid'] = 0;
        } else {
            $request['nurseryname'] = '';
            $request['nurserylen'] = 0;
            $request['nurserywid'] = 0;    
        }

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

        if ($production->program == 'DAY') {
            $production->update([
                'movement_status' => 'P',
                'movementdate' => null,
                'updated_by' => $this->farm_worker,
            ]);
        }

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

    public function listOfStrains($slug)
    {
        $choices = explode("-", $slug);
        return Strain::whereIn('bird', $choices)->orderBy('bird')->orderBy('name')->get();
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            return EggProduction::where(function($query) use ($search){
                $query->where('housename', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%")
                    ->orWhere('batch', 'LIKE', "%$search%")
                    ->orWhere('poultry', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->with(['strain'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            return EggProduction::where(['owner_id' => Auth::id()])
                ->with(['strain'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }
    }

    public function move(Request $request, $id) {
        $this->authorize('isAdminCoopMember'); 

        $production = EggProduction::where('id', $id)
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

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = EggProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
                    ->first();
        
        $production->update([
            'status' => 'A',
            'originaltotalbird' => $production->totalbird,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message' => 'Program ended'];
    }

    public function retireProgram($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $program = EggProduction::findOrFail($id);

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
            'assorted' => 'required',
            'jumbo' => 'required',
            'xlarge' => 'required',
            'large' => 'required',
            'medium' => 'required',
            'small' => 'required',
            'peewee' => 'required',
            'rejected' => 'required',
            'good' => 'required',
            'total' => 'required',
        ]);

        $type = $request['type'];
        $assorted = $request['assorted'];
        $jumbo = $request['jumbo'];
        $xlarge = $request['xlarge'];
        $large = $request['large'];
        $medium = $request['medium'];
        $small = $request['small'];
        $peewee = $request['peewee'];
        $rejected = $request['rejected'];
        $good = $request['good'];
        $total = $request['total']; 

        if ($type == 'tray') {
            $assorted = $request['assorted'] * 30;
            $jumbo = $request['jumbo'] * 30;
            $xlarge = $request['xlarge'] * 30;
            $large = $request['large'] * 30;
            $medium = $request['medium'] * 30;
            $small = $request['small'] * 30;
            $peewee = $request['peewee'] * 30;
            $rejected = $request['rejected'] * 30;
            $good = $request['good'] * 30;
            $total = $request['total'] * 30;     
        }

        EggProductionHarvest::create([
            'owner_id' => Auth::id(),
            'egg_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'type' => $type,
            'assorted' => $assorted,
            'jumbo' => $jumbo,
            'xlarge' => $xlarge,
            'large' => $large,
            'medium' => $medium,
            'small' => $small,
            'peewee' => $peewee,
            'rejected' => $rejected,
            'good' => $good,
            'total' => $total,
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);


        /** Update Harvest Analytics */

        $harvested = EggProductionHarvest::groupBy('egg_production_id', 'owner_id')
                                    ->select(DB::raw('AVG(assorted) AS assorted, AVG(jumbo) AS jumbo, AVG(xlarge) AS xlarge, 
                                                    AVG(large) AS large, AVG(medium) AS medium, AVG(small) AS small,
                                                    AVG(peewee) AS peewee, AVG(rejected) AS rejected, AVG(good) AS dailyprod, SUM(total) AS totalprod'))
                                    ->where('egg_production_id', $id)
                                    ->first();

        $production = EggProduction::findOrFail($id);

        $production->update([
            'totaleggproduction' => $harvested->totalprod,
            'avedailyassorted' => $harvested->assorted,
            'avedailyegg' => $harvested->dailyprod,
            'avedailyjumbo' => $harvested->jumbo,
            'avedailyxlarge' => $harvested->xlarge,
            'avedailylarge' => $harvested->large,
            'avedailymedium' => $harvested->medium,
            'avedailysmall' => $harvested->small,
            'avedailypeewee' => $harvested->peewee,
            'avedailyrejected' => $harvested->rejected,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function showHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = EggProductionHarvest::where('egg_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = EggProductionHarvest::findOrFail($id);

        $type = $request['type'];
        $assorted = $request['assorted'];
        $jumbo = $request['jumbo'];
        $xlarge = $request['xlarge'];
        $large = $request['large'];
        $medium = $request['medium'];
        $small = $request['small'];
        $peewee = $request['peewee'];
        $rejected = $request['rejected'];
        $good = $request['good'];
        $total = $request['total']; 

        if ($type == 'tray') {
            $assorted = $request['assorted'] * 30;
            $jumbo = $request['jumbo'] * 30;
            $xlarge = $request['xlarge'] * 30;
            $large = $request['large'] * 30;
            $medium = $request['medium'] * 30;
            $small = $request['small'] * 30;
            $peewee = $request['peewee'] * 30;
            $rejected = $request['rejected'] * 30;
            $good = $request['good'] * 30;
            $total = $request['total'] * 30;     
        }
        

        $harvest->update([
            'harvestdate' => $request['harvestdate'],
            'type' => $type,
            'assorted' => $assorted,
            'jumbo' => $jumbo,
            'xlarge' => $xlarge,
            'large' => $large,
            'medium' => $medium,
            'small' => $small,
            'peewee' => $peewee,
            'rejected' => $rejected,
            'good' => $good,
            'total' => $total,
            'updated_by' => $this->farm_worker,
        ]);

        /** Update Harvest Analytics */

        $harvested = EggProductionHarvest::groupBy('egg_production_id', 'owner_id')
                                    ->select(DB::raw('AVG(assorted) AS assorted, AVG(jumbo) AS jumbo, AVG(xlarge) AS xlarge, 
                                                    AVG(large) AS large, AVG(medium) AS medium, AVG(small) AS small,
                                                    AVG(peewee) AS peewee, AVG(rejected) AS rejected, AVG(good) AS dailyprod, SUM(total) AS totalprod'))
                                    ->where('egg_production_id', $harvest->egg_production_id)
                                    ->first();

        $production = EggProduction::findOrFail($harvest->egg_production_id);

        $production->update([
            'totaleggproduction' => $harvested->totalprod,
            'avedailyassorted' => $harvested->assorted,
            'avedailyegg' => $harvested->dailyprod,
            'avedailyjumbo' => $harvested->jumbo,
            'avedailyxlarge' => $harvested->xlarge,
            'avedailylarge' => $harvested->large,
            'avedailymedium' => $harvested->medium,
            'avedailysmall' => $harvested->small,
            'avedailypeewee' => $harvested->peewee,
            'avedailyrejected' => $harvested->rejected,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = EggProductionHarvest::findOrFail($id);

        if ($harvest) {

            $egg_production_id = $harvest->egg_production_id;
            $harvest->delete();
            /** Update Harvest Analytics */

            $harvested = EggProductionHarvest::groupBy('egg_production_id', 'owner_id')
            ->select(DB::raw('AVG(assorted) AS assorted, AVG(jumbo) AS jumbo, AVG(xlarge) AS xlarge, 
                            AVG(large) AS large, AVG(medium) AS medium, AVG(small) AS small,
                            AVG(peewee) AS peewee, AVG(rejected) AS rejected, AVG(good) AS dailyprod, SUM(total) AS totalprod'))
            ->where('egg_production_id', $egg_production_id)
            ->first();

            $production = EggProduction::findOrFail($egg_production_id);
            
            if ($harvested) {
                $production->update([
                    'totaleggproduction' => $harvested->totalprod,
                    'avedailyegg' => $harvested->dailyprod,
                    'avedailyassorted' => $harvested->assorted,
                    'avedailyjumbo' => $harvested->jumbo,
                    'avedailyxlarge' => $harvested->xlarge,
                    'avedailylarge' => $harvested->large,
                    'avedailymedium' => $harvested->medium,
                    'avedailysmall' => $harvested->small,
                    'avedailypeewee' => $harvested->peewee,
                    'avedailyrejected' => $harvested->rejected,
                    'updated_by' => $this->farm_worker,
                ]);
            } else {
                $production->update([
                    'totaleggproduction' => 0,
                    'avedailyegg' => 0,
                    'avedailyassorted' => 0,
                    'avedailyjumbo' => 0,
                    'avedailyxlarge' => 0,
                    'avedailylarge' => 0,
                    'avedailymedium' => 0,
                    'avedailysmall' => 0,
                    'avedailypeewee' => 0,
                    'avedailyrejected' => 0,
                    'updated_by' => $this->farm_worker,
                ]);
            }
            return ['message' => 'Harvest deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function lastFeedingRecord($id) {
        $this->authorize('isAdminCoopMember'); 

        $feeding = EggProductionFeeding::where('egg_production_id',$id)
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

        EggProductionFeeding::create([
            'owner_id' => Auth::id(),
            'egg_production_id' => $id,
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

        $feeding = EggProductionFeeding::where('egg_production_id', $id)
                    ->orderBy('feeddate', 'DESC')
                    ->get();
        
        return $feeding;
    }

    public function updateFeeding(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $feeding = EggProductionFeeding::findOrFail($id);

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

        $feeding = EggProductionFeeding::findOrFail($id);

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

        EggProductionMortality::create([
            'owner_id' => Auth::id(),
            'egg_production_id' => $id,
            'mortalitydate' => $request['mortalitydate'],
            'noofbirds' => $request['noofbirds'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = EggProduction::findOrFail($id);

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

        $mortality = EggProductionMortality::where('egg_production_id', $id)
                    ->orderBy('mortalitydate', 'DESC')
                    ->get();
        
        return $mortality;
    }

    public function updateMortality(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $mortality = EggProductionMortality::findOrFail($id);

        $mortality->update([
            'mortalitydate' => $request['mortalitydate'],
            'noofbirds' => $request['noofbirds'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        $totalbird = 0;

        $production = EggProduction::findOrFail($mortality->egg_production_id);

        $mortalityLoss = EggProductionMortality::where('egg_production_id', $mortality->egg_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofbirds');

        $mortalityAdd = EggProductionMortality::where('egg_production_id', $mortality->egg_production_id)
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

        $mortality = EggProductionMortality::findOrFail($id);

        if ($mortality) {

            $production = EggProduction::findOrFail($mortality->egg_production_id);

            $mortality->delete();

            $mortalityLoss = EggProductionMortality::where('egg_production_id', $mortality->egg_production_id)
                                            ->where('status', 'loss')
                                            ->sum('noofbirds');

            $mortalityAdd = EggProductionMortality::where('egg_production_id', $mortality->egg_production_id)
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

        EggProductionVaccine::create([
            'owner_id' => Auth::id(),
            'egg_production_id' => $id,
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

        $vaccine = EggProductionVaccine::where('egg_production_id', $id)
                    ->orderBy('administereddate', 'DESC')
                    ->get();
        
        return $vaccine;
    }

    public function updateVaccine(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $vaccine = EggProductionVaccine::findOrFail($id);

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

        $vaccine = EggProductionVaccine::findOrFail($id);

        if ($vaccine) {

            $vaccine->delete();
            return ['message' => 'Vaccine deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }
}
