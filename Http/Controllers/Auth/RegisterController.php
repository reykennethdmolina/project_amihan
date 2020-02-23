<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Community;
use App\Models\SocialProvider;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Mail\RegistrationEmail;

use Mail;
use DB;
use Socialite;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $community = Community::select('code', 'name')->orderBy('name', 'ASC')->get();
        return view("auth.register", compact("community"));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // custom validator called startswith
        Validator::extend('startswith', function( $attribute, $value, $parameters ) {
            return substr( $value, 0, strlen( $parameters[0] ) ) == $parameters[0];
        });

        // Error message for startswith failure
        $messages = [ 'startswith' => 'invalid mobile number' ];
        
        return Validator::make($data, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'mobile' => 'required|string|min:11|max:11|unique:users|startswith:09',
        ], $messages);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $org = $data['org'];

        if ($data['org'] == 'open') {
            $org = '';
        }

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => $data['mobile'],
            'org' => $org,
        ]);

        $user->profile()->create([
            'landline' => '',
        ]);

        Mail::to($user->email)->send(new RegistrationEmail($user));
        
        # Redirect to clientele landing page
        if ($user->org == '') {
            $this->redirectTo = '/';
        } else {
            $this->redirectTo = '/espclientele/'.$user->org;   
        }

        return $user;
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect($provider, $org = '')
    {
        session(['org_key' => $org]);
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect('/');
        }

        // Check if we have logged provider
        $socialProvider = SocialProvider::where('provider_id', $socialUser->id)->first();

        if(!$socialProvider) {
            // Create a new user and provider
            $org = $request->session()->get('org_key');

            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                ['firstname' => ucwords($socialUser->getName()), 'lastname' => ' ', 'org' => $org]
            );

            $user->socialProviders()->create([
                'provider_id' => $socialUser->getId(),
                'provider' => $provider
            ]);

        } else {
            $user = $socialProvider->user;
        }

        auth()->login($user);

        return redirect('/home');
    }
}
