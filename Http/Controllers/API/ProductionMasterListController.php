<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\ProductionMasterList;

use Auth;

class ProductionMasterListController extends Controller
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
        
        return Product::select('id', 'sku', 'seller_id', 'category_id', 'subcategory_id', 'name', 'slug', 'variety')
                ->where(['seller_id' => Auth::id()])
                ->with(['category', 'subcategory', 'productionmasterlists.member'])
                ->orderBy('name', 'ASC')
                ->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function memberProductionMasterList()
    {
        $this->authorize('isAdminCoopMember'); 

        return ProductionMasterList::where(['member_id' => Auth::id()])
                ->with(['product.category', 'product.subcategory'])
                ->get();
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

    public function assignMembers(Request $request)
    {
        $item = $request['item'];
        $members = $request['members'];

        foreach ($members as $member) {
            ProductionMasterList::updateOrCreate(
                ['owner_id' => $item['seller_id'], 'member_id' => $member, 'product_id' => $item['id']],
                ['status' => 'A', 'updated_by' => Auth::id()]
            );
        }
    }

    public function applyProduction(Request $request)
    {
        $item = $request['item'];   
        $products = $request['products']; 

        foreach ($products as $product) {
            ProductionMasterList::updateOrCreate(
                ['owner_id' => Auth::user()->group_id, 'member_id' => Auth::id(), 'product_id' => $product],
                ['status' => 'P', 'updated_by' => Auth::id(),]
            );
        }
    }

    public function productionList()
    {
        return Product::where('seller_id', Auth::user()->group_id)
                ->orderBy('name', 'ASC')
                ->get();
    }

    public function statusAssignMembers(Request $request, $id, $stat)
    {
        $this->authorize('isAdminCoopMember'); 
        $production = ProductionMasterList::findOrFail($id);

        if ($stat == 'remove') {

            $production->update(['status' => 'RC', 'updated_by' => Auth::id()]);
    
            $production->delete();

            echo 'remove';
    
        } else if ($stat == 'verify') {

            echo $production;

            $production->update(['status' => 'V', 'updated_by' => Auth::id(),]);

            echo 'verify';

        } else if ($stat == 'deny') {

            $production->update(['status' => 'D', 'updated_by' => Auth::id(),]);

            echo 'deny';

        } else {
            echo 'Do Nothing';
        }
    }
}
