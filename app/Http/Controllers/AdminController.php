<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Project;
use Redirect,Response;
class AdminController extends Controller
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
    public function index($user)
    {
		$loggeduser = Auth::user();
		if($loggeduser->name != 'admin')
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'Only admin has previlage to view this dashboard'
			);
			return Response::json($returnData, 500);
		}
		$user = User::where('name',$user)->get();
		$projects = Project::where('user_id',$user[0]->id)->get();
		$user = $user[0];
		return view('home',compact('user','projects'));
    }
}
