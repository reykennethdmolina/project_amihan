<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Gate;

class CustomerController extends Controller
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

        return Customer::where(['owner_id' => Auth::id()])
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
        
        $request['code'] = Auth::id().';'.$request['code'];

        $this->validate($request, [
            'code' => 'required|string|max:15|unique:customers',
            'name' => 'required|string|max:191',
            'province' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11',
        ]);

        $customer = Customer::create([
            'owner_id' => Auth::id(),
            'code' => $request['code'],
            'name' => $request['name'],
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],            
            'contact_person' => $request['contact_person'],            
            'contact_position' => $request['contact_position'],            
            'mobile' => $request['mobile'],            
            'landline' => $request['landline'],            
            'fax' => $request['fax'],            
            'tin' => $request['tin'],            
        ]);

        return $customer;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Customer::findOrFail($id);
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

        $customer = Customer::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'province' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'contact_person' => 'required|string|max:191',
            'mobile' => 'required|string|min:11|max:11|unique:customers,mobile,'.$customer->id,
        ]);

        $customer->update([
            'name' => $request['name'],
            'hoblst' => $request['hoblst'],
            'barangay' => $request['barangay'],            
            'city' => $request['city'],            
            'province' => $request['province'],            
            'postal_code' => $request['postal_code'],            
            'landmark' => $request['landmark'],            
            'contact_person' => $request['contact_person'],            
            'contact_position' => $request['contact_position'],            
            'mobile' => $request['mobile'],            
            'landline' => $request['landline'],            
            'fax' => $request['fax'],            
            'tin' => $request['tin'],   
        ]);

        return ['message' => 'Customer updated'];
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

        $member = Customer::findOrFail($id);

        $member->delete();

        return ['message' => 'Member deleted'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $customer = Customer::where(function($query) use ($search){
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%")
                    ->orWhere('contact_person', 'LIKE', "%$search%");
            })->where(['owner_id' => Auth::id()])
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
        } else {
            $customer = Customer::where(['owner_id' => Auth::id()])
                ->orderBy('created_at', 'DESC')
                ->paginate(15);
        }

        return $customer;
    }

    public function listOfCustomer()
    {
        $this->authorize('isAdminCoopMember');    

        return Customer::where(['owner_id' => Auth::id()])
                ->orderBy('name', 'ASC')
                ->get();
    }
}
