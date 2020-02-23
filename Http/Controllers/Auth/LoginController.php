<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use App\Models\AgroGroupModules;
use App\Models\AgroUserModules;
use Carbon\Carbon;
use DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $user->update([
            'last_log' => Carbon::now()->toDateTimeString(),
        ]);

        # Creating session for user access
        $agro_user_id = Auth::user()->agro_group_id;

        $group_modules = AgroGroupModules::where('agro_group_id', $agro_user_id)
                    ->leftJoin('agro_modules', 'agro_modules.id', '=', 'agro_group_modules.agro_module_id')
                    ->leftJoin('agro_main_modules', 'agro_main_modules.id', '=', 'agro_modules.agro_main_module_id')
                    ->select(DB::raw('agro_main_modules.name AS main_module, agro_main_modules.code AS main_code, agro_main_modules.icon AS main_icon, agro_main_modules.sort AS main_sort'),
                                    'agro_modules.code', 'agro_modules.name', 'agro_modules.icon', 'agro_modules.sort')
                    ->orderBy('agro_main_modules.sort', 'ASC')
                    ->orderBy('agro_modules.sort', 'ASC')
                    ->get();

        $user_modules = AgroUserModules::where('user_id', Auth::user()->id)
                    ->leftJoin('agro_modules', 'agro_modules.id', '=', 'agro_user_modules.agro_module_id')
                    ->leftJoin('agro_main_modules', 'agro_main_modules.id', '=', 'agro_modules.agro_main_module_id')
                    ->select(DB::raw('agro_main_modules.name AS main_module, agro_main_modules.code AS main_code, agro_main_modules.icon AS main_icon, agro_main_modules.sort AS main_sort'),
                                    'agro_modules.code', 'agro_modules.name', 'agro_modules.icon', 'agro_modules.sort')
                    ->orderBy('agro_main_modules.sort', 'ASC')
                    ->orderBy('agro_modules.sort', 'ASC')
                    ->get();
       
        $groupmod = collect($group_modules);

        $usermod = collect($user_modules);

        $merged = $groupmod->merge($usermod)->sortBy(['main_sort']);
        $modules = $merged->unique('code');
        $modules->values()->all();

        $grouped = $modules->groupBy('main_code');

        $grouped->toArray();
        
        session(['usr_modules' => $grouped]);

        $worker = explode('@',Auth::user()->email);

        session(['worker' => @$worker[0], 'worker_id' => strVal(Auth::user()->id), 'worker_status' => false]);
        //Cache::forever('worker', @$worker[0]);

        session_start();
        //$_SESSION['farm_worker'] = @$worker[0];
        $_SESSION['farm_worker'] = 'admin';

        # Redirect to clientele landing page
        if ($user->org == '') {
            if ($user->type == 'user') {
                return redirect('/');
            } else {
                return redirect('/home');
            }
        } else {
            return redirect('/espclientele/'.$user->org);     
        }

    }
}
