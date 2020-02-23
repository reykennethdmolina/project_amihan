<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Profile;
use App\Models\FarmTypology;
use Gate;

use Carbon\Carbon;

class MemberController extends Controller
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
        // if (Gate::allows('isAdmin') || Gate::allows('isCoop')) {
            
        // }
        //return User::where(['type' => 'member', 'group_id' => Auth::id()])
        return User::where(['type' => 'member'])
                ->whereRaw('FIND_IN_SET(?, group_id)', [Auth::id()])
                ->with(['profile'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
    }

    public function farmtypo()
    {
        return FarmTypology::get();
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

        #print_r($request->all());

        $this->validate($request, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'mobile' => 'required|string|min:11|max:11|unique:users',
            'profile.business_name' => 'required|string|max:191|unique:profiles,business_name',
            'profile.business_email' => 'required|string|max:191|unique:profiles,business_email',
            'profile.contact_person' => 'required|string|max:191',
            //'profile.hoblst' => 'required|string|max:191',
            //'profile.barangay' => 'required|string|max:191',
            'profile.city' => 'required|string|max:191',
            'profile.province' => 'required|string|max:191',
            'profile.farm_typology' => 'required|string|max:191',
            //'profile.postal_code' => 'required|string|max:191',
        ]);

        $membership_date = str_replace('T', ' ',substr($request['membership_date'], 0, -5));
        
        $user = User::create([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'type' => 'member',
            'mobile' => $request['mobile'],
            'group_id' => Auth::id(),
            'agro_group_id' => 2, // farmers access group
            'last_log' => Carbon::now(),
        ]);

        $user->profile()->create([
            'membership_date' => $membership_date,
            'landline' => $request['profile']['landline'],
            'fax' => $request['profile']['fax'],
            'business_name' => $request['profile']['business_name'],
            'business_name_slug' => str_slug($request['profile']['business_name']),
            'business_email' => $request['profile']['business_email'],
            'contact_person' => $request['profile']['contact_person'],
            'contact_position' => $request['profile']['contact_position'],
            'tin' => $request['profile']['tin'],
            'hoblst' => $request['profile']['hoblst'],
            'barangay' => $request['profile']['barangay'],
            'city' => $request['profile']['city'],
            'province' => $request['profile']['province'],
            'country' => 'PH',
            'postal_code' => $request['profile']['postal_code'],
            'landmark' => $request['profile']['landmark'],
            'farmlot' => $request['profile']['farmlot'],
            'farm_typology' => $request['profile']['farm_typology'],
            'updated_by' => Auth::id(),
        ]);

        return $user;
    }

    public function addExistingMember(Request $request) {
        $new = $request['id'];
        
        $member = User::findOrFail($new);

        if (empty($member->group_id)) {
            $member->update([
                'group_id' => Auth::id()
            ]);
        } else {
            $groups = explode(',', $member->group_id);
            array_push($groups, Auth::id());
            $groups = implode(',', $groups);
            $member->update([
               'group_id' => $groups 
            ]);
        }

        return $member;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $existing = User::where(['type' => 'member'])
                    ->whereRaw('FIND_IN_SET(?, group_id)', [Auth::id()])
                    ->pluck('id');
        
        return User::where('id', $id)->where(['type' => 'member'])
                ->where('id', '<>', Auth::id())
                ->whereNotIn('id', $existing)
                ->with('profile')->first();
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

        $member = User::findOrFail($id);
        $memberprofile = Profile::where('user_id', $id)->first();
        
        $this->validate($request, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$member->id,
            'mobile' => 'required|string|min:11|max:11|unique:users,mobile,'.$member->id,
            'profile.business_name' => 'required|string|max:191|unique:profiles,business_name,'.$memberprofile->id,
            'profile.business_email' => 'required|string|max:191|unique:profiles,business_email,'.$memberprofile->id,
            'profile.contact_person' => 'required|string|max:191',
            //'profile.hoblst' => 'required|string|max:191',
            //'profile.barangay' => 'required|string|max:191',
            'profile.city' => 'required|string|max:191',
            'profile.province' => 'required|string|max:191',
            'profile.farm_typology' => 'required|string|max:191',
            //'profile.postal_code' => 'required|string|max:191',
        ]);

        $member->update([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'email' => $request['email'],
            'mobile' => $request['mobile'],
        ]);

        $data = $member->profile()->first();

        if ($data['membership_date'] != $request['membership_date']) {
            $membership_date = str_replace('T', ' ',substr($request['membership_date'], 0, -5));
        } else {
            $membership_date = $data['membership_date'] ;
        }
        
        $member->profile()->update([
            'membership_date' => $membership_date,
            'landline' => $request['profile']['landline'],
            'fax' => $request['profile']['fax'],
            'business_name' => $request['profile']['business_name'],
            'business_name_slug' => str_slug($request['profile']['business_name']),
            'business_email' => $request['profile']['business_email'],
            'contact_person' => $request['profile']['contact_person'],
            'contact_position' => $request['profile']['contact_position'],
            'tin' => $request['profile']['tin'],
            'hoblst' => $request['profile']['hoblst'],
            'barangay' => $request['profile']['barangay'],
            'city' => $request['profile']['city'],
            'province' => $request['profile']['province'],
            'country' => 'PH',
            'postal_code' => $request['profile']['postal_code'],
            'landmark' => $request['profile']['landmark'],
            'farmlot' => $request['profile']['farmlot'],
            'farm_typology' => $request['profile']['farm_typology'],
            'updated_by' => Auth::id(),
        ]);

        return ['message' => 'Member updated'];
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

        $member = User::findOrFail($id);

        $groups = explode(',', $member->group_id);

        if (count($groups) <= 1) {
            // Delete
            $member->delete();
        } else {
            // Remove from group
            $find = array_search(Auth::id(), $groups, false);
            unset($groups[$find]);
            $groups = implode(',', $groups);
            $member->update([
               'group_id' => $groups 
            ]);
        }

        return ['message' => 'Member deleted'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $members = User::where(function($query) use ($search){
                $query->where('firstname', 'LIKE', "%$search%")
                    ->orWhere('lastname', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            })->where(['type' => 'member', 'group_id' => Auth::id()])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $members = User::where(['type' => 'member', 'group_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $members;
    }

}
