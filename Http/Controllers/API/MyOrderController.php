<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\OrderTransaction;
use App\Models\Order;

use Auth;
use Carbon\Carbon;

class MyOrderController extends Controller
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
        return OrderTransaction::where(['buyer_id' => Auth::id()])
                //->select('id', 'payment_status')
                //->where('payment_status', 'S')
                //->whereNotNull('payment_date')
                //->orderBy('payment_date', 'DESC')
                //->orderByRaw("FIELD(payment_status,'P','S','COD')")
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
        $orderTransaction = OrderTransaction::where(['buyer_id' => Auth::id()])
            ->where('id', $id)
            //->where('payment_status', 'S')
            //->whereNotNull('payment_date')
            ->first();

        if ($orderTransaction) {
            $order = Order::where('order_transaction_id', $orderTransaction->id)
                //->where('status', '!=', 'P')
                ->with(array('seller' => function($query) {
                    $query->select(['id', 'firstname', 'lastname', 'mobile'])
                        ->with(array('profile' => function($query) {
                            $query->select(['user_id', 'business_name', 'hoblst', 'barangay', 'city', 'province', 'postal_code']);
                    }));    
                }))
                ->with(array('orderItems' => function($query) {
                    $query->select(['id', 'order_id', 'sku', 'name', 'variety', 'qty', 'price', 'total_price', 'weight', 'unit']);
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
            $orderTransaction = OrderTransaction::where(function($query) use ($search){
                $query->where('fullname', 'LIKE', "%$search%")
                    ->orWhere('refNo', 'LIKE', "%$search%");
                })->where(['buyer_id' => Auth::id()])
                //->where('payment_status', 'S')
                //->whereNotNull('payment_date')
                //->orderByRaw("FIELD(payment_status,'P','S','COD')")
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        } else {
            $orderTransaction = OrderTransaction::where(['buyer_id' => Auth::id()])
                //->where('payment_status', 'S')
                //->whereNotNull('payment_date')
                //->orderByRaw("FIELD(payment_status,'P','S','COD')")
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        }

        return $orderTransaction;
    }

    public function cancel($id) 
    {
        $order = Order::where(['buyer_id' => Auth::id()])
            ->where('id', $id)
            ->first();
        
        if ($order) {
            $log = $order->log.' '.Carbon::now().':: Cancelled by buyer;';
            
            $order->update([
                'status' => 'CB',
                'log' => $log,
            ]);

            $order->orderItems()->update([
                'status' => 'P',
            ]);
        }

        return $order;
    }
}
