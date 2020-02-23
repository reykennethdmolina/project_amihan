<?php

namespace App\Http\Controllers\API\MOBILE;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityPartner;
use App\Models\User;
// use Illuminate\Support\Facades\Auth;

class FetchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function product()
    {
        return Product::where(['post_status' => 'Y'])
                ->with(['category'])
                ->orderBy('id', 'DESC')
                ->paginate(15);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function featProduct()
    {
        return Product::where('is_featured', 1)
                ->where('post_status', 'Y')
                ->select('id', 'name', 'sku', 'variety', 'slug', 'created_at', 'is_featured', 'photo')
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->get();
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function category()
    {
        return Category::orderBy('is_active')->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function partner()
    {
        return User::where('type', '!=', 'user')
            ->select(['id', 'firstname', 'lastname', 'mobile', 'photo', 'last_log'])
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->orderBy('business_name', 'DESC');
            }))
            ->where('type', '!=' ,'user')
            ->where('show', 1)
            ->paginate(9);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function community()
    {
        return Community::orderBy('name', 'ASC')->paginate(9);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productData($id) 
    {
        return Product::where(['post_status' => 'Y'])
            ->where('id', @$id)
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
}
