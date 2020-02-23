<?php

namespace App\Repositories;

use DB;
use App\Models\PoMain;

class MyPORepository extends Repository
{
    public function getOrderSummary($user, $request)
    {
        $builder = PoMain::where('customer_id', $user)
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

        if ($request->has('merchant')) {
            if ($request->input('merchant') != '') {
                $query->where('owner_id', $request->input('merchant'));
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

        return $query;
    }
}
