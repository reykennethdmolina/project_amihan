<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\PoMain;

class MyPurchaseOrderController extends Controller
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
        return PoMain::where(['customer_id' => Auth::id()])
                ->with(['owner'])
                ->orderBy('id', 'DESC')
                //->orderBy('podate', 'DESC')
                //->orderByRaw("FIELD(status,'P','M','O','C', 'D')")
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
        return Pomain::where(['id' => $id, 'customer_id' => Auth::id()])->with(['items'])->first();
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
        $po = PoMain::findOrFail($id);

        $po->update([
            'status' => 'C',
            'updated_by' => Auth::id()
        ]);

        return ['message' => 'PO deleted'];
    }

    public function search()
    {
        if ($search = \Request::get('q')) {
            $po = PoMain::where(function($query) use ($search){
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('customer_name', 'LIKE', "%$search%")
                    ->orWhere('refno', 'LIKE', "%$search%");
            })->where(['customer_id' => Auth::id()])
            ->with(['owner'])
            ->orderBy('podate', 'DESC')
            ->orderByRaw("FIELD(status,'P','M','O','C', 'D')")
            ->paginate(15);
        } else {
            $po = PoMain::where(['customer_id' => Auth::id()])
                ->with(['owner'])
                ->orderBy('podate', 'DESC')
                ->orderByRaw("FIELD(status,'P','M','O','C', 'D')")
                ->paginate(15);
        }

        return $po;
    }
}
