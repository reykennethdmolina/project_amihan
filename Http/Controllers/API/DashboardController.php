<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\Order;
use App\Models\OrderAllocation;
use App\Models\PreOrder;
use App\Models\Product;

use Auth;
use Carbon\Carbon;

class DashboardController extends Controller
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

    public function latestPO()
    {
        return PoMain::where(['owner_id' => Auth::id()])
            ->select(['id', 'code', 'customer_name', 'podate', 'date_needed', 'refno', 'total_amount', 'status', 'po_status', 'payment_status'])
            ->orderBy('code', 'DESC')
            ->limit(15)
            ->get();
    }

    public function latestDuePO()
    {
        // 7 Days
        $from = Carbon::today()->toDateString();
        $to = Carbon::today()->addDays(7)->toDateString();

        return PoMain::where(['owner_id' => Auth::id()])
            ->select(['id', 'code', 'customer_name', 'podate', 'date_needed', 'refno', 'total_amount', 'status', 'po_status', 'payment_status'])
            //->where('status', 'M')
            ->whereBetween('date_needed', [$from, $to])
            ->orderBy('date_needed', 'ASC')
            ->limit(15)
            ->get();    
    }

    public function latestProduceDuePO()
    {
        // 7 Days
        $from = Carbon::today()->toDateString();
        $to = Carbon::today()->addDays(7)->toDateString();

        $main = PoMain::where(['owner_id' => Auth::id()])
            ->select(['id', 'code', 'customer_name', 'podate', 'date_needed', 'refno', 'total_amount', 'status', 'po_status',  'payment_status'])
            ->where('status', 'M')
            ->whereBetween('date_needed', [$from, $to])
            ->pluck('id'); 
        
        $item = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(qty) AS finalqty, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->get();
        return $item;
    }

    public function latestAllocationProduceDuePO()
    {
        // 7 Days
        $from = Carbon::today()->toDateString();
        $to = Carbon::today()->addDays(7)->toDateString();

        $orderAllocation = OrderAllocation::where(['member_id' => Auth::id()])
                                        ->distinct()
                                        ->pluck('po_main_id');

        $main = PoMain::whereIn('id', $orderAllocation)
                ->select(['id', 'code', 'customer_name', 'podate', 'date_needed', 'refno', 'total_amount', 'status', 'po_status', 'payment_status'])
                ->where('status', 'M')
                ->where('type', 0)
                ->whereBetween('date_needed', [$from, $to])
                ->pluck('id'); 

        $item = OrderAllocation::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(final_qty) AS finalqty, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->where('status', 'A')
                    ->get();

        return $item;

    }

    public function latestOrder()
    {
        return Order::where(['seller_id' => Auth::id()])
            ->select(['id', 'status', 'order_transaction_id', 'shippingOption', 'handling_status', 'totalCost'])
            ->with(array('orderTransaction' => function($query) {
                $query->select(['id', 'refNo', 'fullname', 'payment_date'])
                    ->orderBy('payment_date', 'DESC');
            }))
            ->where('status', '!=' ,'P')
            ->limit(10)
            ->get();
    }

    public function latestPreOrder()
    {
        return PreOrder::where(['seller_id' => Auth::id()])
            ->select(['id', 'status', 'pre_order_transaction_id', 'shippingOption', 'handling_status', 'totalCost'])
            ->with(array('preOrderTransaction' => function($query) {
                $query->select(['id', 'tranRefNo', 'refNo', 'fullname', 'payment_date', 'status', 'payment_status'])
                    ->orderBy('created_at', 'DESC');
            }))
            ->limit(10)
            ->get();
    }

    public function latestProduct()
    {
        $this->authorize('isAdminCoopMember'); 
        
        return Product::where(['seller_id' => Auth::id()])
                ->with(['category'])
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();
    }

    public function figures()
    {
        $totalpo = PoMain::where(['owner_id' => Auth::id()])->count();
        $completedpo = PoMain::where(['owner_id' => Auth::id()])
                ->where('status', 'O')
                ->count();
        $pendingpo = PoMain::where(['owner_id' => Auth::id()])
                ->where('status', 'P')
                ->count();
        // $preorder = PreOrder::where(['seller_id' => Auth::id()])
        //         ->where('status', 'C')
        //         ->sum('totalCost');
        $product = Product::where(['seller_id' => Auth::id()])->count();
        // $inventory = Product::where(['seller_id' => Auth::id()])->sum('stock');

        return ['totalpo' => $totalpo, 'completedpo' => $completedpo, 'pendingpo' => $pendingpo, 'product' => $product];
    }
}
