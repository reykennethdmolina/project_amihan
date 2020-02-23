<?php

namespace App\Http\Controllers\Report;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Repositories\RegistryRepository;

use PDF;
use Excel;
use Carbon\Carbon;

class RegistryController extends Controller
{
    protected $repository;

    public function __construct(RegistryRepository $repository)
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

        $date['from'] = '2017-01-01';
        $date['to'] = Carbon::today()->format('Y-m-d');
        
        return view('report.registry', compact('date'));
    }

    public function generate(Request $request)
    {
        #$this->authorize('isAdminCoopMember'); 

        if ($request->input('report') == 1) {
            $list = $this->repository->getRegistry(Auth::id(), $request)->get();
            // $list = PoItem::groupBy('product_id', 'unit', 'sku', 'name', 'variety', 'status')
            //         ->selectRaw('SUM(qty) AS totalqty, SUM(amount) AS totalamount, sku, name, variety, unit, product_id, status')
            //         ->whereIN('po_main_id', $main)
            //         ->orderBy('name', 'ASC')
            //         ->orderBy('variety', 'ASC')
            //         ->get();

            $html = view('report.registry.registry-listing', ['list' => $list])->render();
        } 
        // elseif ($request->input('report') == 2) {
        //     $list = $this->repository->getOrderSummary(Auth::id(), $request)
        //             ->with(['owner'])
        //             ->orderBy('code', 'ASC')
        //             ->get();
           
        //     $html = view('report.mypo.po-listing', ['list' => $list])->render();
        // } elseif ($request->input('report') == 3) {
        //     $list = $this->repository->getOrderSummary(Auth::id(), $request)
        //             ->with(['owner'])
        //             ->orderBy('code', 'ASC')
        //             ->get();

        //     $html = view('report.mypo.po-listing-with-item', ['list' => $list])->render();
        // } 
        else {
            $list = [];
            $html = '';
        }
        

        return response()->json( array('success' => true, 'html'=>$html) );
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
            $title = "Registry Listing";

            $data = $this->repository->getRegistry(Auth::id(), $request)->get();

            $dataArray[] = ['Salutation', 'Firstname', 'Middlename', 'Lastname',
            'Suffix', 'House Number, Building and Street', 'Barangay', 'Province',
            'City', 'Mobile', 'Gender', 'Civil Status', 'Birth Date', 'Religion', 'Education',  
            'Livelihood', 'Farm Name', 'Farm Lot', 'Farming Since', 'Member Organization'
            ];

            foreach ($data as $row) {
                $dataArray[] = [$row->salutation, $row->firstname, $row->middlename, $row->lastname,
                $row->suffix, $row->hoblst, $row->barangay, $row->province, $row->city, $row->mobile,
                $row->gender, $row->civil_status, $row->birth_date, $row->religion, $row->education, 
                $row->livelihood_type, $row->farm_name, $row->farm_lot, $row->farming_since, $row->name_of_org,
                ];
            }

        } 
        // else if ($request->input('report') == 2) {
        //     $title = "My PO Listing";
        //     $data = $this->repository->getOrderSummary(Auth::id(), $request)
        //             ->with(['owner'])
        //             ->orderBy('code', 'ASC')
        //             ->get();

        //     $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Merchant', 'Ref No', 'Qty', 'Amount'];

        //     foreach ($data as $row) {
        //         $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->owner->business_name, $row->refno, $row->total_qty, $row->total_amount];
        //     }

        // } else if ($request->input('report') == 3) {
        //     $title = "My PO Listing With Item";
        //     $data = $this->repository->getOrderSummary(Auth::id(), $request)
        //             ->with(['owner'])
        //             ->orderBy('code', 'ASC')
        //             ->get();

        //     $dataArray[] = ['PO Code', 'PO Date', 'Date Needed', 'Merchant', 'Ref No', 'Qty', 'Amount'];

        //     foreach ($data as $row) {
        //         $dataArray[] = [$row->code, $row->podate, $row->date_needed, $row->owner->business_name, $row->refno, $row->total_qty, $row->total_amount];
        //         $counter = 1;
        //         foreach ($row->items as $item) {
        //             $dataArray[] = [$counter ,$item->sku, $item->name, $item->variety,$item->qty, $item->price, $item->amount];
        //             $counter += 1;
        //         }
        //     }
        // }
        
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
}
