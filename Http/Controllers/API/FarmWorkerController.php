<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\UserWorker;
use App\Models\UserWorkerModules;
use App\Models\AgroGroupModules;
use App\Models\AgroUserModules;
use App\Models\AgroModules;

class FarmWorkerController extends Controller
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

        return UserWorker::where(['main_user_id' => Auth::id()])
                ->with(['workerModules.module'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
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

        $this->validate($request, [
            'firstname' => 'required|string|max:191',
            'lastname' => 'required|string|max:191',
            'username' => 'required|string|max:191|unique:user_workers',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $worker = UserWorker::create([
            'firstname' => $request['firstname'],
            'lastname' => $request['lastname'],
            'username' => $request['username'],
            'password' => Hash::make($request['password']),
            'active' => abs($request['active']),
            'main_user_id' => Auth::id(),
        ]);


        $modules = $request['selectedModule'];

        foreach ($modules as $mod) {
            UserWorkerModules::create([
                'main_user_id' => Auth::id(),
                'user_worker_id' => $worker->id,
                'agro_module_id' => $mod
            ]);  
        }

        
        return ['message' => 'Worker added'];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

        $change = $request['change'];
        $worker = UserWorker::findOrFail($id);

        if ($change) {
            $this->validate($request, [
                'firstname' => 'required|string|max:191',
                'lastname' => 'required|string|max:191',
                'username' => 'required|string|max:191|unique:user_workers,username,'.$id,
                'password' => 'required|string|min:6|confirmed',
            ]);

            $worker->update([
                'firstname' => $request['firstname'],
                'lastname' => $request['lastname'],
                'username' => $request['username'],
                'password' => Hash::make($request['password']),
                'active' => abs($request['active']),
            ]);
        } else {
            $this->validate($request, [
                'firstname' => 'required|string|max:191',
                'lastname' => 'required|string|max:191',
                'username' => 'required|string|max:191|unique:user_workers,username,'.$id,
            ]);

            $worker->update([
                'firstname' => $request['firstname'],
                'lastname' => $request['lastname'],
                'username' => $request['username'],
                'active' => abs($request['active']),
            ]);
        }


        $modules = $request['selectedModule'];

        /** Set all modules to soft delete */

        UserWorkerModules::where(['main_user_id' => Auth::id(), 'user_worker_id' => $worker->id])->delete();

        foreach ($modules as $mod) {
            $data = UserWorkerModules::withTrashed()->where(['main_user_id' => Auth::id(), 'user_worker_id' => $worker->id, 'agro_module_id' => $mod])->first();

            if (!empty($data)) {
                $data->restore();
            } else {
                UserWorkerModules::create([
                    'main_user_id' => Auth::id(),
                    'user_worker_id' => $worker->id,
                    'agro_module_id' => $mod
                ]);  
            }
        }

        return ['message' => 'Worker updated'];
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

        $worker = UserWorker::findOrFail($id);

        $worker->delete();

        return ['message' => 'Worker deleted'];
    }

    public function search()
    {
        $this->authorize('isAdminCoopMember'); 
        
        if ($search = \Request::get('q')) {
            $workers = UserWorker::where(function($query) use ($search){
                $query->where('firstname', 'LIKE', "%$search%")
                    ->orWhere('lastname', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%");
            })->where(['main_user_id' => Auth::id()])
            ->with(['workerModules.module'])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
        } else {
            $workers = UserWorker::where(['main_user_id' => Auth::id()])
                ->with(['workerModules.module'])
                ->orderBy('created_at', 'DESC')
                ->paginate(20);
        }

        return $workers;
    }

    public function userModules()
    {
        $this->authorize('isAdminCoopMember'); 

        $agro_user_id = Auth::user()->agro_group_id;

        $group_modules = AgroGroupModules::where('agro_group_id', $agro_user_id)->pluck('agro_module_id');
        $user_modules = AgroUserModules::where('user_id', Auth::user()->id)->pluck('agro_module_id');

        $groupmod = collect($group_modules);

        $usermod = collect($user_modules);

        $merged = $groupmod->merge($usermod);

        // Get Only Selected Modules
        $modules = AgroModules::whereIn('id', $merged)
                ->whereIN('agro_main_module_id', [3, 7])
                ->whereIN('id', [6,7,8,9,21,24,23])
                ->orderBy('sort', 'ASC')
                ->get();

        return $modules;

    }
}
