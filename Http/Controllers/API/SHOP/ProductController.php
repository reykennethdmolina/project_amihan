<?php

namespace App\Http\Controllers\API\SHOP;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Community;
use App\Models\CommunityPartner;
use App\Repositories\ProductRepository;

use DB;

class ProductController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($page = null)
    {
        return Product::where(['post_status' => 'Y'])
                ->with(['category'])
                //->orderBy(DB::raw('RAND()')) 
                //->orderBy('name', 'ASC')
                //->orderBy('variety', 'ASC')
                ->orderBy('id', 'DESC')
                ->paginate(20);
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
    public function info($id)
    {
        return Product::where('id', $id)->select(['id', 'category_id', 'subcategory_id', 'seller_id'])
            ->with(array('seller' => function($query) {
                $query->select(['id', 'firstname', 'lastname', 'mobile', 'last_log'])
                    ->with(array('profile' => function($query) {
                        $query->select(['user_id', 'business_name', 'city', 'province', 'landline', 'fax']);
                    }));
            }))
            ->with(array('category' => function($query){
                $query->select(['id', 'name']);
            }))
            ->with(array('subcategory' => function($query){
                $query->select(['id', 'name']);
            }))->first();
    }

    public function data($slug) 
    {
        $data = explode("&=", $slug);
        return Product::where(['post_status' => 'Y'])
            ->where('slug', @$data[0])
            ->where('id', @$data[1])
            ->where('sku', @$data[2])
            ->with(array('seller' => function($query) {
                $query->select(['id', 'firstname', 'lastname', 'mobile', 'last_log'])
                    ->with(array('profile' => function($query) {
                        $query->select(['user_id', 'business_name', 'business_name_slug', 'city', 'province', 'landline', 'fax']);
                    }));
            }))
            ->with(array('category' => function($query){
                $query->select(['id', 'name']);
            }))
            ->with(array('subcategory' => function($query){
                $query->select(['id', 'name']);
            }))->first();
    }

    public function other($seller) {
        return Product::where(['post_status' => 'Y'])
            ->where('seller_id', $seller)
            ->select('id', 'name', 'sku', 'variety', 'slug', 'created_at', 'price', 'old_price', 'photo', 'min_order', 'max_order')
            ->orderBy(DB::raw('RAND()')) 
            ->limit(4)
            ->get();
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

    public function search($page=null, Request $request)
    {
        $category = $request->get('category');
        $q = $request->get('q');
        if ($category || $q) {
            return $this->productRepository->getProductSearch($id=null,$request)->paginate(20);
        } else{
            return Product::where(['post_status' => 'Y'])
                ->with(['category'])
                //->orderBy('created_at', 'DESC')
                //->orderBy(DB::raw('RAND()')) 
                ->orderBy('id', 'DESC')
                ->paginate(20);
        }
    }

    public function initOrderTransaction(Request $request)
    {
        $carts = $request['cart'];
        $list = array();
        foreach ($carts as $cart) {
            $data = $this->getProductData($cart);
            $data['name'] = $cart['name'];
            $data['variety'] = $cart['variety'];    
            $data['price'] = floatval($cart['price']);
            $data['qty'] = floatval($cart['qty']);
            $data['shippingCost'] = 0;
            // $data['shippingOption'] = false;
            // $data['shippingDetail'] = 'Choose shipping option';
            $data['shippingOption'] = "PICKUP"; // set default
            $data['shippingDetail'] = "Seller will contact you once your order is ready to pickup"; // set default
            $data['pickUpLocation'] = 'false';
            $data['weight'] = floatval($data['weight']);
            $data['min'] = $data['min_order'];
            $data['max'] = $data['max_order'];
            $data['subweight'] = round(floatval($data['qty']) * floatval($data['weight']), 2);
            $data['subtotal'] = round(floatval($data['qty']) * floatval($data['price']), 2);
            array_push($list, $data);
        }

        $result = array();
        foreach ($list as $element) {
            $result[$element['seller_id']][] = $element;
        }

        return $result;
    }

    public function initPreOrderTransaction(Request $request)
    {
        $carts = $request['cart'];
        $list = array();
        foreach ($carts as $cart) {
            $data = $this->getProductData($cart);
            $data['name'] = $cart['name'];
            $data['variety'] = $cart['variety'];
            $data['price'] = floatval($cart['price']);
            $data['qty'] = floatval($cart['qty']);
            $data['preOrderRemarks'] = '';
            $data['dateNeeded'] = '';
            $data['askingPrice'] = 0;
            $data['weight'] = floatval($data['weight']);
            $data['min'] = $data['min_order'];
            $data['max'] = $data['max_order'];
            $data['subweight'] = round(floatval($data['qty']) * floatval($data['weight']), 2);
            $data['subtotal'] = round(floatval($data['qty']) * floatval($data['price']), 2);
            array_push($list, $data);
        }

        $result = array();
        foreach ($list as $element) {
            $result[$element['seller_id']][] = $element;
        }

        return $result;
    }

    public function initPOTransaction(Request $request)
    {
        $carts = $request['cart'];
        $type = $request['type'];

        if ($type != 'open') {
            $community = Community::where('code', $type)->first();

            //return $community;
        }

        $list = array();
        foreach ($carts as $cart) {
            $data = $this->getProductData($cart);
            $data['name'] = $cart['name'];
            $data['variety'] = $cart['variety'];
            $data['price'] = floatval($cart['price']);
            $data['qty'] = floatval($cart['qty']);
            $data['preOrderRemarks'] = '';
            $data['dateNeeded'] = '';
            $data['delivery_details'] = '';
            $data['askingPrice'] = 0;
            $data['paymentMode'] = 'false';
            $data['pickUpLocation'] = 'false';
            $data['disallowdates'] = $data['disallowdates'];
            $data['paymentmode'] = $data['paymentmode'];
            $data['weight'] = floatval($data['weight']);
            $data['min'] = $data['min_order'];
            $data['max'] = $data['max_order'];
            $data['subweight'] = round(floatval($data['qty']) * floatval($data['weight']), 2);
            $data['subtotal'] = round(floatval($data['qty']) * floatval($data['price']), 2);
            array_push($list, $data);
        }

        $result = array();
        foreach ($list as $element) {
            $result[$element['seller_id']][] = $element;
            if ($type != 'open') {
                $result[$element['seller_id']][0]['seller']['profile']['disallowdates'] = $this->getCommunityDates($community, $element['seller_id']);
                $result[$element['seller_id']][0]['seller']['profile']['paymentmode'] = $this->getCommunityPaymentMode($community, $element['seller_id']);
                $result[$element['seller_id']][0]['seller']['profile']['pickuplocation'] = $this->getCommunityPickupLocation($community, $element['seller_id']);
            }
        }

        return $result;   
    }

    public function getCommunityDates($community, $seller)
    {
        $data = CommunityPartner::where(['community_id' => $community->id, 'partner_id' => $seller])->first();

        if ($data) {
            return $disallowdates = array(
                'disallowdates' => $data->disallowdates,
            );
        } else {
            return $disallowdates = array(
                'disallowdates' => "[0,1,2,3,4,5,6]",
            );
        }
        
    }

    public function getCommunityPickupLocation($community, $seller)
    {
        $data = CommunityPartner::where(['community_id' => $community->id, 'partner_id' => $seller])->first();

        if ($data) {
            return $data->pickuplocation;
        } else {
            return null;
        }
        
    }

    public function getCommunityPaymentMode($community, $seller)
    {
        $data = CommunityPartner::where(['community_id' => $community->id, 'partner_id' => $seller])->first();

        if ($data) {
            return $data->paymentmode;
        } else {
            return null;
        }
        
    }

    public function getProductData($info)
    {
        return Product::where('id', $info['id'])
            ->select(['id', 'sku', 'seller_id', 'name', 'variety', 'unit', 'weight', 'photo', 'min_order', 'max_order'])
            ->with(array('seller' => function($query) {
                $query->select(['id', 'firstname', 'lastname', 'mobile', 'last_log'])
                    ->with(array('profile' => function($query) {
                        $query->select(['user_id', 'business_name', 'business_email', 'enable_pickup', 'delivery_fee', 'delivery_target', 'pickuplocation', 'disallowdates', 'paymentmode', 'contact_person', 'hoblst', 'barangay', 'city', 'province', 'postal_code']);
                }));    
            }))
            ->first();
    }
}
