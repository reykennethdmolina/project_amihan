<?php

namespace App\Http\Controllers\Report;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\PORepository;
use App\Models\PoMain;
use App\Models\PoItem;
use App\Models\PaymentMode;
use App\Models\OrderAllocation;
use App\Services\PermissionService;

use DB;
use PDF;
use Excel;
use Carbon\Carbon;

class POController extends Controller
{

    protected $repository;

    public function __construct(PORepository $repository)
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
        
        $this->authorize('isAdminCoopMember');

        $paymentmode = PaymentMode::orderBy('code')->get();
        $date['from'] = Carbon::today()->subDays(30)->format('Y-m-d');
        $date['to'] = Carbon::today()->format('Y-m-d');

        return view('report.po', compact('paymentmode', 'date'));
    }

    public function generate(Request $request)
    {
        $this->authorize('isAdminCoopMember'); 

        if ($request->input('report') == 1) {
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');
            $list = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
                    ->selectRaw('SUM(qty) AS totalqty, SUM(amount) AS totalamount, sku, name, variety, unit, product_id, status')
                    ->whereIN('po_main_id', $main)
                    ->orderBy('name', 'ASC')
                    ->orderBy('variety', 'ASC')
                    ->get();

            $html = view('report.po.orders-summary', ['list' => $list])->render();
        } elseif ($request->input('report') == 2) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->orderBy('code', 'ASC')
                    ->get();
           
            $html = view('report.po.po-listing', ['list' => $list])->render();
        } elseif ($request->input('report') == 3) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['items'])
                    ->orderBy('code', 'ASC')
                    ->get();
            $html = view('report.po.po-listing-with-item', ['list' => $list])->render();
        } elseif ($request->input('report') == 4) {
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');

            $pomain_ids = trim($main, '[]');            

            $list = DB::select( 
                        DB::raw("SELECT p.business_name, oa.product_id, oa.sku, oa.name, oa.variety, oa.price, concat(ifnull(oa.name, ''),' ', ifnull(oa.variety, '')) as fullname,
                            SUM(oa.amount) AS amount, oa.unit, SUM(oa.assign_qty) AS assign_qty,
                            SUM(oa.final_qty) AS final_qty, SUM(oa.actual_qty) AS actual_qty, oa.date_needed, if(oa.status = 'A', 'Accepted', 'Pending') AS `status`
                            FROM order_allocations AS oa
                            LEFT OUTER JOIN profiles AS p ON p.user_id = oa.member_id
                            WHERE oa.po_main_id IN ($pomain_ids)
                            GROUP BY oa.member_id, oa.product_id, oa.date_needed
                            ORDER BY p.business_name, oa.name, oa.variety, oa.date_needed") 
                    );

            $data = [];
            foreach ($list as $list) {
                $data[$list->business_name][$list->fullname][] = $list;
            }

            $html = view('report.po.po-consolidated-allocation', ['data' => $data])->render();
        } else {
            $list = [];
            $html = '';
        }
        

        return response()->json( array('success' => true, 'html'=>$html) );
    }

    public function pdf(Request $request)
    {
        $this->authorize('isAdminCoopMember'); 

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
            $pdf = PDF::loadView('report.po.pdf-orders-summary', compact('list', 'date'));
        } elseif ($request->input('report') == 2) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->orderBy('customer_name', 'ASC')
                    ->get();
           
            $pdf = PDF::loadView('report.po.pdf-po-listing', compact('list', 'date'));
        } elseif ($request->input('report') == 3) {
            $list = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['items'])
                    ->orderBy('code', 'ASC')
                    ->get();
            
            $pdf = PDF::loadView('report.po.pdf-po-listing-with-item', compact('list', 'date'));
        } elseif ($request->input('report') == 4) {
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');

            $pomain_ids = trim($main, '[]');            

            $list = DB::select( 
                        DB::raw("SELECT p.business_name, oa.product_id, oa.sku, oa.name, oa.variety, oa.price, concat(ifnull(oa.name, ''),' ', ifnull(oa.variety, '')) as fullname,
                            SUM(oa.amount) AS amount, oa.unit, SUM(oa.assign_qty) AS assign_qty,
                            SUM(oa.final_qty) AS final_qty, SUM(oa.actual_qty) AS actual_qty, oa.date_needed, if(oa.status = 'A', 'Accepted', 'Pending') AS `status`
                            FROM order_allocations AS oa
                            LEFT OUTER JOIN profiles AS p ON p.user_id = oa.member_id
                            WHERE oa.po_main_id IN ($pomain_ids)
                            GROUP BY oa.member_id, oa.product_id, oa.date_needed
                            ORDER BY p.business_name, oa.name, oa.variety, oa.date_needed") 
                    );

            $data = [];
            foreach ($list as $list) {
                $data[$list->business_name][$list->fullname][] = $list;
            }

            $pdf = PDF::loadView('report.po.pdf-po-consolidated-allocation', compact('data', 'date'));

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
            $title = "Orders Summary";

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
            $title = "PO Listing";
            $data = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->orderBy('customer_name', 'ASC')
                    ->get();

            $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Customer', 'Ref No', 'Qty', 'Amount'];

            foreach ($data as $row) {
                $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->customer_name, $row->refno, $row->total_qty, $row->total_amount];
            }

        } else if ($request->input('report') == 3) {
            $title = "PO Listing With Item";
            $data = $this->repository->getOrderSummary(Auth::id(), $request)
                    ->with(['items'])
                    ->orderBy('code', 'ASC')
                    ->get();

            $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Customer', 'Ref No', 'Qty', 'Amount'];

            foreach ($data as $row) {
                $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->customer_name, $row->mobile, $row->pickUpLocation, $row->delivery_details, $row->refno, $row->total_qty, $row->total_amount];
                $counter = 1;
                foreach ($row->items as $item) {
                    $dataArray[] = [$counter, $item->sku, $item->name, $item->variety, $item->qty, $item->price, $item->amount];
                    $counter += 1;
                }
            }
        } else if ($request->input('report') == 4) {
            $title = "PO Consolidated Allocation";
            $main = $this->repository->getOrderSummary(Auth::id(), $request)->pluck('id');

            $pomain_ids = trim($main, '[]');            

            $list = DB::select( 
                        DB::raw("SELECT p.business_name, oa.product_id, oa.sku, oa.name, oa.variety, oa.price, concat('   ', ifnull(oa.name, ''),' ', ifnull(oa.variety, '')) as fullname,
                            SUM(oa.amount) AS amount, oa.unit, SUM(oa.assign_qty) AS assign_qty,
                            SUM(oa.final_qty) AS final_qty, SUM(oa.actual_qty) AS actual_qty, oa.date_needed, if(oa.status = 'A', 'Accepted', 'Pending') AS `status`
                            FROM order_allocations AS oa
                            LEFT OUTER JOIN profiles AS p ON p.user_id = oa.member_id
                            WHERE oa.po_main_id IN ($pomain_ids)
                            GROUP BY oa.member_id, oa.product_id, oa.date_needed
                            ORDER BY p.business_name, oa.name, oa.variety, oa.date_needed") 
                    );

            $data = [];
            foreach ($list as $list) {
                $data[$list->business_name][$list->fullname][] = $list;
            }

            $dataArray[] = ['Products', 'Date Needed', 'Assign Qty', 'Accepted Qty', 'Unit', 'Price', 'Amount', 'Status'];
            
            $ftotalaqty = 0; $ftotalfqty = 0; $ftotalamount = 0;
            foreach ($data as $fulfiller => $item) {
                $dataArray[] = [$fulfiller];
                foreach ($item as $product => $lists) {
                    $productname = $product;
                    $totalaqty = 0; $totalfqty = 0; $totalamount = 0;
                    foreach ($lists as $key => $list) {
                        if (abs($key) != 0 ) {
                            $productname = '';
                        }
                        $totalaqty += $list->assign_qty; $totalfqty += $list->final_qty; $totalamount += $list->amount;
                        $ftotalaqty += $list->assign_qty; $ftotalfqty += $list->final_qty; $ftotalamount += $list->amount;

                        $dataArray[] = [$productname, $list->date_needed, $list->assign_qty, $list->final_qty, $list->unit, $list->price, $list->amount, $list->status];        
                    }

                    if (count($lists) > 1 ) {
                        $dataArray[] = ['', 'Sub-total', $totalaqty, $totalfqty, '', '', $totalamount, ''];        
                    }
                }   
            }
            $dataArray[] = ['', 'Grand-total', $ftotalaqty, $ftotalfqty, '', '', $ftotalamount, ''];        

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

        $pdf = PDF::loadView('pdf.po-form', compact('list', 'date', 'data'));
        return $pdf->stream();
    }

    public function singlepdfallocation($id)
    {
        $date['now'] = Carbon::now();

        $data = PoMain::where('id', $id)->with(['owner', 'items', 'allocateTo.fulfiller'])->first();
        $data['cityname'] = explode(";", $data->city);
        $data['provincename'] = explode(";", $data->province);

        $data['ownercityname'] = explode(";", $data->owner->city);
        $data['ownerprovincename'] = explode(";", $data->owner->province);

        $list = [];

        $pdf = PDF::loadView('pdf.po-form-allocation', compact('list', 'date', 'data'));
        return $pdf->stream();
    }

}
