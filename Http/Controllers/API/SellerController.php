<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Profile;
use App\Models\CommunityPartner;

class SellerController extends Controller
{
    public function index()
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

    public function search()
    {
        if ($search = \Request::get('q')) {
            $seller = User::where('type', '!=', 'user')
            ->where('show', 1)
            ->select(['id', 'firstname', 'lastname', 'mobile', 'photo', 'last_log'])
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->orderBy('business_name', 'DESC');
            }))
            ->whereHas('profile', function ($query) use ($search) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->where('contact_person', 'like', '%'.$search.'%')
                    ->orWhere('business_name', 'like', '%'.$search.'%')
                    ->orWhere('business_email', 'like', '%'.$search.'%')
                    ->orWhere('city', 'LIKE', '%'.$search.'%')
                    ->orWhere('province', 'LIKE', '%'.$search.'%');
            })
            ->paginate(9);
        } else {
            $seller = User::where('type', '!=', 'user')
            ->select(['id', 'firstname', 'lastname', 'mobile', 'photo', 'last_log'])
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->orderBy('business_name', 'DESC');
            }))
            ->where('type', '!=' ,'user')
            ->where('show', 1)
            ->paginate(9);
        }

        return $seller;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $seller = User::where('type', '!=', 'user')
            ->where('show', 1)
            ->select(['id', 'firstname', 'lastname', 'mobile', 'photo', 'last_log'])
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->orderBy('business_name', 'DESC');
            }))
            ->whereHas('profile', function ($query) use ($slug) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->where('business_name_slug', ''.$slug.'');
            })
            ->first();

        if ($seller) {
            return ['status' => true, 'data' => $seller];
        } else {
            return ['status' => false, 'data' => null];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function partner($id)
    {
        $seller = User::where('type', '!=', 'user')
            //->where('show', 1)
            ->where('id', $id)
            ->select(['id', 'firstname', 'lastname', 'mobile', 'photo', 'last_log'])
            ->with(array('profile' => function($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about'])
                    ->orderBy('business_name', 'DESC');
            }))
            ->whereHas('profile', function ($query) {
                $query->select(['id', 'user_id', 'about', 'contact_person', 'contact_position', 'business_name', 'business_name_slug', 'hoblst', 'barangay', 'city', 'province', 'postal_code', 'about']);
            })
            ->first();

        if ($seller) {
            return ['status' => true, 'data' => $seller];
        } else {
            return ['status' => false, 'data' => null];
        }
    }

    public function partnerList($id)
    {
        return CommunityPartner::where('community_id', $id)
                ->with(['partners.user'])
                ->get();
    }

}
