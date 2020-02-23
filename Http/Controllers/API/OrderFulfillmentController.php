<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\OrderAllocation;
use Gate;

class OrderFulfillmentController extends Controller
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
        $this->authorize('isAdminCoopMember'); 

        return PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->where('status', '!=' ,'P')
                ->first();
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

    public function saveFulfillment(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->where('status', 'M')
                ->first();

        if ($po) {
            $fulfiller = $request->input('fulfiller');    

            foreach ($fulfiller as $filler) {
                foreach ($filler as $item) {
                    $allocation = OrderAllocation::findOrFail($item['id']);
                    $allocation->update([
                        'actual_amount' => $item['price'] * $item['actual_qty'],
                        'actual_qty' => $item['actual_qty'],
                        'grade' => $item['grade'],
                        'remarks' => $item['remarks']
                    ]);

                    $actual_qty = OrderAllocation::where('po_item_id', $allocation['po_item_id'])->sum('actual_qty');
                    $actual_amount = OrderAllocation::where('po_item_id', $allocation['po_item_id'])->sum('actual_amount');
                    $poitem = PoItem::findOrFail($allocation['po_item_id']);

                    $poitem->update([
                        'actual_qty' => $actual_qty,
                        'actual_amount' => $actual_amount,
                    ]);
                }
            }

            $total_actual_qty = PoItem::where('po_main_id', $id)->sum('actual_qty');
            $total_actual_amount = PoItem::where('po_main_id', $id)->sum('actual_amount');
            $pomain = PoMain::findOrFail($id);
            $pomain->update([
                'total_actual_qty' => $total_actual_qty,
                'total_actual_amount' => $total_actual_amount,
            ]);

        } 

        return ['message' => 'success'];
    }

    public function tagAsComplete($id) 
    {
        $this->authorize('isAdminCoopMember'); 

        $pomain = PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->where('status', 'M')
                ->first();

        if ($pomain) {
            $pomain->update([
                'status' => 'O'
            ]);

            $pomain->items()->update([
                'status' => 'C'
            ]);

            $active = OrderAllocation::where('po_main_id', $id)
                ->where('status', 'A')
                ->update(['status' => 'C']);
    
        }
        
        return ['message' => 'success'];
    }

    public  function multiTagAsComplete(Request $request)
    {
        $po = $request->input('po');

        $list = PoMain::whereIn('id', $po)
            ->where('status', 'M')
            ->get();

        foreach ($list as $item) {
            $this->autoFulfillment($item->id);    
        }

        return ['message' => 'Multi Tag as Complete'];
    }

    public function autoFulfillment($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::where(['owner_id' => Auth::id()])
                ->where('id', $id)
                ->where('status', 'M')
                ->first();

        if ($po) {
            $allocation = OrderAllocation::where('po_main_id', $po->id)
                                ->where('status', 'A')
                                ->get();

            foreach ($allocation as $item) {
                $data = OrderAllocation::findOrFail($item['id']);
                $data->update([
                    'actual_amount' => $data->amount,
                    'actual_qty' => $data->final_qty,
                    'grade' => 'A',
                    'remarks' => '',
                    'status' => 'C'
                ]);  
                
                $poitem = PoItem::findOrFail($data->po_item_id);

                $poitem->update([
                    'actual_qty' => $poitem->final_qty,
                    'actual_amount' => $poitem->amount,
                    'status' => 'C'
                ]);
                
            }

            $po->update([
                'total_actual_qty' => $po->total_final_qty,
                'total_actual_amount' => $po->total_amount,
                'status' => 'O'
            ]);

        } 

        return ['message' => 'success'];
    }
}
