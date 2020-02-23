<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Models\EggProduction;
use App\Models\RiceProduction;
use App\Models\CropProduction;
use App\Models\BroilerProduction;
use App\Models\PigFattenerProduction;
use App\Models\FruitProduction;
use App\Models\AgroGroupModules;
use App\Models\AgroUserModules;
use App\Models\PoMain;
use App\Models\RegistryForm;

use App\Services\PermissionService;

use DB;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $check = $request->session()->get('usr_modules');

        if (!$check) {
            $this->userModule();
        } 
        if (session('worker_status')) {
            return redirect()->route('home-farm-worker');
        } else {
            return view('home');    
        }
    }

    public function indexFarmWorker(Request $request)
    {
        return view('home-farm-worker');
    }
    
    public function userModule() 
    {
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

        return 'True';

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('profile');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function myorder()
    {
        return view('myorder');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function orders()
    {
        return view('admin-master');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function myproducts()
    {
        return view('admin-master');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function members()
    {
        return view('admin-master');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function open()
    {
        return view('admin-master');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function modules()
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }

        return view('admin-master');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword()
    {
        $user = Auth::user();
        //return $user->email;

        //return app('auth.password.broker')->sendResetLink($user->email);
        //$token = app('auth.password.broker')->createToken($user);
        return view('reset-password');
    }

    public function checkEggProductionView($id) {
        
        $production = EggProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('egg-production');
        }
    }

    public function checkRiceProductionView($id) {
        
        $production = RiceProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('rice-production');
        }
    }

    public function checkCropProductionView($id) {
        
        $production = CropProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('crop-production');
        }
    }

    public function checkBroilerProductionView($id) {
        
        $production = BroilerProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('broiler-production');
        }
    }

    public function checkPigFattenerProductionView($id) {
        
        $production = PigFattenerProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('broiler-production');
        }
    }

    public function checkFruitProductionView($id) {
        
        $production = FruitProduction::where(['owner_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('fruit-production');
        }
    }

    public function checkRegistryFormView($id) {
        
        $production = RegistryForm::where(['mao_id' => Auth::id()])
            ->where('id', $id)
            ->count();

        if ($production) {
            return view('home');
        } else {
            return redirect()->route('registry-form');
        }
    }

    public function qrpayment($crypt) {

        try {
            $decrypted = Crypt::decryptString($crypt);
        } catch (DecryptException $e) {
            return redirect()->route('welcome');
        }

        $po = PoMain::where(['customer_id' => Auth::id(), 'id' => $decrypted, 'paymentMode' => 'QRPAY', 'payment_status' => 'P'])->count();

        if ($po == 0) {
            return redirect()->route('welcome');
        } else {
            return view('qrpayment');
        }

    }

    public function bankdeposit($crypt) {

        try {
            $decrypted = Crypt::decryptString($crypt);
        } catch (DecryptException $e) {
            return redirect()->route('welcome');
        }

        $po = PoMain::where(['customer_id' => Auth::id(), 'id' => $decrypted, 'paymentMode' => 'BANKDEPO'])->count();

        if ($po == 0) {
            return redirect()->route('welcome');
        } else {
            return view('qrpayment');
        }
    }

    public function qrpaymentConvert($id) {
        try {
            $crypt = Crypt::encryptString($id);
        } catch (DecryptException $e) {
            return redirect()->route('welcome');
        }
        
        return redirect()->route('qrpayment', ['crypt' => $crypt]);
    }

    public function bankDepositConvert($id) {
        try {
            $crypt = Crypt::encryptString($id);
        } catch (DecryptException $e) {
            return redirect()->route('welcome');
        }
        
        return redirect()->route('bankdeposit', ['crypt' => $crypt]);
    }

    public function bankDepositView($id) {
        $po = PoMain::where(['owner_id' => Auth::id(), 'id' => $id, 'paymentMode' => 'BANKDEPO'])->count();

        if ($po == 0) {
            return redirect()->route('home');
        } else {
            return view('bankdepositview');
        }    
    }
    
}
