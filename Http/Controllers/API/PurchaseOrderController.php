<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\OrderAllocation;
use App\Mail\POConfirmationEmail;
use App\Mail\MerchantPOListEmail;

use Carbon\Carbon;
use Gate;
use Mail;
use Dragonpay;

class PurchaseOrderController extends Controller
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
        $this->authorize('isAdminCoopMember'); 
        
        $po = PoMain::where(['owner_id' => Auth::id(), 'po_status' => 'M']);

        /** Handle Filter in Pagination */
        $status = \Request::get('status');
        if ($status != '') {
            $po->where('status', strtoupper($status));
        }

        return $po->orderBy('created_at', 'DESC')
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
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'podate' => 'required',
            'date_needed' => 'required',
            'customer_id' => 'required',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11',
        ]);

        $podate = str_replace('T', ' ',substr($request['podate'], 0, -5));
        $date_needed = str_replace('T', ' ',substr($request['date_needed'], 0, -5));

        $code = $this->pocode(Auth::id());

        $po = PoMain::create([
            'owner_id' => Auth::id(),
            'code' => $code,
            'podate' => $podate,
            'refno' => $request['refno'],
            'date_needed' => $date_needed,
            'customer_id' => $request['customer_id'],
            'customer_name' => $request['customer_name'],
            'contact_person' => $request['contact_person'],
            'total_amount' => $request['total_amount'],
            'total_qty' => $request['total_qty'],
            'remarks' => $request['remarks'],
            'mobile' => $request['mobile'],            
            'landline' => $request['landline'],    
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],    
            'status' => 'P',
            'po_status' => 'M',
            'paymentMode' => 'COD',
            'payment_status' => 'P',         
        ]);

        $items = $request['items'];

        foreach ($items as $item) {
            PoItem::create([
                'po_main_id' => $po->id,
                'product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'variety' => $item['variety'],
                'slug' => $item['name'].' '.$item['variety'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $item['qty'] * $item['price'],
                'unit' => $item['unit'],
            ]);
        }

        return $po;
    }
    
    private function pocode($id) {
        $count = PoMain::where('owner_id', $id)->count();
        return Auth::id().''.str_pad($count + 1, 6 ,"0", STR_PAD_LEFT);
    }

    private function pocodebuyer($id) {
        $count = PoMain::where('customer_id', $id)->count();
        return Auth::id().''.str_pad($count + 1, 6 ,"0", STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Pomain::where(['id' => $id])
                ->where(function ($query) {
                    $query->where('owner_id', Auth::id())
                        ->orWhere('customer_id', Auth::id());
                })
                ->with(['items'])->first();
        //return Pomain::where(['id' => $id, 'owner_id' => Auth::id()])->with(['items'])->first();
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
        $this->authorize('isAdminCoopMember'); 

        $this->validate($request, [
            'podate' => 'required',
            'date_needed' => 'required',
            'customer_id' => 'required',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11',
        ]);
        
        $po = PoMain::findOrFail($id);

        if ($po->podate != $request['podate']) {
            $podate = str_replace('T', ' ',substr($request['podate'], 0, -5));
        } else {
            $podate = $po->podate;
        }

        if ($po->date_needed != $request['date_needed']) {
            $date_needed = str_replace('T', ' ',substr($request['date_needed'], 0, -5));
        } else {
            $date_needed = $po->date_needed;
        }

        $po->update([
            'podate' => $podate,
            'refno' => $request['refno'],
            'date_needed' => $date_needed,
            'customer_id' => $request['customer_id'],
            'customer_name' => $request['customer_name'],
            'contact_person' => $request['contact_person'],
            'total_amount' => $request['total_amount'],
            'total_qty' => $request['total_qty'],
            'remarks' => $request['remarks'],
            'mobile' => $request['mobile'],            
            'landline' => $request['landline'],    
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],    
            'status' => 'P'               
        ]);

        $items = $request['items'];
        $ids = [];
        foreach ($items as $item) {
            $data = PoItem::updateOrCreate(['product_id' => $item['product_id'], 'po_main_id' => $po->id],
                ['product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'variety' => $item['variety'],
                'slug' => $item['name'].' '.$item['variety'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $item['qty'] * $item['price'],
                'unit' => $item['unit'],
            ]);
            array_push($ids, $data['id']);
        }

        $poitem = PoItem::where('po_main_id', $po->id)->whereNotIn('id', $ids)->forcedelete();

        return $po;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::findOrFail($id);

        $po->update([
            'status' => 'C',
            'updated_by' => Auth::id(),
        ]);

        return ['message' => 'PO deleted'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function declined($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::findOrFail($id);

        $po->update([
            'status' => 'D',
            'updated_by' => Auth::id(),
        ]);

        return ['message' => 'PO declined'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $status = \Request::get('status');
            $po = PoMain::where(function($query) use ($search){
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('customer_name', 'LIKE', "%$search%")
                    ->orWhere('refno', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()]);


            if ($status != '') {
                $po->where('status', strtoupper($status));
            }

            return $po->orderBy('created_at', 'DESC')
                    ->paginate(15);
        } else {
            $status = \Request::get('status');
            $po = PoMain::where(['owner_id' => Auth::id()]);

            if ($status != '') {
                $po->where('status', strtoupper($status));
            }

            return $po->orderBy('created_at', 'DESC')
                    ->paginate(15);
        }

        return $po;
    }

    public function multiAllocate(Request $request)
    {
        $po = $request->input('po');

        $list = PoMain::whereIn('id', $po)
            ->where('status', 'P')
            ->get();

        foreach ($list as $item) {
            if (Auth::user()->type == 'member') {
                $this->processPO($item->id);
            } else {
                $this->moveToAllocation($item->id);
            }
        }

        return ['message' => 'Multi allocation'];
    }

    public function moveToAllocation($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::findOrFail($id);

        $po->update([
            'status' => 'M'
        ]);

        return ['message' => 'PO move to allocation'];
    }

    public function processPO($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $po = PoMain::findOrFail($id);

        if ($po) {

            $items = PoItem::where('po_main_id', $id)->get();

            foreach ($items as $item) {

                OrderAllocation::create([
                    'po_main_id' => $id, 
                    'po_item_id' => $item['id'], 
                    'product_id' => $item['product_id'], 
                    'member_id' => Auth::id(),
                    'sku' => $item['sku'], 
                    'name' => $item['name'], 
                    'variety' => $item['variety'], 
                    'slug' => $item['name'].' '.$item['variety'],
                    'assign_qty' => $item['qty'], 
                    'final_qty' => $item['qty'], 
                    'price' => $item['price'], 
                    'amount' => $item['amount'],
                    'unit' => $item['unit'], 
                    'updated_by' => Auth::id(),
                    'status' => 'A',
                    'type' => 'P',
                ]);

                $item->update([
                    'assign_qty' => $item['qty'],
                    'final_qty' => $item['qty'],
                ]);
            }

            $po->update([
                'status' => 'M',
                'total_assign_qty' => $po['total_qty'],
                'total_final_qty' => $po['total_qty'],
            ]);
        }

        return ['message' => 'Process PO to allocation'];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function po(Request $request)
    {
        $user = auth('api')->user();
        $orderLists = $request->orderList;

        $info = $request->info;

        $dateNeeded = str_replace('T', ' ',substr($orderLists[0]['dateNeeded'], 0, -5));
        //$dateNeeded = $orderLists[0]['dateNeeded'];

        $seller = $orderLists[0]['seller_id'];
        $code = $this->pocode($seller);

        /** Initiate order transaction */
        $transaction = PoMain::create([
            'owner_id' => $seller,
            'code' => $code,
            'podate' => Carbon::now(),
            'refno' => 'ONLINE',
            'date_needed' => $dateNeeded,
            'customer_id' => $user->id,
            'customer_name' => $info['fullname'],
            'contact_person' => $info['fullname'],
            'mobile' => $info['mobile'],            
            'hoblst' => $info['hoblst'],
            'barangay' => $info['barangay'],            
            'city' => $info['city'],            
            'province' => $info['province'],            
            'postal_code' => $info['postal_code'],            
            'landmark' => $info['landmark'],    
            'status' => 'P',               
            'type' => '1',
            'pickUpLocation' => $orderLists[0]['pickUpLocation'],               
            'paymentMode' => $orderLists[0]['paymentMode'],    
            'delivery_details' => $orderLists[0]['delivery_details'],    
            'payment_status' => 'P',           
        ]);

        $total_qty = 0;
        $total_amount = 0;
        foreach ($orderLists as $item) {
            $total_qty += $item['qty'];
            $total_amount += $item['qty'] * $item['price'];
            PoItem::create([
                'po_main_id' => $transaction->id,
                'product_id' => $item['id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'variety' => $item['variety'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $item['qty'] * $item['price'],
                'unit' => $item['unit'],
            ]);
        }

        $transaction->update([
            'total_qty' => $total_qty,
            'total_amount' => $total_amount
        ]);

        $this->sendEmail($transaction->id);

        $url = '';
        if ($transaction->paymentMode == 'DRAGONPAY') {
            $url = $this->createDragonpayLink($transaction);
            $totalcheckamount =  $transaction->total_amount + 25;
            $transaction->update([
                'purchased_date' => Carbon::now(),
                'paid_amount' => $totalcheckamount
            ]);
        } else if ($transaction->paymentMode == 'QRPAY') {
            $url = Crypt::encryptString($transaction->id);
            $totalcheckamount =  0;
            $transaction->update([
                'purchased_date' => Carbon::now(),
                'paid_amount' => $totalcheckamount
            ]);
        } else if ($transaction->paymentMode == 'BANKDEPO') {
            $url = Crypt::encryptString($transaction->id);
            $totalcheckamount =  0;
            $transaction->update([
                'purchased_date' => Carbon::now(),
                'paid_amount' => $totalcheckamount
            ]);
        }

        return ['status' => 'success', 'payopt' => $transaction->paymentMode, 'url' => $url];
    }

    public function sendEmail($transId) 
    {
        $transaction = PoMain::where('id', $transId)
            ->where('type', 1)
            ->where('status', 'P')
            ->firstOrFail();
    
        /* Get Items */
        $items = PoItem::where('po_main_id', $transId)->get();

        /* Email Buyer Order Transaction */
        $buyer = User::where('id', $transaction->customer_id)->first();
        Mail::to($buyer->email)->send(new POConfirmationEmail($buyer, $items));

        /* Email Merchant Order Transaction */
        $merchant = User::where('id', $transaction->owner_id)->with(['profile'])->first();
        Mail::to($merchant->email)
            ->cc($merchant->profile->business_email)
            ->send(new MerchantPOListEmail($merchant, $items));   

        return $transaction;
    }

    public function createDragonpayLink($transaction)
    {
        $description = "Payment for agronegosyo PO#".$transaction->code;
        $totalcheckamount =  $transaction->total_amount + 25;
        $params = [
            'transactionId' => $transaction->id,
            'amount'        => number_format($totalcheckamount, 2, '.', ''),
            'currency'      => 'PHP',
            'description'   => $description,
            'email'         => auth('api')->user()->email,
        ];

        #$message; 0u0p7fJcVzEGFVh
        #echo sha1('AGRONEGOSYO:6104:1169.7:PHP:Payment for agronegosyo:john@gmail.com:0u0p7fJcVzEGFVh');

        $url = Dragonpay::getUrl($params);

        if ($url) {
            /* Update purchased date */
            $datetime = Carbon::now();
            $transaction->update(['purchased_date' => $datetime]);
        }

        return $url;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function buyerPO(Request $request)
    {
        $this->validate($request, [
            'podate' => 'required',
            'date_needed' => 'required',
            'customer_name' => 'required',
            'owner_id' => 'required',
            'paymentMode' => 'required',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11',
        ]);

        // $podate = str_replace('T', ' ',substr($request['podate'], 0, -5));
        // $date_needed = str_replace('T', ' ',substr($request['date_needed'], 0, -5));

        $code = $this->pocodebuyer(Auth::id());

        $po = PoMain::create([
            'owner_id' => $request['owner_id'],
            'code' => $code,
            'podate' => $request['podate'],
            'refno' => $request['refno'],
            'date_needed' => $request['date_needed'],
            'customer_id' => Auth::id(),
            'customer_name' => $request['customer_name'],
            'contact_person' => $request['contact_person'],
            'total_amount' => $request['total_amount'],
            'total_qty' => $request['total_qty'],
            'remarks' => $request['remarks'],
            'delivery_details' => $request['delivery_details'],
            'mobile' => $request['mobile'],            
            'tin' => $request['tin'],    
            'paymentMode' => $request['paymentMode'],    
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],    
            'status' => 'P',
            'po_status' => 'P',
            'payment_status' => 'P',         
            'type' => 1,         
        ]);

        $items = $request['items'];

        foreach ($items as $item) {
            PoItem::create([
                'po_main_id' => $po->id,
                'product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'variety' => $item['variety'],
                'slug' => $item['name'].' '.$item['variety'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $item['qty'] * $item['price'],
                'unit' => $item['unit'],
            ]);
        }

        return $po;

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateBuyerPO(Request $request, $id)
    {

        $this->validate($request, [
            'podate' => 'required',
            'date_needed' => 'required',
            'customer_name' => 'required',
            'owner_id' => 'required',
            'paymentMode' => 'required',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11',
        ]);
        
        $po = PoMain::findOrFail($id);

        
        $po->update([
            'podate' => $request['podate'],
            'refno' => $request['refno'],
            'date_needed' => $request['date_needed'],
            'customer_id' => Auth::id(),
            'customer_name' => $request['customer_name'],
            'contact_person' => $request['contact_person'],
            'total_amount' => $request['total_amount'],
            'total_qty' => $request['total_qty'],
            'remarks' => $request['remarks'],
            'delivery_details' => $request['delivery_details'],
            'mobile' => $request['mobile'],            
            'tin' => $request['tin'],    
            'paymentMode' => $request['paymentMode'],    
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],    
            'status' => 'P',            
        ]);

        $items = $request['items'];
        $ids = [];
        foreach ($items as $item) {
            $data = PoItem::updateOrCreate(['product_id' => $item['product_id'], 'po_main_id' => $po->id],
                ['product_id' => $item['product_id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'variety' => $item['variety'],
                'slug' => $item['name'].' '.$item['variety'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'amount' => $item['qty'] * $item['price'],
                'unit' => $item['unit'],
            ]);
            array_push($ids, $data['id']);
        }

        $poitem = PoItem::where('po_main_id', $po->id)->whereNotIn('id', $ids)->forcedelete();

        return $po;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function moveToOrder($id)
    {
        $po = PoMain::findOrFail($id);

        $po->update([
            'po_status' => 'M',
        ]);

        return ['message' => 'PO move to order'];
    }

    public function getQRPaymentInfo($crypt) {

        try {
            $decrypted = Crypt::decryptString($crypt);
        } catch (DecryptException $e) {
            return null;
        }

        $po = PoMain::where(['customer_id' => Auth::id(), 'id' => $decrypted, 'paymentMode' => 'QRPAY', 'payment_status' => 'P'])
                ->with('owner')->first();
        
        return $po;

    }

    public function getBankDepositInfo($crypt) {

        try {
            $decrypted = Crypt::decryptString($crypt);
        } catch (DecryptException $e) {
            return null;
        }

        $po = PoMain::where(['customer_id' => Auth::id(), 'id' => $decrypted, 'paymentMode' => 'BANKDEPO'])
                ->with('owner')->first();
        
        return $po;

    }

    public function getBankDepositData($id) {
        $po = PoMain::where(['owner_id' => Auth::id(), 'id' => $id, 'paymentMode' => 'BANKDEPO'])->first();
        
        return $po;    
    }
}
