<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Validator;
use App\Models\Category;
use App\Models\Product;

class PageController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $featprod = Product::where('is_featured', 1)
                        ->where('post_status', 'Y')
                        ->select('id', 'name', 'sku', 'variety', 'slug', 'created_at', 'is_featured', 'photo')
                        ->orderBy('created_at', 'DESC')
                        ->limit(8)
                        ->get();
                        
        $categories = Category::where('is_active', 0)->orderBy('name')->get();
        return view('welcome-v1', compact('categories', 'featprod'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index2()
    {
        $featprod = Product::where('is_featured', 1)
                        ->where('post_status', 'Y')
                        ->select('id', 'name', 'sku', 'variety', 'slug', 'created_at', 'is_featured', 'photo')
                        ->orderBy(DB::raw('RAND()')) 
                        ->limit(6)
                        ->get();
                        
        $categories = Category::orderBy('is_active')->get();
        return view('welcome', compact('categories', 'featprod'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index3()
    {
        $featprod = Product::where('is_featured', 1)
                        ->where('post_status', 'Y')
                        ->select('id', 'name', 'sku', 'variety', 'slug', 'created_at', 'is_featured', 'photo')
                        ->orderBy(DB::raw('RAND()')) 
                        ->limit(6)
                        ->get();
                        
        $categories = Category::orderBy('is_active')->get();
        return view('welcome-v3', compact('categories', 'featprod'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        return view('privacy');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function termAndCondition()
    {
        return view('termAndCondition');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function contactUs()
    {
        return view('contactUs');
    }

    public function register($code)
    {
        return view('clientele.auth.register', compact('code'));
    }

    public function forgot($code=null)
    {
        return view('clientele.auth.forgot', compact('code'));
    }

    public function login($code=null)
    {
        return view('clientele.auth.login', compact('code'));
    }

    public function logout()
    {
        session_start();
        $_SESSION = array();
        session_destroy();
        
        session()->forget('usr_modules');

        $org = @Auth::user()->org;
        if ($org != '') {
            # Redirect to clientele page
            Auth::logout();
            return redirect('/espclientele/'.$org);
        } else {
            Auth::logout();
            return redirect('/');
        }

    }

    public function loginFarmWorker()
    {
        return view('admin.worker.login');
    }

    public function logoutFarmWorker()
    {
        session_start();
        $_SESSION = array();
        session_destroy();

        session()->forget('usr_modules');

        Auth::logout();
        return redirect('/login-farm-worker');

    }


    // public function singleproduct()
    // {
    //     return view('singleproduct');
    // }  

}
