<?php

namespace App\Http\Controllers\Report;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\MyPORepository;
use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\Profile;
use App\Services\PermissionService;

use PDF;
use Excel;
use Carbon\Carbon;

class MyPOController extends Controller
{

    protected $repository;

    public function __construct(MyPORepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }
        
        #$this->authorize('isAdminCoopMember');

        /** Get all merchant in transaction */
        $merchant_id = PoMain::where('customer_id', Auth::id())
            ->groupBy('owner_id')->pluck('owner_id');
    
        $merchants = Profile::whereIn('user_id', $merchant_id)
            ->select('id', 'business_name')
            ->orderBy('business_name')
            ->get();

            $date['from'] = Carbon::today()->subDays(30)->format('Y-m-d');
            $date['to'] = Carbon::today()->format('Y-m-d');

        return view('report.mypo', compact('merchants', 'date'));
    }

    public function generate(Request $request)
    {
        #$this->authorize('isAdminCoopMember'); 

        if ($request->input('report') == 1) {
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');
            $list = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(qty) AS totalqty, SUM(amount) AS totalamount, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->orderBy('name', 'ASC')
                    ->orderBy('variety', 'ASC')
                    ->get();

            $html = view('report.mypo.orders-summary', ['list' => $list])->render();
        } elseif ($request->input('report') == 2) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();
           
            $html = view('report.mypo.po-listing', ['list' => $list])->render();
        } elseif ($request->input('report') == 3) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();

            $html = view('report.mypo.po-listing-with-item', ['list' => $list])->render();
        } else {
            $list = [];
            $html = '';
        }
        

        return response()->json( array('success' => true, 'html'=>$html) );
    }

    public function pdf(Request $request)
    {
        #$this->authorize('isAdminCoopMember'); 

        $date['from'] = $request->input('podatefrom');
        $date['to'] = $request->input('podateto');

        if ($request->input('report') == 1) {
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');
            $list = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(qty) AS totalqty, SUM(amount) AS totalamount, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->orderBy('name', 'ASC')
                    ->orderBy('variety', 'ASC')
                    ->get();

            $pdf = PDF::loadView('report.mypo.pdf-orders-summary', compact('list', 'date'));
        } elseif ($request->input('report') == 2) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();
           
            $pdf = PDF::loadView('report.mypo.pdf-po-listing', compact('list', 'date'));
        } elseif ($request->input('report') == 3) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();
            
            $pdf = PDF::loadView('report.mypo.pdf-po-listing-with-item', compact('list', 'date'));
        } else {
            $list = [];
            $html = '';
        }


        return $pdf->stream();
    }

    public function excel(Request $request)
    {
        $this->authorize('isAdminCoopMember'); 

        $date['from'] = $request->input('podatefrom');
        $date['to'] = $request->input('podateto');

        // Initialize the array which will be passed into the Excel
        // generator.
        $dataArray = [];
        $title = "";

        if ($request->input('report') == 1) {
            $title = "My Order Summary";

            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');
            $data = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(qty) AS totalqty, SUM(amount) AS totalamount, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->orderBy('name', 'ASC')
                    ->orderBy('variety', 'ASC')
                    ->get();

            $dataArray[] = ['SKU', 'Product', 'Variety', 'Quantity', 'Unit', 'Amount'
            ];

            foreach ($data as $row) {
                $dataArray[] = [$row->sku, $row->name, $row->variety, $row->totalqty, $row->unit, $row->totalamount];
            }

        } else if ($request->input('report') == 2) {
            $title = "My PO Listing";
            $data = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();

            $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Merchant', 'Ref No', 'Qty', 'Amount'];

            foreach ($data as $row) {
                $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->owner->business_name, $row->refno, $row->total_qty, $row->total_amount];
            }

        } else if ($request->input('report') == 3) {
            $title = "My PO Listing With Item";
            $data = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['owner'])
                    ->orderBy('code', 'ASC')
                    ->get();

            $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Merchant', 'Ref No', 'Qty', 'Amount'];

            foreach ($data as $row) {
                $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->owner->business_name, $row->refno, $row->total_qty, $row->total_amount];
                $counter = 1;
                foreach ($row->items as $item) {
                    $dataArray[] = [$counter ,$item->sku, $item->name, $item->variety,$item->qty, $item->price, $item->amount];
                    $counter += 1;
                }
            }
        }
        
        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($title, $dataArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setDescription($title.' excel file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($dataArray) {
                $sheet->fromArray($dataArray, null, 'A1', false, false);
            });

        })->download('xlsx');

        return true;
    }

    public function singlepdf($id)
    {
        $date['now'] = Carbon::now();

        $data = PoMain::where('id', $id)->with(['owner', 'items'])->first();
        $data['cityname'] = explode(";", $data->city);
        $data['provincename'] = explode(";", $data->province);

        $data['ownercityname'] = explode(";", $data->owner->city);
        $data['ownerprovincename'] = explode(";", $data->owner->province);

        $list = [];

        $pdf = PDF::loadView('pdf.mypo-form', compact('list', 'date', 'data'));
        return $pdf->stream();
    }

}
