<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use User;
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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		$projects = Auth::user()->projects;
		$user = Auth::user();
		return view('home',compact('projects','user'));
    }
}
