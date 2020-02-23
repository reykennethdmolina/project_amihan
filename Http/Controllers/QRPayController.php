<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use QrCode;

class QRPayController extends Controller
{
    public function index() {
        return QrCode::size(500)->generate('https://us-central1-qrpay-4610f.cloudfunctions.net/public/signup');

        //return QrCode::size(500)->SMS('555-555-5555');
    
        //return view('qrCode');
    }
}
