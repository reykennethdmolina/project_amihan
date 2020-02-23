<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductionMasterList;
use Image;

class MyProductController extends Controller
{
    private $CONST_VAT, $CONST_VATRATE;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->CONST_VAT = 'VE';
        $this->CONST_VATRATE = 0;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('isAdminCoopMember'); 
        
        return Product::where(['seller_id' => Auth::id()])
                ->with(['category', 'subcategory'])
                ->orderBy('created_at', 'DESC')
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
            'category_id' => 'required|max:191',
            'name' => 'required|string|max:191',
            'description' => 'required|string|max:250|min:10',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_order' => 'required|numeric',
            'max_order' => 'required|numeric',
            'unit' => 'required|string',
            'weight' => 'required|numeric',
        ]);

        $compute = $this->compute($request, $this->CONST_VAT, $this->CONST_VATRATE);

        $sku = $this->sku();
        
        if ($request['photo']) {
            $photo = $this->uploadPhoto($request->photo, '', 1);
        } else {
            $photo = 'product.png';
        }
        if ($request['photo2']) {
            $photo2 = $this->uploadPhoto($request->photo2, '', 2);
        } else {
            $photo2 = 'product.png';
        }
        if ($request['photo3']) {
            $photo3 = $this->uploadPhoto($request->photo3, '', 3);
        } else {
            $photo3 = 'product.png';
        }

        $data = explode(';', $request['category_id']);
        $category = explode(':', $data[0]);
        $subcategory = explode(':', $data[1]);

        $key = strtolower($request['name'].' '.$request['variety'].' '.$category[1].' '.$subcategory[1]);

        $myProduct = Product::create([
            'category_id' => $category[0],
            'subcategory_id' => $subcategory[0],
            'sku' => $sku,
            'name' => $request['name'],
            'variety' => $request['variety'],
            'search_key' => $key,
            'slug' => $request['name'].' '.$request['variety'],
            'description' => $request['description'],
            'old_price' => $request['price'],
            'price' => $request['price'],
            'vatable_amount' => $compute['vatable_amount'],
            'vat_exempt_amount' => $compute['vat_exempt_amount'],
            'vat_zero_amount' => $compute['vat_zero_amount'],
            'vat_amount' => $compute['vat_amount'],
            'vat_type' => $compute['vat_type'],
            'vat_rate' => $compute['vat_rate'],
            'commission_rate' => $compute['commission_rate'],
            'commission_amount' => $compute['commission_amount'],
            'stock' => $request['stock'],
            'min_order' => $request['min_order'],
            'max_order' => $request['max_order'],
            'unit' => $request['unit'],
            'weight' => $request['weight'],
            'photo' => $photo,
            'photo2' => $photo2,
            'photo3' => $photo3,
            'seller_id' => Auth::id(),
        ]);

        return $myProduct;
    }

    private function uploadPhoto($file, $currentPhoto, $counter) {
        if ($file != $currentPhoto) {
            $photo = auth('api')->user()->id.''.$counter.''.time().'.'.explode('/', explode(':', 
                    substr($file, 0, strpos($file, ';')))[1])[1];
            
            $img = Image::make($file)->fit(480);
            $img->save(public_path('img/product/').$photo);

            /**
             * Delete old photo
             */
            if ($currentPhoto != 'profile.png' || $currentPhoto != '' || $currentPhoto != 'product.png') {
                $oldPhoto = public_path('img/product/').$currentPhoto;
                if (file_exists($oldPhoto)) {
                    @unlink($oldPhoto);
                }
            }
            return $photo;
        } else {
            return $file;
        }
    }

    private function sku() {
        $count = Product::where('seller_id', Auth::id())->count();
        return Auth::id().''.str_pad($count + 1, 4 ,"0", STR_PAD_LEFT);
    }

    private function compute($request, $vat, $vatrate) {
        $vatable_amount = 0;
        $vat_exempt_amount = 0;
        $vat_zero_amount = 0;
        $vat_amount = 0;
        $vat_type = $vat;
        $vat_rate = $vatrate;
        $commission_rate = 3;
        $commission_amount = 0;

        $price = $request['price'];

        if ($vat == 'VAT12') {
            $vatable_amount = round($price / (1 + ( $vat_rate / 100 )), 2);
            $commission_amount = round($vatable_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vatable_amount * ($vat_rate / 100 ), 2);
        } else if ($vat == 'VE') {
            $vat_exempt_amount = $price;
            $commission_amount = round($vat_exempt_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vat_exempt_amount * ($vat_rate / 100 ), 2);
        } else {
            $vat_zero_amount = $price;
            $commission_amount = round($vat_zero_amount * ($commission_rate / 100), 2);
            $vat_amount = round($vat_zero_amount * ($vat_rate / 100 ), 2);
        }

        return ([
            'vatable_amount' => $vatable_amount,
            'vat_exempt_amount' => $vat_exempt_amount,
            'vat_zero_amount' => $vat_zero_amount,
            'vat_amount' => $vat_amount,
            'vat_type' => $vat_type,
            'vat_rate' => $vat_rate,
            'commission_rate' => $commission_rate,
            'commission_amount' => $commission_amount,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::where('id', $id)
            ->where('seller_id', Auth::id())
            ->first();
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

        $myProduct = Product::findOrFail($id);

        $this->validate($request, [
            'category_id' => 'required|max:191',
            'name' => 'required|string|max:191',
            'description' => 'required|string|max:250|min:10',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'min_order' => 'required|numeric',
            'max_order' => 'required|numeric',
            'unit' => 'required|string',
            'weight' => 'required|numeric',
        ]);
            
        $compute = $this->compute($request, $this->CONST_VAT, $this->CONST_VATRATE);

        if ($request['photo']) {
            $photo = $this->uploadPhoto($request->photo, $myProduct->photo, 1);
        } else {
            $photo = 'product.png';
        }
        if ($request['photo2']) {
            $photo2 = $this->uploadPhoto($request->photo2, $myProduct->photo2, 2);
        } else {
            $photo2 = 'product.png';
        }
        if ($request['photo3']) {
            $photo3 = $this->uploadPhoto($request->photo3, $myProduct->photo3, 3);
        } else {
            $photo3 = 'product.png';
        }

        $data = explode(';', $request['category_id']);
        $category = explode(':', $data[0]);
        $subcategory = explode(':', $data[1]);

        $key = strtolower($request['name'].' '.$request['variety'].' '.$category[1].' '.$subcategory[1]);

        $myProduct->update([
            'category_id' => $category[0],
            'subcategory_id' => $subcategory[0],
            'name' => $request['name'],
            'variety' => $request['variety'],
            'search_key' => $key,
            'slug' => $request['name'].' '.$request['variety'],
            'description' => $request['description'],
            'old_price' => $myProduct->price,
            'price' => $request['price'],
            'vatable_amount' => $compute['vatable_amount'],
            'vat_exempt_amount' => $compute['vat_exempt_amount'],
            'vat_zero_amount' => $compute['vat_zero_amount'],
            'vat_amount' => $compute['vat_amount'],
            'vat_type' => $compute['vat_type'],
            'vat_rate' => $compute['vat_rate'],
            'commission_rate' => $compute['commission_rate'],
            'commission_amount' => $compute['commission_amount'],
            'stock' => $request['stock'],
            'min_order' => $request['min_order'],
            'max_order' => $request['max_order'],
            'unit' => $request['unit'],
            'weight' => $request['weight'],
            'photo' => $photo,
            'photo2' => $photo2,
            'photo3' => $photo3,
        ]);

        return ['message' => 'Product updated'];
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

        $myProduct = Product::findOrFail($id);

        $myProduct->delete();

        return ['message' => 'Product deleted'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function post($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $myProduct = Product::findOrFail($id);
        
        $myProduct->update([
            'post_status' => 'Y',
        ]);

        return ['message' => 'Product posted'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function hide($id)
    {
        $this->authorize('isAdminCoopMember'); 

        $myProduct = Product::findOrFail($id);
        
        $myProduct->update([
            'post_status' => 'N',
        ]);

        return ['message' => 'Product hidden'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $myProduct = Product::where(function($query) use ($search){
                $query->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhere('search_key', 'LIKE', "%$search%");
            })->where(['seller_id' => Auth::id()])
            ->with(['category', 'subcategory'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $myProduct = Product::where(['seller_id' => Auth::id()])
            ->with(['category', 'subcategory'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $myProduct;
    }

    public function listOfProduct()
    {
        return Product::where(['seller_id' => Auth::id()])
                ->orderBy('name', 'ASC')
                ->get();
    }

    public function guidelines(Request $request, $id)
    {
        $this->authorize('isAdminCoopMember'); 

        $myProduct = Product::findOrFail($id);
        
        $myProduct->update([
            'guide' => $request['text'],
        ]);

        return ['message' => 'Update guidelines'];
    }

    public function listOfCrops()
    {
        $personal = Product::where(['seller_id' => Auth::id()])
            ->where('category_id', 4)
            ->where('subcategory_id', 17)
            ->orderBy('name', 'ASC')
            ->get();

        $coop = ProductionMasterList::where(['member_id' => Auth::id()])
            ->whereHas('product', function ($query) {
                $query->where('category_id', 4)->where('subcategory_id', 17);
            })
            ->with('product')
            ->where('status', 'V')
            ->get();

        $crops = [];
        foreach ($coop as $crop) {
            array_push($crops, 
                array(
                    'id' => $crop->product->id,
                    'sku' => $crop->product->sku,
                    'name' => $crop->product->name,
                    'variety' => $crop->product->variety,
                    'slug' => $crop->product->slug,
                    'rows' => $crop->product->rows,
                    'hills' => $crop->product->hills,
                    'yieldperplant' => $crop->product->yieldperplant,
                    'maturity' => $crop->product->maturity,
                    'productivity' => $crop->product->productivity,
                    'traysize' => $crop->product->traysize,
                    'type' => 'coop',
            ));
        }

        foreach ($personal as $crop) {

            array_push($crops, 
                array(
                    'id' => $crop->id,
                    'sku' => $crop->sku,
                    'name' => $crop->name,
                    'variety' => $crop->variety,
                    'slug' => $crop->slug,
                    'rows' => $crop->rows,
                    'hills' => $crop->hills,
                    'yieldperplant' => $crop->yieldperplant,
                    'maturity' => $crop->maturity,
                    'productivity' => $crop->productivity,
                    'traysize' => $crop->traysize,
                    'type' => 'personal',
            ));
        }

        $data = collect($crops);

        $unique = $data->unique('slug')->sortBy('name');

        return $unique->values()->all();
        
    }

    public function listOfFruits()
    {
        $personal = Product::where(['seller_id' => Auth::id()])
            ->where('subcategory_id', 19)
            ->orderBy('name', 'ASC')
            ->get();

        $coop = ProductionMasterList::where(['member_id' => Auth::id()])
            ->whereHas('product', function ($query) {
                $query->where('subcategory_id', 19);
            })
            ->with('product')
            ->where('status', 'V')
            ->get();

        $crops = [];
        foreach ($coop as $crop) {
            array_push($crops, 
                array(
                    'id' => $crop->product->id,
                    'sku' => $crop->product->sku,
                    'name' => $crop->product->name,
                    'variety' => $crop->product->variety,
                    'slug' => $crop->product->slug,
                    'rows' => $crop->product->rows,
                    'hills' => $crop->product->hills,
                    'yieldperplant' => $crop->product->yieldperplant,
                    'maturity' => $crop->product->maturity,
                    'productivity' => $crop->product->productivity,
                    'traysize' => $crop->product->traysize,
                    'type' => 'coop',
            ));
        }

        foreach ($personal as $crop) {

            array_push($crops, 
                array(
                    'id' => $crop->id,
                    'sku' => $crop->sku,
                    'name' => $crop->name,
                    'variety' => $crop->variety,
                    'slug' => $crop->slug,
                    'rows' => $crop->rows,
                    'hills' => $crop->hills,
                    'yieldperplant' => $crop->yieldperplant,
                    'maturity' => $crop->maturity,
                    'productivity' => $crop->productivity,
                    'traysize' => $crop->traysize,
                    'type' => 'personal',
            ));
        }

        $data = collect($crops);

        $unique = $data->unique('slug')->sortBy('name');

        return $unique->values()->all();
        
    }
}
