<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PreOrderTransaction;
use App\Models\PreOrder;

use Auth;
use Carbon\Carbon;
use Dragonpay;

class MyPreOrderController extends Controller
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
        return PreOrderTransaction::where(['buyer_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(5);
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
        $orderTransaction = PreOrderTransaction::where(['buyer_id' => Auth::id()])
            ->where('id', $id)
            ->first();

        if ($orderTransaction) {
            $order = PreOrder::where('pre_order_transaction_id', $orderTransaction->id)
                ->with(array('seller' => function($query) {
                    $query->select(['id', 'firstname', 'lastname', 'mobile'])
                        ->with(array('profile' => function($query) {
                            $query->select(['user_id', 'business_name', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'enable_pickup', 'delivery_target', 'delivery_fee']);
                    }));    
                }))
                ->with(array('preOrderItems' => function($query) {
                    $query->select(['id', 'pre_order_id', 'sku', 'name', 'variety', 'qty', 'price', 'asking_price', 'final_price', 'final_qty', 'total_price', 'weight', 'unit']);
                }))
                ->orderBy('created_at', 'DESC')
                ->get();
            return $order;
        } else {
            return [];
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

    public function search()
    {       
        if ($search = \Request::get('q')) {
            $orderTransaction = PreOrderTransaction::where(function($query) use ($search){
                $query->where('fullname', 'LIKE', "%$search%")
                    ->orWhere('tranRefNo', 'LIKE', "%$search%");
                })->where(['buyer_id' => Auth::id()])
                ->paginate(5);
        } else {
            $orderTransaction = PreOrderTransaction::where(['buyer_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(5);
        }

        return $orderTransaction;
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

        $orderTransaction = PreOrderTransaction::where(['buyer_id' => Auth::id()])
            ->where('id', $id)
            ->first();

        if ($orderTransaction) {
            $reason = $orderTransaction->reason.' '.Carbon::now().':: Cancelled by buyer '.$request['reason'].';';
            
            $orderTransaction->update([
                'status' => $request['status'],
                'reason' => $reason,
            ]);
        }

        return $orderTransaction;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function purchased(Request $request, $id)
    {

        $orderTransaction = PreOrderTransaction::where(['buyer_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', 'A')
            ->first();

        if ($orderTransaction) {
            $reason = $orderTransaction->reason.' '.Carbon::now().':: Purchased Order '.$request['reason'].';';
            
            $orderTransaction->update([
                'fullname' => $request['form']['fullname'],
                'mobile' => $request['form']['mobile'],
                'hoblst' => $request['form']['hoblst'],
                'barangay' => $request['form']['barangay'],
                'city' => $request['form']['city'],
                'province' => $request['form']['province'],
                'postal_code' => $request['form']['postal_code'],
                'landmark' => $request['form']['landmark'],
                'payment_mode' => $request['form']['payment_mode'],
                'purchased_date' => Carbon::now(),
                'status' => 'PO',
                'totalShippingCost' => $request['orderlist']['shippingCost'],
                'totalAmount' => floatVal($orderTransaction->totalCost) + floatVal($request['orderlist']['shippingCost']),
                'reason' => $reason,
            ]);

            $orderTransaction->preOrders()->update([
                'shippingOption' => $request['orderlist']['shippingOption'],
                'totalShippingCost' => $request['orderlist']['shippingCost'],
                'totalAmount' => floatVal($orderTransaction->totalCost) + floatVal($request['orderlist']['shippingCost']),
                'status' => 'A',
            ]);
            
            if ($request['form']['payment_mode'] == 'DP') {
                $url = $this->finalPay($orderTransaction, "DRAGONPAY");

                $orderTransaction->update([
                    'payment_link' => $url
                ]);
                
                return ['status' => true, 'url' => $url];
            } else {
                $orderTransaction->update([
                    'payment_status' => 'COD',
                ]);
                return ['status' => true, 'url' => 'COD'];   
            }
            
        }

        return ['status' => false, 'url' => 'none'];
    }

    public function finalPay($transaction , $paymentMode)
    {
        if ($paymentMode === "DRAGONPAY") {
            return $this->dragonpay($transaction);
        } else {
            return false;
        }
    }

    public function dragonpay($transaction)
    {
        $description = "PreOrder Payment for agronegosyo";
        $params = [
            'transactionId' => $transaction->id,
            'amount'        => number_format($transaction->totalAmount, 2, '.', ''),
            'currency'      => 'PHP',
            'description'   => $description,
            'email'         => auth('api')->user()->email,
        ];

        $url = Dragonpay::getUrl($params);

        return $url;
    }

}
