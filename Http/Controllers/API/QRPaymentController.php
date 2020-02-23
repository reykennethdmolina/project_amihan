<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use App\Models\PoMain;
use Carbon\Carbon;

class QRPaymentController extends Controller
{
    /**
     * Signup 
     *
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        /** Mobile is registered ? Login : Register */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/signup/otp";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'phone' => $request->POST('mobile'),
                    'otp' => $request->POST('otp'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        return json_encode(['status' => $status, 'result' => $body]);
    }
    /**
     * Signup OTP
     *
     * @return \Illuminate\Http\Response
     */
    public function signupOTP(Request $request)
    {
        /** Mobile is registered ? Login : Register */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/signup";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'phone' => $request->POST('mobile'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        return json_encode(['status' => $status, 'result' => $body]);
    }


    /**
     * Login OTP
     *
     * @return \Illuminate\Http\Response
     */
    public function loginOTP(Request $request)
    {
        /** Mobile is registered ? Login : Register */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/login";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'id' => $request->POST('mobile'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();


        $result = $body;


        return json_encode(['status' => $status, 'result' => $result]);

    }

    /**
     * Login 
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        /** Mobile is registered ? Login : Register */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/login/otp";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'id' => $request->POST('mobile'),
                    'otp' => $request->POST('otp'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();


        $result = $body;


        return json_encode(['status' => $status, 'result' => $result]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkUserBalance(Request $request)
    {
        /** Check User Balance */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/checkBalance";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'key' => $request->POST('key'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();


        $result = $body;


        return json_encode(['status' => $status, 'result' => $result]);
    }
    

    public function paymentQRPay(Request $request)
    {
        /** Payment */
        try {
            $client = new Client();
            $url = "https://us-central1-qrpay-4610f.cloudfunctions.net/public/payment";
            $response = $client->post($url, [
                'headers' => ['Content-type' => 'application/json'],
                'body' => json_encode([
                    'apiKey' => $request->POST('apiKey'),
                    'key' => $request->POST('key'),
                    'amount' => floatval($request->POST('amount')),
                    'itemName' => 'PO#'.$request->POST('itemName'),
                    'transactionID' => $request->POST('transactionID'),
                ])
            ]);
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }
        $status = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        $manage = json_decode($body);

        if ($status == 200) {
            $trans = PoMain::where('id', $request->POST('transactionID'))
                ->where('payment_status', 'P')
                ->firstOrFail();
                $datetime = Carbon::now();
                $trans->update(['payment_date' => $datetime, 'payment_status' => 'S', 'payment_refno' => $manage->transactionID]);                
        }


        $result = $body;


        return json_encode(['status' => $status, 'result' => $result]);    
    }
}
