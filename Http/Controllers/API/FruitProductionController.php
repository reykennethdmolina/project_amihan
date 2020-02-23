<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\FruitProduction;
use App\Models\FruitProductionHarvest;
use App\Models\FruitProductionActivity;
use App\Models\FruitProductionAllocation;
use App\Models\OrderAllocation;
use App\Models\PoMain;
use App\Models\PoItem;
use Carbon\Carbon;

class FruitProductionController extends Controller
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
        return FruitProduction::where(['owner_id' => Auth::id()])
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
            'product_id' => 'required|max:191',
            'program' => 'required|max:191',
            'areawidth' => 'required|max:191',
            'arealength' => 'required|max:191',
            'noofplant' => 'required|max:191',
            'yieldperplant' => 'required|max:191',
            'first_harvest_date' => 'required|max:191',
            'last_harvest_date' => 'required|max:191',
        ]);

        $fdate = $request['first_harvest_date'];
        $first_harvest_date = new Carbon($fdate);
        $ldate = $request['last_harvest_date'];
        $last_harvest_date = new Carbon($ldate);

        $product = explode(';', $request['product_id']);

        $variety = $product[3];

        if ($variety == 'null') {
            $variety = '';
        }

        $production = FruitProduction::create([
            'owner_id' => Auth::id(),
            'product_id' => $product[0],
            'sku' => $product[1],
            'name' => $product[2],
            'slug' => $product[4],
            'variety' => $variety,
            'program' => $request['program'],
            'areawidth' => $request['areawidth'],
            'arealength' => $request['arealength'],
            'first_harvest_date' => $first_harvest_date,
            'last_harvest_date' => $last_harvest_date,
            'noofplant' => $request['noofplant'],
            'yieldperplant' => $request['yieldperplant'],
            'estproduction' => $request['estproduction'],
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

        $production = FruitProduction::where('id', $id)
                    ->where(['owner_id' => Auth::id()])
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

        $production = FruitProduction::findOrFail($id);

        $this->validate($request, [
            'product_id' => 'required|max:191',
            'program' => 'required|max:191',
            'areawidth' => 'required|max:191',
            'arealength' => 'required|max:191',
            'noofplant' => 'required|max:191',
            'yieldperplant' => 'required|max:191',
            'first_harvest_date' => 'required|max:191',
            'last_harvest_date' => 'required|max:191',
        ]);

        $fdate = $request['first_harvest_date'];
        $first_harvest_date = new Carbon($fdate);
        $ldate = $request['last_harvest_date'];
        $last_harvest_date = new Carbon($ldate);

        $product = explode(';', $request['product_id']);

        $variety = $product[3];

        if ($variety == 'null') {
            $variety = '';
        }

        $production->update([
            'product_id' => $product[0],
            'product_id' => $product[0],
            'sku' => $product[1],
            'name' => $product[2],
            'slug' => $product[4],
            'variety' => $variety,
            'program' => $request['program'],
            'areawidth' => $request['areawidth'],
            'arealength' => $request['arealength'],
            'first_harvest_date' => $first_harvest_date,
            'last_harvest_date' => $last_harvest_date,
            'noofplant' => $request['noofplant'],
            'yieldperplant' => $request['yieldperplant'],
            'estproduction' => $request['estproduction'],
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

        $plot = FruitProduction::findOrFail($id);

        if ($plot->totalallocation == 0) {
            $plot->delete();
            return ['message' => 'Program deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }  
    }

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = FruitProduction::where('id', $id)
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

        $plot = FruitProduction::findOrFail($id);

        $plot->update([
            'status' => 'R',
            'retiredate' => Carbon::now(),
            'updated_by' => $this->farm_worker,
        ]);
        
        return ['message' => 'Program ended'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $po = FruitProduction::where(function($query) use ($search){
                $query->where('program', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('variety', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $po = FruitProduction::where(['owner_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $po;
    }

    public function storeHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'harvestdate' => 'required',
            'good' => 'required',
            'rejected' => 'required',
            'remarks' => 'required',
        ]);

        FruitProductionHarvest::create([
            'owner_id' => Auth::id(),
            'fruit_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'rejected' => $request['rejected'],
            'good' => $request['good'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = FruitProduction::findOrFail($id);

        $harvested = FruitProductionHarvest::where('fruit_production_id', $id)->sum('good');
        
        $production->update([
            'actualproduction' => $harvested
        ]);

        return ['message', 'success'];
    }

    public function showHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = FruitProductionHarvest::where('fruit_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = FruitProductionHarvest::findOrFail($id);

        $this->validate($request, [
            'harvestdate' => 'required',
            'good' => 'required',
            'rejected' => 'required',
            'remarks' => 'required',
        ]);

        $harvest->update([
            'harvestdate' => $request['harvestdate'],
            'rejected' => $request['rejected'],
            'good' => $request['good'],
            'remarks' => $request['remarks'],
            'updated_by' => $this->farm_worker,
        ]);

        $production = FruitProduction::findOrFail($harvest->fruit_production_id);

        $harvested = FruitProductionHarvest::where('fruit_production_id', $harvest->fruit_production_id)->sum('good');

        $production->update([
            'actualproduction' => $harvested,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = FruitProductionHarvest::findOrFail($id);

        if ($harvest) {
            $fruit_production_id = $harvest->fruit_production_id;
            $harvest->delete();

            $production = FruitProduction::findOrFail($fruit_production_id);

            $harvested = FruitProductionHarvest::where('fruit_production_id', $fruit_production_id)->sum('good');

            if ($harvested) {
                $production->update([
                    'actualproduction' => $harvested,
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

    public function storeActivity(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'activity_date' => 'required',
            'activity' => 'required',
            'amount' => 'required',
            'remarks' => 'required',
        ]);

        FruitProductionActivity::create([
            'owner_id' => Auth::id(),
            'fruit_production_id' => $id,
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

        $activity = FruitProductionActivity::findOrFail($id);

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

    public function showActivity($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = FruitProductionActivity::where('fruit_production_id', $id)
                    ->orderBy('activity_date', 'DESC')
                    ->get();
        
        return $activity;
    }

    public function destroyActivity($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = FruitProductionActivity::findOrFail($id);

        if ($activity) {
            $activity->delete();

            return ['message' => 'Activity deleted'];
        } else {
            return ['message' => 'Something is wrong'];
        }               
    }

    public function availableAllocation($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $program = FruitProduction::findOrFail($id);

        $personal_po = PoMain::where(['owner_id' => Auth::id()], 'status' != 'P')
                            ->whereBetween('date_needed', [$program->first_harvest_date, $program->last_harvest_date])
                            ->distinct()
                            ->pluck('id');

        $production = FruitProduction::where('slug', $program->slug)->pluck('id');
                    
        $availablePO = FruitProductionAllocation::where('owner_id', Auth::id())
                                        ->whereIn('fruit_production_id', $production)
                                        ->pluck('po_main_id');

        /** hack get all same produces using slug **safer methods** */                
        $personal_item = PoItem::whereIn('po_main_id', $personal_po)
                        ->whereNotIn('id', $availablePO)
                        ->where('slug', $program->slug)
                        ->with(['pomain' => function($q) {
                            $q->select('id', 'code', 'date_needed', 'customer_name', 'status');
                        }])
                        ->get();

        /** hack get all same produces using slug **safer methods** */                
        $coop_allocation = OrderAllocation::where(['member_id' => Auth::id()])
                                    ->where('slug', $program->slug)
                                    ->whereBetween('date_needed', [$program->first_harvest_date, $program->last_harvest_date])
                                    ->where('status', 'A')
                                    ->where('type', 'C')
                                    ->whereNotIn('po_main_id', $availablePO)
                                    ->with(['pomain' => function($q) {
                                        $q->select('id', 'code', 'date_needed', 'customer_name', 'status');
                                    }])
                                    ->get();

        $pos = [];
        foreach ($personal_item as $po) {
            if ($po->pomain->status != 'P') {
                array_push($pos, 
                    array(
                        'po_code' => $po->pomain->code,
                        'po_main_id' => $po->id,
                        'date_needed' => $po->pomain->date_needed,
                        'customer' => $po->pomain->customer_name,
                        'qty' => $po->final_qty,
                        'unit' => $po->unit,
                ));
            }
        }

        foreach ($coop_allocation as $po) {
            array_push($pos, 
                array(
                    'po_code' => $po->pomain->code,
                    'po_main_id' => $po->po_main_id,
                    'date_needed' => $po->date_needed,
                    'customer' => $po->pomain->customer_name,
                    'qty' => $po->final_qty,
                    'unit' => $po->unit,
            ));
        }

        return collect($pos)->sortBy('date_needed')->values()->all();    
    }

    public function allocate(Request $request)
    {

        $this->authorize('isAdminCoopMember'); 

        $program = FruitProduction::findOrFail($request['id']);

        FruitProductionAllocation::create([
            'owner_id' => Auth::id(),
            'fruit_production_id' => $request['id'], 
            'po_main_id' => $request['po']['po_main_id'], 
            'po_code' => $request['po']['po_code'], 
            'customer' => $request['po']['customer'], 
            'allocation' => $request['po']['qty'], 
            'date_needed' => $request['po']['date_needed'], 
            'unit' => $request['po']['unit'], 
            'status' => 'P',
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $totalallocated = FruitProductionAllocation::where('fruit_production_id', $request['id'])->sum('allocation');
        
        $program->update([
            'totalallocation' => $totalallocated,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function showAllocation($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $allocation = FruitProductionAllocation::where('fruit_production_id', $id)
                    ->orderBy('date_needed', 'DESC')
                    ->get();
        
        return $allocation;
    }

    public function removeAllocation($id, $prog)
    {
        $this->authorize('isAdminCoopMember'); 

        $program = FruitProduction::findOrFail($prog);

        $allocation = FruitProductionAllocation::findOrFail($id);

        $allocation->delete();
        
        $totalallocated = FruitProductionAllocation::where('fruit_production_id', $id)->sum('allocation');
        
        $program->update([
            'totalallocation' => $totalallocated,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }
}
