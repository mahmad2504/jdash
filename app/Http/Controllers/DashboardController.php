<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Project;
use App\Task;
use App\Jira;
use App\ConsoleLog;
use Redirect,Response;
use Auth;


class DashboardController extends Controller
{
    //
    public function Show($user,$project)
    {
    	$users = User::where('name',$user)->get();
    	if(count($users)==0)
    	{
    		return Response::json(['error'=>'User does not exist']);
    	}
    	$projects = Project::where('name',$project)->get();
    	if(count($projects)==0)
    	{
    		return Response::json(['error'=>'Project does not exist']);
    	}
    	$user = $users[0]->name;
    	$project = $projects[0]->name;
    	return View('dashboard',compact('user','project'));

    }
}