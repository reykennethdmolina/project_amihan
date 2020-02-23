<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserWorker;
use App\Models\UserWorkerModules;

use Auth;
use DB;

class FarmWorkerLoginController extends Controller
{
    public function doLoginFarmWorker(Request $request)
    {
        $this->validate($request, [
            'main_user_id'    => 'required|numeric', 
            'username'    => 'required', 
            'password' => 'required|min:3' 
        ]);

        $checkWorker = UserWorker::where('main_user_id', $request->input('main_user_id'))
                        ->where('username', $request->input('username'))
                        ->where('active', 1)
                        ->first();

        if ( !$checkWorker ) { 
            return redirect('login-farm-worker')->with('invalid', 'Farm Worker Not Found!');
        }

        if (Hash::check($request->input('password'), $checkWorker->password)) {
            //password matched. Log in the user 

            //echo '<pre>';
            //print($checkWorker->load('mainuser'));

            //return 'asd';

            $user = User::findOrFail($checkWorker->main_user_id);
            //$user['worker'] = 'x'; #$checkWorker->username;

            auth()->login($user);

            //return Auth::user();
            
            session(['worker' => $checkWorker->username, 'workerid' => strval($checkWorker->id), 'worker_status' => true, 'workername' => $checkWorker->firstname.' '.$checkWorker->lastname]);
            
            session_start();
            $_SESSION['farm_worker'] = $checkWorker->username;
            //print_r($user);
            //var_dump(Auth::user()->worker);
            //die(Auth::user());
            $this->workerModule();

            return redirect('/home-farm-worker');
        } else {
            return redirect('login-farm-worker')->with('invalid', 'User Credentials Not Found!');    
        }
        
    }

    public function workerModule() 
    {
        # Creating session for user access

        $group_modules = [];
        $worker_id = session('workerid');

        $worker_modules = UserWorkerModules::where('main_user_id', Auth::user()->id)
                    ->where('user_worker_modules.user_worker_id', $worker_id)
                    ->leftJoin('agro_modules', 'agro_modules.id', '=', 'user_worker_modules.agro_module_id')
                    ->leftJoin('agro_main_modules', 'agro_main_modules.id', '=', 'agro_modules.agro_main_module_id')
                    ->select(DB::raw('agro_main_modules.name AS main_module, agro_main_modules.code AS main_code, agro_main_modules.icon AS main_icon, agro_main_modules.sort AS main_sort'),
                                    'agro_modules.code', 'agro_modules.name', 'agro_modules.icon', 'agro_modules.sort')
                    ->orderBy('agro_main_modules.sort', 'ASC')
                    ->orderBy('agro_modules.sort', 'ASC')
                    ->get();
        //die($worker_modules);
        // $user_modules = AgroUserModules::where('user_id', Auth::user()->id)
        //             ->leftJoin('agro_modules', 'agro_modules.id', '=', 'agro_user_modules.agro_module_id')
        //             ->leftJoin('agro_main_modules', 'agro_main_modules.id', '=', 'agro_modules.agro_main_module_id')
        //             ->select(DB::raw('agro_main_modules.name AS main_module, agro_main_modules.code AS main_code, agro_main_modules.icon AS main_icon, agro_main_modules.sort AS main_sort'),
        //                             'agro_modules.code', 'agro_modules.name', 'agro_modules.icon', 'agro_modules.sort')
        //             ->orderBy('agro_main_modules.sort', 'ASC')
        //             ->orderBy('agro_modules.sort', 'ASC')
        //             ->get();
       
        $groupmod = collect($group_modules);

        $usermod = collect($worker_modules);

        $merged = $groupmod->merge($usermod)->sortBy(['main_sort']);
        $modules = $merged->unique('code');
        $modules->values()->all();

        $grouped = $modules->groupBy('main_code');

        $grouped->toArray();
        
        session(['usr_modules' => $grouped]);

        return 'True';

    }
}
