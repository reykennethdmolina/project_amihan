<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoMain;
use App\Models\User;

use Carbon\Carbon;
use Dragonpay;
use Mail;

class DragonpayController extends Controller
{
    /* Postback URL */
    public function handle(Dragonpay $client, Request $request)
    {
        $params = [
            'transactionId'   => $request['txnid'],
            'referenceNumber' => $request['refno'],
            'status'          => $request['status'],
            'message'         => $request['message'],
            'digest'          => $request['digest']
        ];

        $transaction = Dragonpay::transactionIsValid($params);

        if ($transaction) {
            
            $trans = PoMain::where('id', $params['transactionId'])
                ->where('payment_status', 'P')
                ->firstOrFail();

            if ($params['status'] == 'S') {
                $datetime = Carbon::now();
                $trans->update(['payment_date' => $datetime, 'payment_status' => 'S', 'payment_refno' => $params['referenceNumber']]);                
            } else {
                $trans->update(['payment_refno' => $params['referenceNumber']]);    
            }

        }
        return $transaction ? 'result=OK' : 'result=FAIL_DIGEST_MISMATCH';
    }

    /** Sample https://www.agronegosyo.com/dragonpay/return?txnid=6106&refno=RVFVLTP4&status=S&message=%5b000%5d+BOG+Reference+No%3a+20181226190944+%23RVFVLTP4&digest=3aaecc596fe92fefb25e379503ae52e81f616b28 */
    /* Return URL */
    public function return(Request $request)
    {
        $status = $request['status'];

        if ($status == 'S') {
            return view('errors.payment-success');
        } elseif ($status == 'F') {
            return view('errors.payment-failed');
        } elseif ($status == 'P') {
            return view('errors.payment-pending');
        } else {
            return view('errors.payment-error');
        }
    }

    public function test() {
        return view('errors.testform');
    }
}
