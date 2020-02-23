<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\PermissionService;

class ProductionGuidelinesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }
        
        $this->authorize('isAdminCoopMember'); 

        $product = Product::findOrFail($id);

        return view('admin.production-guidelines', compact('product'));
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
        $product = Product::findOrFail($id);
        
        $product->update([
            'guide' => $request['guideEditor'],
        ]);
        
        return redirect()->route('production.guidelines', ['id' => $product->id])->with('guide-status', 'Production guidelines updated!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateMatrix(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $product->update([
            'rows' => $request['rows'],
            'hills' => $request['hills'],
            'yieldperplant' => $request['yieldperplant'],
            'maturity' => $request['maturity'],
            'productivity' => $request['productivity'],
            'traysize' => $request['traysize'],
        ]);
        
        return redirect()->route('production.guidelines', ['id' => $product->id])->with('matrix-status', 'Production matrix updated!');
    }
}
