<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\PermissionService;
use Session;
use Redirect;

class ProfileController extends Controller
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

    public function changePassword()
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }

        $user = Auth::user();

        return view('profile/change-password', compact('user'));
    }

    public function changePasswordRequest(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request['password']),
        ]);

        Session::flash('changePasswordStatus', "Change password was successful!");
        return Redirect::back();

        #return redirect()->back()->with('changePasswordStatus', 'Change password was successful!');   


        #$request->session()->flash('changePasswordStatus', 'Change password was successful!');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'mobile' => 'required|string|min:11|max:11|unique:users',
        ]);
    }
    
}
