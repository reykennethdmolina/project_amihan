<?php

namespace App\Repositories;

use DB;
use App\Models\PoMain;

class PORepository extends Repository
{
    public function getOrderSummary($user, $request)
    {
        $builder = PoMain::where('owner_id', $user)
            ->orderBy('podate', 'DESC');

        return $this->filter($builder, $request);
    }

    
    public function filter($query, $request)
    {
        if ($request->has('podatefrom')) {
            $query->whereBetween('podate', [$request->input('podatefrom'), $request->input('podateto')]);
        }

        if ($request->has('dateneeded')) {
            if ($request->input('dateneeded') != '') {
                $query->where('date_needed', $request->input('dateneeded'));
            }
        }

        if ($request->has('pickuplocation')) {
            if ($request->input('pickuplocation') != '') {
                $query->where('pickUpLocation', 'LIKE', '%'.$request->input('pickuplocation').'%');
            }
        }

        if ($request->has('customer')) {
            if ($request->input('customer') != '') {
                $query->where('customer_name', 'LIKE', '%'.$request->input('customer').'%');
            }
        }

        if ($request->has('refno')) {
            if ($request->input('refno') != '') {
                $query->where('refno', 'LIKE', '%'.$request->input('refno').'%');
            }
        }

        if ($request->has('status')) {
            if ($request->get('status') != 'all') {
                $query->where('status', strtoupper($request->get('status')));
            }
        }

        if ($request->has('paymentMode')) {
            if ($request->get('paymentMode') != 'all') {
                $query->where('paymentMode', strtoupper($request->get('paymentMode')));
            }
        }

        if ($request->has('payment_status')) {
            if ($request->get('payment_status') != 'all') {
                $query->where('payment_status', strtoupper($request->get('payment_status')));
            }
        }

        return $query;
    }
}
