<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Models\CropProduction;
use App\Models\CropProductionHarvest;
use App\Models\CropProductionActivity;
use App\Models\CropProductionAllocation;
use App\Models\OrderAllocation;
use App\Models\PoMain;
use App\Models\PoItem;
use Carbon\Carbon;

class CropProductionController extends Controller
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
        return CropProduction::where(['owner_id' => Auth::id()])
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
            'typeofproduction' => 'required|string|max:191',
            'product_id' => 'required|max:191',
            'plotname' => 'required|max:191',
            'plotwidth' => 'required|max:191',
            'plotmargin' => 'required|max:191',
            'areawidth' => 'required|max:191',
            'arealength' => 'required|max:191',
            'rows' => 'required|max:191',
            'hills' => 'required|max:191',
            'yieldperplant' => 'required|max:191',
            'transplant_date' => 'required|max:191',
            'maturity' => 'required|max:191',
            'productivity' => 'required|max:191'
        ]);

        //$tdate = str_replace('T', ' ',substr($request['transplant_date'], 0, -5));
        $tdate = $request['transplant_date'];
        $transplant_date = new Carbon($tdate);
        $maturity_date = new Carbon($tdate);
        $maturity_date = $maturity_date->addDays($request['maturity']);
        $productivity_date = new Carbon($maturity_date);
        $productivity_date = $productivity_date->addDays($request['productivity']);

        $product = explode(';', $request['product_id']);

        $variety = $product[3];

        if ($variety == 'null') {
            $variety = '';
        }

        $production = CropProduction::create([
            'owner_id' => Auth::id(),
            'typeofproduction' => $request['typeofproduction'],
            'product_id' => $product[0],
            'sku' => $product[1],
            'name' => $product[2],
            'slug' => $product[4],
            'variety' => $variety,
            'plotname' => $request['plotname'],
            'areawidth' => $request['areawidth'],
            'arealength' => $request['arealength'],
            'plotwidth' => $request['plotwidth'],
            'plotmargin' => $request['plotmargin'],
            'rows' => $request['rows'],
            'hills' => $request['hills'],
            'transplant_date' => $transplant_date,
            'maturity' => $request['maturity'],
            'maturity_date' => $maturity_date,
            'productivity' => $request['productivity'],
            'productivity_date' => $productivity_date,
            'noofplot' => $request['noofplot'],
            'noofplant' => $request['noofplant'],
            'yieldperplant' => $request['yieldperplant'],
            'yieldperplot' => $request['yieldperplot'],
            'totalplant' => $request['totalplant'],
            'estproduction' => $request['estproduction'],
            'traysize' => $request['traysize'],
            'nooftray' => $request['nooftray'],
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

        $production = CropProduction::where('id', $id)
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

        $production = CropProduction::findOrFail($id);

        $this->validate($request, [
            'typeofproduction' => 'required|string|max:191',
            'product_id' => 'required|max:191',
            'plotname' => 'required|max:191',
            'areawidth' => 'required|max:191',
            'arealength' => 'required|max:191',
            'plotwidth' => 'required|max:191',
            'plotmargin' => 'required|max:191',
            'rows' => 'required|max:191',
            'hills' => 'required|max:191',
            'yieldperplant' => 'required|max:191',
            'transplant_date' => 'required|max:191',
            'maturity' => 'required|max:191',
            'productivity' => 'required|max:191'
        ]);


        if ($production->transplant_date != $request['transplant_date']) {
            //$tdate = str_replace('T', ' ',substr($request['transplant_date'], 0, -5));
            $tdate = $request['transplant_date'];
            $transplant_date = new Carbon($tdate);
            $maturity_date = new Carbon($tdate);
            $maturity_date = $maturity_date->addDays($request['maturity']);
            $productivity_date = new Carbon($maturity_date);
            $productivity_date = $productivity_date->addDays($request['productivity']);
        } else {
            $transplant_date = new Carbon($production->transplant_date);
            $maturity_date = new Carbon($production->transplant_date);
            $maturity_date = $maturity_date->addDays($request['maturity']);
            $productivity_date = new Carbon($maturity_date);
            $productivity_date = $productivity_date->addDays($request['productivity']);
        }

        $product = explode(';', $request['product_id']);

        $variety = $product[3];

        if ($variety == 'null') {
            $variety = '';
        }

        $production->update([
            'typeofproduction' => $request['typeofproduction'],
            'product_id' => $product[0],
            'sku' => $product[1],
            'name' => $product[2],
            'slug' => $product[4],
            'variety' => $variety,
            'plotname' => $request['plotname'],
            'areawidth' => $request['areawidth'],
            'arealength' => $request['arealength'],
            'plotwidth' => $request['plotwidth'],
            'plotmargin' => $request['plotmargin'],
            'rows' => $request['rows'],
            'hills' => $request['hills'],
            'transplant_date' => $transplant_date,
            'maturity' => $request['maturity'],
            'maturity_date' => $maturity_date,
            'productivity' => $request['productivity'],
            'productivity_date' => $productivity_date,
            'noofplot' => $request['noofplot'],
            'noofplant' => $request['noofplant'],
            'yieldperplant' => $request['yieldperplant'],
            'yieldperplot' => $request['yieldperplot'],
            'totalplant' => $request['totalplant'],
            'estproduction' => $request['estproduction'],
            'traysize' => $request['traysize'],
            'nooftray' => $request['nooftray'],
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

        $plot = CropProduction::findOrFail($id);

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
            $po = CropProduction::where(function($query) use ($search){
                $query->where('plotname', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('variety', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $po = CropProduction::where(['owner_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $po;
    }

    public function activate($id) {
        $this->authorize('isAdminCoopMember'); 

        $production = CropProduction::where('id', $id)
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

        $plot = CropProduction::findOrFail($id);

        $plot->update([
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
            'good' => 'required',
            'rejected' => 'required',
            'remarks' => 'required',
        ]);

        CropProductionHarvest::create([
            'owner_id' => Auth::id(),
            'crop_production_id' => $id,
            'harvestdate' => $request['harvestdate'],
            'rejected' => $request['rejected'],
            'good' => $request['good'],
            'remarks' => $request['remarks'],
            'created_by' => $this->farm_worker,
            'updated_by' => $this->farm_worker,
        ]);

        $production = CropProduction::findOrFail($id);

        $harvested = CropProductionHarvest::where('crop_production_id', $id)->sum('good');
        
        $production->update([
            'actualproduction' => $harvested,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function showHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = CropProductionHarvest::where('crop_production_id', $id)
                    ->orderBy('harvestdate', 'DESC')
                    ->get();
        
        return $harvest;
    }

    public function updateHarvest(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = CropProductionHarvest::findOrFail($id);

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

        $production = CropProduction::findOrFail($harvest->crop_production_id);

        $harvested = CropProductionHarvest::where('crop_production_id', $harvest->crop_production_id)->sum('good');

        $production->update([
            'actualproduction' => $harvested,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    public function destroyHarvest($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $harvest = CropProductionHarvest::findOrFail($id);

        if ($harvest) {
            $crop_production_id = $harvest->crop_production_id;
            $harvest->delete();

            $production = CropProduction::findOrFail($crop_production_id);

            $harvested = CropProductionHarvest::where('crop_production_id', $crop_production_id)->sum('good');

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

        CropProductionActivity::create([
            'owner_id' => Auth::id(),
            'crop_production_id' => $id,
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

        $activity = CropProductionActivity::findOrFail($id);

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

        $activity = CropProductionActivity::where('crop_production_id', $id)
                    ->orderBy('activity_date', 'DESC')
                    ->get();
        
        return $activity;
    }

    public function destroyActivity($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $activity = CropProductionActivity::findOrFail($id);

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

        $program = CropProduction::findOrFail($id);

        $personal_po = PoMain::where(['owner_id' => Auth::id()], 'status' != 'P')
                            ->whereBetween('date_needed', [$program->maturity_date, $program->productivity_date])
                            ->distinct()
                            ->pluck('id');

        $production = CropProduction::where('slug', $program->slug)->pluck('id');
                    
        $availablePO = CropProductionAllocation::where('owner_id', Auth::id())
                                        ->whereIn('crop_production_id', $production)
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
                                    ->whereBetween('date_needed', [$program->maturity_date, $program->productivity_date])
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

        $program = CropProduction::findOrFail($request['id']);

        CropProductionAllocation::create([
            'owner_id' => Auth::id(),
            'crop_production_id' => $request['id'], 
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

        $totalallocated = CropProductionAllocation::where('crop_production_id', $request['id'])->sum('allocation');
        
        $program->update([
            'totalallocation' => $totalallocated,
        ]);

        return ['message', 'success'];
    }

    public function showAllocation($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $allocation = CropProductionAllocation::where('crop_production_id', $id)
                    ->orderBy('date_needed', 'DESC')
                    ->get();
        
        return $allocation;
    }

    public function removeAllocation($id, $prog)
    {
        $this->authorize('isAdminCoopMember'); 

        $program = CropProduction::findOrFail($prog);

        $allocation = CropProductionAllocation::findOrFail($id);

        $allocation->delete();
        
        $totalallocated = CropProductionAllocation::where('crop_production_id', $id)->sum('allocation');
        
        $program->update([
            'totalallocation' => $totalallocated,
            'updated_by' => $this->farm_worker,
        ]);

        return ['message', 'success'];
    }

    // public function pos($id)
    // {
    //     $this->authorize('isAdminCoopMember'); 

    //     $plot = CropProduction::findOrFail($id);

    //     $personal_po = PoMain::where(['owner_id' => Auth::id()], 'status' != 'P')
    //                         ->whereBetween('date_needed', [$plot->maturity_date, $plot->productivity_date])
    //                         ->distinct()
    //                         ->pluck('id');
 
    //     $production = CropProduction::where('slug', $plot->slug)->pluck('id');
                            
    //     $alPos = CropProductionAllocation::where('owner_id', Auth::id())
    //                                     ->whereIn('crop_production_id', $production)
    //                                     ->pluck('po_main_id');

    //     /** hack get all same produces using slug **safer methods** */                
    //     $personal_item = PoItem::whereIn('po_main_id', $personal_po)
    //                     ->whereNotIn('id', $alPos)
    //                     ->where('slug', $plot->slug)
    //                     ->with(['pomain' => function($q) {
    //                         $q->select('id', 'code', 'date_needed', 'status');
    //                     }])
    //                     ->get();
        
    //     /** hack get all same produces using slug **safer methods** */                
    //     $coop_allocation = OrderAllocation::where(['member_id' => Auth::id()])
    //                                 ->where('slug', $plot->slug)
    //                                 ->whereBetween('date_needed', [$plot->maturity_date, $plot->productivity_date])
    //                                 ->where('status', 'A')
    //                                 ->where('type', 'C')
    //                                 ->whereNotIn('po_main_id', $alPos)
    //                                 ->with(['pomain' => function($q) {
    //                                     $q->select('id', 'code', 'date_needed', 'status');
    //                                 }])
    //                                 ->get();
        
    //     $pos = [];
    //     foreach ($personal_item as $po) {
    //         if ($po->pomain->status != 'P') {
    //             array_push($pos, 
    //                 array(
    //                     'po_code' => $po->pomain->code,
    //                     'po_main_id' => $po->id,
    //                     'date_needed' => $po->pomain->date_needed,
    //                     'qty' => $po->final_qty,
    //                     'unit' => $po->unit,
    //             ));
    //         }
    //     }

    //     foreach ($coop_allocation as $po) {
    //         array_push($pos, 
    //             array(
    //                 'po_code' => $po->pomain->code,
    //                 'po_main_id' => $po->po_main_id,
    //                 'date_needed' => $po->date_needed,
    //                 'qty' => $po->final_qty,
    //                 'unit' => $po->unit,
    //         ));
    //     }

    //     return collect($pos)->sortBy('date_needed')->values()->all();
    // }

    // public function allocate(Request $request)
    // {

    //     $this->authorize('isAdminCoopMember'); 

    //     $plot = CropProduction::findOrFail($request['id']);

    //     CropProductionAllocation::create([
    //         'owner_id' => Auth::id(),
    //         'crop_production_id' => $request['id'], 
    //         'po_main_id' => $request['po']['po_main_id'], 
    //         'po_code' => $request['po']['po_code'], 
    //         'allocation' => $request['po']['qty'], 
    //         'date_needed' => $request['po']['date_needed'], 
    //         'unit' => $request['po']['unit'], 
    //         'status' => 'P',
    //     ]);

    //     $totalallocation = floatval($plot->totalallocation) + floatval($request['po']['qty']);
        
    //     $plot->update([
    //         'totalallocation' => $totalallocation,
    //     ]);

    //     return ['totalallocation' => $totalallocation];
    // }

    // public function posAl($id)
    // {
    //     $this->authorize('isAdminCoopMember'); 

    //     $allocation = CropProductionAllocation::where('crop_production_id', $id)->orderBy('date_needed')->get();

    //     return $allocation;
    // }

    // public function removeAllocate($id, $plot)
    // {
    //     $this->authorize('isAdminCoopMember'); 

    //     $plot = CropProduction::findOrFail($plot);

    //     $allocation = CropProductionAllocation::findOrFail($id);

    //     $totalallocation = floatval($plot->totalallocation) - floatval($allocation->allocation);

    //     $allocation->delete();
        
    //     $plot->update([
    //         'totalallocation' => abs($totalallocation),
    //     ]);

    //     return ['totalallocation' => abs($totalallocation)];
    // }

    // public function saveActivity(Request $request, $production_id)
    // {
    //     $this->authorize('isAdminCoopMember'); 

    //     $plot = CropProduction::findOrFail($production_id);

    //     CropProductionActivity::create([
    //         'owner_id' => Auth::id(),
    //         'crop_production_id' => $production_id,
    //         'activity_date' => $request['activity_date'],
    //         'qty' => abs($request['qty']),
    //         'remarks' => $request['remarks']
    //     ]);

    //     $actualproduction = CropProductionActivity::where('crop_production_id', $production_id)->sum('qty');
        
    //     $plot->update([
    //         'actualproduction' => $actualproduction,
    //     ]);

    //     return ['actualproduction' => abs($actualproduction)];
    // }

    // public function activityLogs($production_id)
    // {
    //     $this->authorize('isAdminCoopMember'); 
        
    //     return CropProductionActivity::where('owner_id', Auth::id())
    //         ->where('crop_production_id', $production_id)
    //         ->orderBy('activity_date', 'DESC')
    //         ->get();
    // }

    // public function updateActivityLogs(Request $request, $id)
    // {
    //     $this->authorize('isAdminCoopMember'); 

    //     $activity = CropProductionActivity::findOrFail($id);

    //     $plot = CropProduction::findOrFail($activity->crop_production_id);
        
    //     $activity->update([
    //         'activity_date' => $request['activity_date'],
    //         'qty' => abs($request['qty']),
    //         'remarks' => $request['remarks']
    //     ]);

    //     $actualproduction = CropProductionActivity::where('crop_production_id', $activity->crop_production_id)->sum('qty');

    //     $plot->update([
    //         'actualproduction' => $actualproduction,
    //     ]);

    //     return ['actualproduction' => abs($actualproduction)];
    // }
}
