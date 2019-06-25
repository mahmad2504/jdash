<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect,Response;
use App\Resource;
use App\User;
use Auth;
use App\Utility;

class ResourceController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
		
		//if(Auth::user()->role == 'admin')
    }
	public function projectview($username,$projectname)
	{
		
		$user = Auth::user();
		if($user->IsAdmin() || $user->name == $username )
		{
			$tree = Utility::GetProjectTree($username,$projectname);
			if(is_string($tree))
				abort(403, $tree);
			
			$resources = $tree->resources;
			return view('resources.project',compact('user','projectname','resources'));
		}
	}
	public function savecalendardata(Request $request)
	{
		$rpath = 'data/resources';
		$filename = $rpath."/".$request->username;
	
		if(file_exists($filename))
		{
			$jsonstr = json_encode($request->data, JSON_PRETTY_PRINT);
			file_put_contents($filename, stripslashes($jsonstr));
		}
		else
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'An error occurred!'
			);
			echo Response::json($returnData, 500);
		}
	}
	public function calendardata($username)
	{
		$rpath = 'data/resources';
		$filename = $rpath."/".$username;
		$data = file_get_contents($filename);
		return $data;
		
		
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		//if(Auth::user()->IsAdmin())
		{
			$user = Auth::user();
			return view('resources.index',compact('user'));
		}
		//else
		//	abort(401); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
		dd('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
		dd('store');
		//self::storeobj($request);
    }
	public static function storeobj($request)
    {
        //
		Resource::create([
        'name' => $request['name'],
        'displayname' => $request['displayname'],
        'email' => $request['email'],
        'timezone' => $request['timezone'],
        ]);
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
		return Resource::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
	
	
}
