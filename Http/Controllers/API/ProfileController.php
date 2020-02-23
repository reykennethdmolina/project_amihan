<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

use Image;
use Auth;

class ProfileController extends Controller
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
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        $profile = User::find(auth('api')->user()->id)->load(['profile']);
        return $profile;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'mobile' => 'required|string|min:11|max:11|unique:users,mobile,'.$user->id,
        ]);

        $currentPhoto = $user->photo;

        if ($request->photo != $currentPhoto) {
            $photo = auth('api')->user()->id.''.time().'.'.explode('/', explode(':', 
                    substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            
            $img = Image::make($request->photo)->fit(480);
            $img->save(public_path('img/profile/').$photo);
            #Image::make($request->photo)->save(public_path('img/profile/').$photo);
            $request['photo'] = $photo;

            /**
             * Delete old photo
             */
            if ($currentPhoto != 'profile.png') {
                $oldPhoto = public_path('img/profile/').$currentPhoto;
                if (file_exists($oldPhoto)) {
                    @unlink($oldPhoto);
                }
            }
        }

        $user->update([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'email' => $request['email'],
            'mobile' => $request['mobile'],
            'photo' => $request['photo'],
        ]);
        
        return ['message' => 'Success'];
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProfileBusiness(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'profile.business_name' => 'required|string|max:191|unique:profiles,business_name,'.$user->profile->id,
            'profile.business_email' => 'required|string|max:191|unique:profiles,business_email,'.$user->profile->id,
            'profile.contact_person' => 'required|string|max:191',
        ]);

        $user->profile()->update([
            'landline' => $request['profile']['landline'],
            'fax' => $request['profile']['fax'],
            'business_name' => $request['profile']['business_name'],
            'business_name_slug' => str_slug($request['profile']['business_name']),
            'business_email' => $request['profile']['business_email'],
            'contact_person' => $request['profile']['contact_person'],
            'contact_position' => $request['profile']['contact_position'],
            'tin' => $request['profile']['tin'],
            'about' => $request['profile']['about'],
            'updated_by' => Auth::id(),
        ]);
        
        return ['message' => 'success'];
    }

    public function updateProfileAddress(Request $request) {
        $user = auth('api')->user();

        $this->validate($request, [
            'profile.hoblst' => 'required|string|max:191',
            'profile.barangay' => 'required|string|max:191',
            'profile.city' => 'required|string|max:191',
            'profile.province' => 'required|string|max:191',
            'profile.postal_code' => 'required|string|max:191',
        ]);    

        $user->profile()->update([
            'hoblst' => $request['profile']['hoblst'],
            'barangay' => $request['profile']['barangay'],
            'city' => $request['profile']['city'],
            'province' => $request['profile']['province'],
            'country' => $request['profile']['country'],
            'postal_code' => $request['profile']['postal_code'],
            'landmark' => $request['profile']['landmark'],
            'updated_by' => Auth::id(),
        ]);

        return ['message' => 'success'];
    }

    public function updateProfileSetting(Request $request) {
        $user = auth('api')->user();
        
        $this->validate($request, [
            'profile.delivery_fee' => 'numeric'
        ]);  

        $enable = 0;
        $targeted = NULL;
        $pickuplocation = NULL;
        $disallowdates = NULL;
        $paymentmode = NULL;
        $bankdetail = NULL;

        if ($request['profile']['enable_pickup']) {
            $enable = 1;
        }
        if ($request['profile']['delivery_target'] != '[]') {
            $targeted = $request['profile']['delivery_target'];
        }
        if ($request['profile']['pickuplocation'] != '[]') {
            $pickuplocation = $request['profile']['pickuplocation'];
        }
        if ($request['profile']['disallowdates'] != '[]') {
            $disallowdates = $request['profile']['disallowdates'];
        }
        if ($request['profile']['paymentmode'] != '[]') {
            $paymentmode = $request['profile']['paymentmode'];
        }

        if ($request['profile']['bankdetail'] != '[]') {
            $bankdetail = $request['profile']['bankdetail'];
        }


        $user->profile()->update([
            'enable_pickup' => $enable,
            'delivery_target' => $targeted,
            'delivery_fee' => $request['profile']['delivery_fee'],
            'pickuplocation' => $pickuplocation,
            'disallowdates' => $disallowdates,
            'paymentmode' => $paymentmode,
            'bankdetail' => $bankdetail,
        ]);
        
        return ['message' => 'success'];
    }

}
