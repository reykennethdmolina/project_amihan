<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\OrderItem;

use Auth;
use Carbon\Carbon;

class OrderController extends Controller
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

        return Order::where(['seller_id' => Auth::id()])
            ->select(['id', 'order_transaction_id', 'shippingOption', 'pickUpLocation', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status',
                    'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 'waybill' ])
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'purchased_date']);
            }))
            ->where('status', '!=' ,'P')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
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
        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->first();

        if ($order) {
            $order = Order::where(['seller_id' => Auth::id()])
                ->select(['id', 'seller_id', 'buyer_id', 'order_transaction_id', 
                        'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 
                        'shippingOption', 'handling_status', 'handling_date', 'status', 'waybill'])
                ->where('id', $id)
                ->where('status', '!=', 'P')
                ->with(array('buyer' => function($query) {
                    $query->select(['id', 'firstname', 'lastname', 'mobile']);    
                }))
                ->with(array('orderItems' => function($query) {
                    $query->select(['id', 'order_id', 'sku', 'name', 'variety', 'qty', 'price', 'total_price', 'weight', 'unit']);
                }))
                ->orderBy('created_at', 'DESC')
                ->get();
            return $order;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function thisOrder($id)
    {
        return Order::where(['seller_id' => Auth::id()])
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setReadyForPickup(Request $request, $id)
    {
        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNull('handling_date')
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status']);
            }))
            ->first();

        $handlingStatus = 'P';

        if ($request->get('type') === 'QRS' || $request->get('type') === 'MRS') {
            $handlingStatus = 'RC';
        }

        if ($order) {

            /** Get Waybill */
            if ($request->get('type') === 'QRS') {
                $waybill = Order::where('status', '!=' ,'P')
                    ->where('handling_status', '!=', 'P')
                    ->whereIn('shippingOption', ['QRSEXP', 'QRSECO'])
                    ->max('waybill');
                $waybill_no = intval(substr_replace($waybill, '', 0, 3)) + 1;
                $waybill_no = 'AGR'.$waybill_no;
            } else if ($request->get('type') === 'MRS') {
                $waybill = Order::where('status', '!=' ,'P')
                    ->where('handling_status', '!=', 'P')
                    ->where('shippingOption', 'MRS')
                    ->max('waybill');    
                $waybill_no = intval(substr_replace($waybill, '', 0, 3)) + 1;
                $waybill_no = 'MRS'.$waybill_no;
            }
            
            $handling = Carbon::now();
            $order->update([
                'handling_status' => $handlingStatus,
                'handling_date' => $handling->toDateTimeString(),
                'waybill' => $waybill_no,
            ]);
        }

        return $order;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function readyFor(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'packageRemarks' => 'required|string',
        ]);

        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNull('handling_date')
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status']);
            }))
            ->first();

        $handlingStatus = 'P';

        if ($order->shippingOption === 'PICKUP') {
            $handlingStatus = 'RP';
        } else if ($order->shippingOption === 'DBS') {
            $handlingStatus = 'RD';
        }

        if ($order) {

            $handling = Carbon::now();
            $order->update([
                'handling_status' => $handlingStatus,
                'handling_date' => $handling->toDateTimeString(),
                'packageRemarks' => $request['packageRemarks'],
            ]);
        }

        return $order;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function tagAsDelivered(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNotNull('handling_date')
            ->whereNull('delivery_date')
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status']);
            }))
            ->first();

        if ($order) {

            $delivery = Carbon::now();
            $order->update([
                'status' => 'C',
                'handling_status' => 'C',
                'delivery_date' => $delivery->toDateTimeString(),
            ]);
        }

        return $order;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function QRSPackageDetail(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'package_length' => 'required|numeric|min:1',
            'package_width' => 'required|numeric|min:1',
            'package_height' => 'required|numeric|min:1',
            'package_actual_weight' => 'required|numeric|min:1',
            'packageRemarks' => 'required|string',
        ]);

        $order = Order::find($id);

        $order->update([
            'package_length' => $request['package_length'],
            'package_width' => $request['package_width'],
            'package_height' => $request['package_height'],
            'package_actual_weight' => $request['package_actual_weight'],
            'packageRemarks' => $request['packageRemarks'],
        ]);

        return ['message' => 'success'];
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
            $order = Order::where(['seller_id' => Auth::id()])
                ->select(['id', 'order_transaction_id', 'shippingOption', 'pickUpLocation', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status', 'waybill'])
                ->with(array('orderTransaction' => function($query) {
                    $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status']);
                }))
                ->whereHas('orderTransaction', function ($query) use ($search) {
                    $query->where('fullname', 'like', '%'.$search.'%')
                        ->orWhere('refNo', 'LIKE', '%'.$search.'%')
                        ->orWhere('hoblst', 'LIKE', '%'.$search.'%')
                        ->orWhere('barangay', 'LIKE', '%'.$search.'%')
                        ->orWhere('city', 'LIKE', '%'.$search.'%')
                        ->orWhere('province', 'LIKE', '%'.$search.'%');
                })
                ->where('status', '!=' ,'P')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            
        } else {
            $order = Order::where(['seller_id' => Auth::id()])
            ->select(['id', 'order_transaction_id', 'shippingOption', 'pickUpLocation', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status', 'waybill'])
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status']);
            }))
            ->where('status', '!=' ,'P')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        }

        return $order;
    }

    public function cancel($id) 
    {
        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->first();
        
        if ($order) {
            $log = $order->log.' '.Carbon::now().':: Cancelled by seller;';
            
            $order->update([
                'status' => 'CS',
                'log' => $log,
            ]);

            $order->orderItems()->update([
                'status' => 'P',
            ]);
        }

        return $order;
    }

}
