<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PreOrderTransaction;
use App\Models\PreOrder;
use App\Models\PreOrderItem;
use App\Models\Product;

use Carbon\Carbon;
use Auth;

class PreOrderController extends Controller
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

        return PreOrder::where(['seller_id' => Auth::id()])
            ->select(['id', 'pre_order_transaction_id', 'shippingOption', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status',
                    'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 'waybill' ])
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'purchased_date', 'status', 'payment_mode', 'payment_status', 'dateNeeded', 'remarks', 'reason']);
            }))
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
        #return auth('api')->user();
        #echo 'yes';
        $user = auth('api')->user();
        $orderLists = $request->orderList;

        $dateNeeded = str_replace('T', ' ',substr($orderLists[0]['dateNeeded'], 0, -5));
        $remarks = $orderLists[0]['preOrderRemarks'];

        /** Initiate order transaction */
        $transaction = PreOrderTransaction::firstOrCreate(['buyer_id' => $user->id,
            'tranRefNo' => null,
            'refNo' => null,
            'purchased_date' => null,
            'payment_date' => null,
            'payment_status' => 'P',
        ]);


        if (!empty($transaction)) {
            $ttotalWeight = 0; $ttotalCost = 0; $ttotalShippingCost = 0; $ttotalAmount = 0;
            /** Initiate transaction */
            $transaction->update([
                'tranRefNo' => strtoupper(str_random(6).''.$transaction->id),
                'fullname' => $user->firstname.' '.$user->lastname,
                'mobile' => $user->mobile,
                'dateNeeded' => $dateNeeded,
                'remarks' => $remarks,
            ]);

            $order = PreOrder::firstOrCreate([
                'pre_order_transaction_id' => $transaction->id,
                'buyer_id' => $user->id,
                'seller_id' => $orderLists[0]['seller_id'],
                'status' => 'P',
            ]);

            $totalWeight = 0; $totalCost = 0; $totalShippingCost = 0; $totalAmount = 0;
            $vatable = 0; $vatexempt = 0; $vatzero = 0; $vatamount = 0; $commission = 0;
            foreach ($orderLists as $item) {
                
                $orderItem = PreOrderItem::firstOrCreate([
                    'pre_order_id' => $order->id,
                    'buyer_id' => $user->id,
                    'seller_id' => $order->seller_id,
                    'product_id' => $item['id'],
                    'status' => 'P',
                ]);

                $product = Product::find($item['id']);
                
                $orderItem->update([
                    'sku' => $item['sku'],
                    'name' => $product->name,
                    'variety' => $item['variety'],
                    'qty' => $item['qty'],
                    'price' => $product['price'],
                    'asking_price' => $item['askingPrice'],
                    'vatable_amount' => $product['vatable_amount'] * $item['qty'],
                    'vat_exempt_amount' => $product['vat_exempt_amount'] * $item['qty'],
                    'vat_zero_amount' => $product['vat_zero_amount'] * $item['qty'],
                    'vat_amount' => $product['vat_amount'] * $item['qty'],
                    'total_price' => $product['price'] * $item['qty'],
                    'vat_type' => $product['vat_type'],
                    'vat_rate' => $product['vat_rate'],
                    'commission_rate' => $product['commission_rate'],
                    'commission_amount' => $product['commission_amount'] * $item['qty'],
                    'weight' => $product['weight'] * $item['qty'],
                    'unit' => $item['unit'],
                ]);

                $totalWeight += $product['weight'] * $item['qty'];
                $totalCost += $product['price'] * $item['qty'];
                $totalShippingCost += 0;
                $totalAmount += ($product['price'] * $item['qty']) + 0;

                $vatable += $product['vatable_amount'] * $item['qty'];
                $vatexempt += $product['vat_exempt_amount'] * $item['qty'];
                $vatzero += $product['vat_zero_amount'] * $item['qty'];
                $vatamount += $product['vat_amount'] * $item['qty'];
                $commission += $product['commission_amount'] * $item['qty'];
            
            }
            $ttotalWeight += $totalWeight; 
            $ttotalCost += $totalCost; 
            $ttotalShippingCost += $totalShippingCost; 
            $ttotalAmount += $totalAmount;

            $order->update([
                'vatable_amount' => $vatable,
                'vat_exempt_amount' => $vatexempt,
                'vat_zero_amount' => $vatzero,
                'vat_amount' => $vatamount,
                'commission_amount' => $commission,
                'totalCost' => $totalCost,
                'totalWeight' => $totalWeight,
                'totalShippingCost' => $totalShippingCost,
                'totalAmount' => $totalAmount,
            ]);

            $transaction->update([
                'totalCost' => $ttotalCost,
                'totalWeight' => $ttotalWeight,
                'totalShippingCost' => $ttotalShippingCost,
                'totalAmount' => $ttotalAmount,
            ]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = PreOrder::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->first();

        if ($order) {
            $order = PreOrder::where(['seller_id' => Auth::id()])
                ->select(['id', 'seller_id', 'buyer_id', 'pre_order_transaction_id', 
                        'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 
                        'shippingOption', 'handling_status', 'handling_date', 'status', 'waybill'])
                ->where('id', $id)
                ->with(array('buyer' => function($query) {
                    $query->select(['id', 'firstname', 'lastname', 'mobile']);    
                }))
                ->with(array('preOrderItems' => function($query) {
                    $query->select(['id', 'pre_order_id', 'sku', 'name', 'variety', 'qty', 'final_qty', 'asking_price', 'price', 'final_price', 'total_price', 'weight', 'unit']);
                }))
                ->with(array('preOrderTransaction' => function($query) {
                    $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'status', 'payment_mode', 'payment_status', 'dateNeeded', 'remarks', 'reason']);
                }))
                ->orderBy('created_at', 'DESC')
                ->get();
            return $order;
        }
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request, $id)
    {
        $orderTransaction = PreOrderTransaction::where('id', $id)
            ->first();

        if ($orderTransaction) {
            $reason = $orderTransaction->reason.' '.Carbon::now().':: Cancelled by seller '.$request['reason'].';';
            
            $orderTransaction->update([
                'status' => $request['status'],
                'reason' => $reason,
            ]);
        }

        return PreOrder::where(['seller_id' => Auth::id()])
            ->select(['id', 'pre_order_transaction_id', 'shippingOption', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status',
                    'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 'waybill' ])
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'payment_mode', 'status', 'dateNeeded', 'remarks', 'reason']);
            }))
            ->where('pre_order_transaction_id', $id)
            ->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        $orderTransaction = PreOrderTransaction::where('id', $id)
            ->first();

        if ($orderTransaction) {
            $items = $request['list'];
            $price = 0;
            $qty = 0;
            $vatable = 0; $vatexempt = 0; $vatzero = 0; $vatamount = 0; $commission = 0; $totalAmount = 0; $totalWeight = 0;
            foreach ($items as $item) {
                if (floatVal(abs($item['final_price'])) === floatVal(0)) {
                    $price = floatVal($item['price']);
                } else {
                    $price = floatVal(abs($item['final_price']));
                }
                if (abs($item['final_qty']) === abs(0)) {
                    $qty = abs($item['qty']);
                } else {
                    $qty = abs($item['final_qty']);
                }
                $item['final_price'] = $price;
                $item['final_qty'] = $qty;

                $preOrderItem = PreOrderItem::where('id', $item['id'])->first();
                $compute = $this->compute($item, $preOrderItem->vat_type, $preOrderItem->vat_rate);
                
                $product = Product::where('id', $preOrderItem->product_id)->select('weight')->first();
                $weight = floatVal($product->weight * $qty);

                $preOrderItem->update([
                    'final_price' => $price,
                    'final_qty' => $qty,
                    'weight' => $weight,
                    'vatable_amount' => $compute['vatable_amount'],
                    'vat_exempt_amount' => $compute['vat_exempt_amount'],
                    'vat_zero_amount' => $compute['vat_zero_amount'],
                    'vat_amount' => $compute['vat_amount'],
                    'commission_amount' => $compute['commission_amount'],
                    'total_price' => $compute['total_price'],
                ]);

                $vatable += $compute['vatable_amount'];
                $vatexempt += $compute['vat_exempt_amount'];
                $vatzero += $compute['vat_zero_amount'];
                $vatamount += $compute['vat_amount'];
                $commission += $compute['commission_amount'];
                $totalAmount += $compute['total_price'];
                $totalWeight += $weight;
            }
            $orderTransaction->preOrders()->update([
                'vatable_amount' => $vatable,
                'vat_exempt_amount' => $vatexempt,
                'vat_zero_amount' => $vatzero,
                'vat_amount' => $vatamount,
                'commission_amount' => $commission,
                'totalCost' => $totalAmount,
                'totalAmount' => $totalAmount,
                'totalWeight' => $totalWeight,
            ]);

            $reason = $orderTransaction->reason.' '.Carbon::now().':: Accepted by seller;';
            $orderTransaction->update([
                'status' => 'A',
                'reason' => $reason,
                'totalCost' => $totalAmount,
                'totalAmount' => $totalAmount,
                'totalWeight' => $totalWeight,
            ]);
        }

        return PreOrder::where(['seller_id' => Auth::id()])
            ->select(['id', 'pre_order_transaction_id', 'shippingOption', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status',
                    'package_length', 'package_width', 'package_height', 'package_actual_weight', 'packageRemarks', 'waybill' ])
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'payment_mode', 'status', 'dateNeeded', 'remarks', 'reason']);
            }))
            ->where('pre_order_transaction_id', $id)
            ->first();
    }

    private function compute($request, $vat, $vatrate) {
        $vatable_amount = 0;
        $vat_exempt_amount = 0;
        $vat_zero_amount = 0;
        $vat_amount = 0;
        $vat_type = $vat;
        $vat_rate = $vatrate;
        $commission_rate = 3;
        $commission_amount = 0;

        $price = $request['final_price'] * $request['final_qty'];

        if ($vat == 'VAT12') {
            $vatable_amount = round($price / (1 + ( $vat_rate / 100 )), 2);
            $commission_amount = round($vatable_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vatable_amount * ($vat_rate / 100 ), 2);
        } else if ($vat == 'VE') {
            $vat_exempt_amount = $price;
            $commission_amount = round($vat_exempt_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vat_exempt_amount * ($vat_rate / 100 ), 2);
        } else {
            $vat_zero_amount = $price;
            $commission_amount = round($vat_zero_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vat_zero_amount * ($vat_rate / 100 ), 2);
        }

        return ([
            'vatable_amount' => $vatable_amount,
            'vat_exempt_amount' => $vat_exempt_amount,
            'vat_zero_amount' => $vat_zero_amount,
            'vat_amount' => $vat_amount,
            'vat_type' => $vat_type,
            'vat_rate' => $vat_rate,
            'commission_rate' => $commission_rate,
            'commission_amount' => $commission_amount,
            'total_price' => $vatable_amount + $vat_exempt_amount + $vat_zero_amount + $vat_amount,
        ]);
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $order = PreOrder::where(['seller_id' => Auth::id()])
                ->select(['id', 'pre_order_transaction_id', 'shippingOption', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status', 'waybill'])
                ->with(array('preOrderTransaction' => function($query) {
                    $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'payment_mode', 'status']);
                }))
                ->whereHas('preOrderTransaction', function ($query) use ($search) {
                    $query->where('fullname', 'like', '%'.$search.'%')
                        ->orWhere('tranRefNo', 'LIKE', '%'.$search.'%')
                        ->orWhere('hoblst', 'LIKE', '%'.$search.'%')
                        ->orWhere('barangay', 'LIKE', '%'.$search.'%')
                        ->orWhere('city', 'LIKE', '%'.$search.'%')
                        ->orWhere('province', 'LIKE', '%'.$search.'%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            
        } else {
            $order = PreOrder::where(['seller_id' => Auth::id()])
            ->select(['id', 'pre_order_transaction_id', 'shippingOption', 'totalWeight', 'totalCost', 'handling_status', 'handling_date', 'status', 'waybill'])
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'payment_mode', 'status']);
            }))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
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
            'package_actual_weight' => 'required|numeric',
            'packageRemarks' => 'required|string',
        ]);

        $order = PreOrder::find($id);

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function thisPreOrder($id)
    {
        return PreOrder::where(['seller_id' => Auth::id()])
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
    public function setReadyForPickup(Request $request, $id)
    {
        $order = PreOrder::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNull('handling_date')
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'payment_mode', 'status']);
            }))
            ->first();

        $handlingStatus = 'P';

        if ($request->get('type') === 'QRS' || $request->get('type') === 'MRS') {
            $handlingStatus = 'RC';
        }

        if ($order) {

            /** Get Waybill */
            if ($request->get('type') === 'QRS') {
                $waybill = PreOrder::where('status', '!=' ,'P')
                    ->where('handling_status', '!=', 'P')
                    ->whereIn('shippingOption', ['QRSEXP', 'QRSECO'])
                    ->max('waybill');
                $waybill_no = intval(substr_replace($waybill, '', 0, 4)) + 1;
                $waybill_no = 'AGRP'.$waybill_no;
            } else if ($request->get('type') === 'MRS') {
                $waybill = PreOrder::where('status', '!=' ,'P')
                    ->where('handling_status', '!=', 'P')
                    ->where('shippingOption', 'MRS')
                    ->max('waybill');    
                $waybill_no = intval(substr_replace($waybill, '', 0, 4)) + 1;
                $waybill_no = 'MRSP'.$waybill_no;
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
    public function tagAsDelivered(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $order = PreOrder::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNotNull('handling_date')
            ->whereNull('delivery_date')
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'status']);
            }))
            ->first();

        if ($order) {

            $delivery = Carbon::now();
            $order->update([
                'status' => 'C',
                'delivery_date' => $delivery->toDateTimeString(),
            ]);
            $order->preOrderItems()->update(['status' => 'C']);

            if ($order->preOrderTransaction['payment_status'] === 'COD') {
                $order->preOrderTransaction->update([
                    'payment_date' => $delivery->toDateTimeString(),
                ]);
            }
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

        $order = PreOrder::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->whereNull('handling_date')
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'mobile', 'hoblst', 'barangay', 'city', 'province', 'payment_date', 'payment_status', 'status']);
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
}
