<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PreOrder;

use Auth;
use PDF;
use DNS1D;

class WaybillController extends Controller
{
    public function order(Request $request, $id, $type = 'QRS')
    {
        $order = Order::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->with('orderTransaction', 'seller.profile', 'orderItems')
            ->first();

        if ($order) {
            if ($type === 'QRS') {
                return $this->QRSWaybill($order, 'order');
            }
        }
    }

    public function preorder(Request $request, $id, $type = 'QRS')
    {
        $order = PreOrder::where(['seller_id' => Auth::id()])
            ->where('id', $id)
            ->where('status', '!=' ,'P')
            ->with('preOrderTransaction', 'seller.profile', 'preOrderItems')
            ->first();

        if ($order) {
            if ($type === 'QRS') {
                return $this->QRSWaybill($order, 'preorder');
            }
        }
    }

    public function QRSWaybill($data, $type) {
        if ($type === 'order') {
            $pdf = PDF::loadView('waybill.qrs', compact('data'));
        } else if ($type === 'preorder') {
            $pdf = PDF::loadView('waybill.qrspre', compact('data'));
        }
        
        return $pdf->stream();
    }
}
