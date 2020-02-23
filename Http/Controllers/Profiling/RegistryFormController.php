<?php

namespace App\Http\Controllers\Profiling;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\PermissionService;

class RegistryFormController extends Controller
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

    public function index() 
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }

        $data = [];

        return view('profiling.registry-form', compact('data'));
    }

    public function create()
    {
        $seg = request()->segment(1);
        $service = new PermissionService();

        if ($service->verify($seg) == 0) {
            return redirect()->route('home');
        }
        
        $data = [];

        return view('profiling.registry-form-create', compact('data'));   
    }
}
