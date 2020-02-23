<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OrderAllocation;
use App\Models\PoMain;
use App\Models\PoItem;

use Auth;

class MyOrderAllocationController extends Controller
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
        $orderAllocation = OrderAllocation::where(['member_id' => Auth::id()])
                                        ->distinct()
                                        ->pluck('po_main_id');

        return PoMain::whereIn('id', $orderAllocation)
                    ->with(['owner', 'allocateTo'])
                    ->where('type', 0)
                    ->orderBy('status', 'ASC')
                    ->orderBy('date_needed', 'DESC')
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
        return OrderAllocation::where('member_id', Auth::id())
                            ->where('po_main_id', $id)
                            ->get();
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

        $orderAllocation = OrderAllocation::where(['member_id' => Auth::id()])
                                        ->distinct()
                                        ->pluck('po_main_id');
        
        if ($search = \Request::get('q')) {
            $po = PoMain::where(function($query) use ($search){
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('date_needed', 'LIKE', "%$search%")
                    ->orWhere('refno', 'LIKE', "%$search%");
            })->whereIn('id', $orderAllocation)
            ->with(['owner'])
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
        } else {
            $po = PoMain::whereIn('id', $orderAllocation)
                ->with(['owner'])
                ->orderBy('date_needed', 'ASC')
                ->paginate(15);
        }

        return $po;
    }

    public function setStatus(Request $request, $id, $status)
    {
        $item = $request->input('item');
        
        $allocation = OrderAllocation::findOrFail($id);

        if ($status == 'D') {
            $allocation->update([
                'status' => 'D',
                'final_qty' => 0,
                'amount' => 0,
                'updated_by' => Auth::id(),
            ]);
        } else {
            $allocation->update([
                'status' => 'A',
                'final_qty' => $item['final_qty'],
                'amount' => round($item['final_qty'] * $item['price'], 2),
                'updated_by' => Auth::id(),
            ]);
        }

        $final_qty = OrderAllocation::where('po_item_id', $allocation->po_item_id)->sum('final_qty');

        $poitem = PoItem::findOrFail($allocation->po_item_id);

        $poitem->update([
            'final_qty' => $final_qty
        ]);

        $total_final_qty = PoItem::where('po_main_id', $allocation->po_main_id)->sum('final_qty');

        $pomain = PoMain::findOrFail($allocation->po_main_id);

        $pomain->update([
            'total_final_qty' => $total_final_qty
        ]);

        return OrderAllocation::where('member_id', Auth::id())
                            ->where('po_main_id', $allocation->po_main_id)
                            ->get();
    }
}
