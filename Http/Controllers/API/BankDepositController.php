<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PoUpload;
use App\Models\PoMain;

use Carbon\Carbon;
use Image;
use Auth;

class BankDepositController extends Controller
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
    public function upload(Request $request)
    {

        // if ($request->photo != $currentPhoto) {
        $photo = auth('api')->user()->id.''.time().'.'.explode('/', explode(':', 
                substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
           
        $img = Image::make($request->photo)->fit(480);
        $img->save(public_path('img/uploadfile/').$photo);
        $datetime = Carbon::now();
        
        PoUpload::insert(['type' => 'DEPOSITSLIP', 'filename' => $photo, 'pomain_id' => $request->id, 'uploadby_id' => Auth::id(), 'upload_date' => $datetime]);

        PoMain::where(['id' => $request->id])->update(['depositslip' => $photo, 'payment_status' => 'V']);
        
        $data = PoUpload::where(['pomain_id' => $request->id])->orderBy('upload_date', 'DESC')->get();

        return $data;
    }

    public function action(Request $request)
    {
        $datetime = Carbon::now();
        PoMain::where(['id' => $request->id])->update(['payment_date' => $datetime, 'payment_status' => $request->status, 'payment_refno' => $request->refno]);    
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
        //
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
}
