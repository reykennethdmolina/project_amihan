<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\User;
use App\Models\OrderAllocation;
use App\Models\ProductionMasterList;
use Gate;

class OrderAllocationController extends Controller
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
        $this->authorize('isAdminCoopMember'); 

        return PoMain::where(['owner_id' => Auth::id()])
                ->where('status', 'M')
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
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

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $po = PoMain::where(function($query) use ($search){
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('customer_name', 'LIKE', "%$search%")
                    ->orWhere('refno', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->where('status', 'M')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
        } else {
            $po = PoMain::where(['owner_id' => Auth::id()])
                ->where('status', 'M')
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        }

        return $po;
    }

    public function thisPO($id)
    {
        $this->authorize('isAdminCoopMember'); 
        return PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->where('status', '!=' ,'P')
                ->with(['items', 'items.allocation.fulfiller'])
                ->first();
    }

    public function updateAddress(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->first();

        $this->validate($request, [
            'hoblst' => 'required|string|max:191',
            'barangay' => 'required|string|max:191',
            'province' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'postal_code' => 'required|string|max:191',
        ]);

        $po->update([
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],            
        ]);

        return ['message' => 'PO Delivery Address updated'];
    }

    public function getMembers()
    {
        //return User::where(['type' => 'member', 'group_id' => Auth::id()])
        return User::where(['type' => 'member'])
                ->whereRaw('FIND_IN_SET(?, group_id)', [Auth::id()])
                ->with(array('profile' => function($query) {
                    $query->select(['id', 'user_id', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code'])
                        ->orderBy('business_name', 'ASC');
                }))
                ->get();
    }

    public function getMemberInProduction(Request $request, $id)
    {
        $production = ProductionMasterList::where(['owner_id' => Auth::id(), 'product_id' => $id, 'status' => 'V'])
            ->pluck('member_id');

        //return User::where(['type' => 'member', 'group_id' => Auth::id()])
        return User::where(['type' => 'member'])
            ->whereRaw('FIND_IN_SET(?, group_id)', [Auth::id()])
            ->whereIn('id', $production)
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code'])
                    ->orderBy('business_name', 'ASC');
            }))
            ->get();
    }

    public function allocateToMembers(Request $request)
    {
        $item = $request['item'];
        $members = $request['members'];
        $info = $request['info'];

        foreach ($members as $member) {
            $allocate = OrderAllocation::updateOrCreate(
                ['po_main_id' => $item['po_main_id'], 'po_item_id' => $item['id'], 'product_id' => $item['product_id'], 'member_id' => $member],
                ['sku' => $item['sku'], 'name' => $item['name'], 'variety' => $item['variety'], 'slug' => $item['name'].' '.$item['variety'], 'date_needed' => $info['date_needed'], 'price' => $item['price'], 'unit' => $item['unit'], 'updated_by' => Auth::id()]
            );
        }
    }

    public function saveAllocation(Request $request, $id)
    {
        $poitem = Poitem::findOrFail($id);

        $allocation = $request['item']['allocation'];

        $total_assign = 0;
        foreach ($allocation as $allocation) {
            $oallocation = OrderAllocation::findOrFail($allocation['id']);
            $oallocation->update([
                'assign_qty' => $allocation['assign_qty'],    
                'price' => $allocation['price'],    
                'updated_by' => Auth::id(),          
            ]);
            $total_assign += $allocation['assign_qty'];
        }

        $poitem->update([
            'assign_qty' => $total_assign,              
        ]);

        $po = Pomain::findOrFail($poitem['po_main_id']);

        $total_assign_qty = PoItem::where('po_main_id', $poitem['po_main_id'])->sum('assign_qty');

        $po->update([
            'total_assign_qty' => $total_assign_qty,              
        ]);

        return ['message' => 'success'];
    }

    public function removeAllocation(Request $request, $id)
    {
        $orderAllocation = OrderAllocation::findOrFail($id);
        $orderAllocation->delete();

        $assign_qty = OrderAllocation::where('po_item_id', $orderAllocation['po_item_id'])->sum('assign_qty');
        $total_assign_qty = PoItem::where('po_main_id', $orderAllocation['po_main_id'])->sum('assign_qty');

        $poitem = PoItem::findOrFail($orderAllocation['po_item_id']);

        $poitem->update([
            'assign_qty' => $assign_qty
        ]);

        $pomain = PoMain::findOrFail($orderAllocation['po_main_id']);

        $pomain->update([
            'total_assign_qty' => $total_assign_qty
        ]);

        return $orderAllocation;
    }

    public function fulfiller($id) {
        return OrderAllocation::where('po_main_id', $id)
            ->whereNotIn('status', ['P', 'D'])
            ->with(['fulfiller', 'item'])
            ->orderBy('member_id')->get();
    }
}
