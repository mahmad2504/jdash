<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Redirect,Response;

use App\Project;
use Auth;
class ProjectController extends Controller
{
	public function Project($name)
    {
		$projects = Project::where('name',$name)->get();
		if(count($projects) > 0)
			return Response::json($projects[0]);
		else
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'No record found'
			);
			return Response::json($returnData, 500);
		}
    }
    public function UserProjects($user_id)
    {
        $projects = Project::where('user_id',$user_id)->get();
        return Response::json($projects);
    }
    public function Create(Request $request)
    {  
       $request->validate([
             'name' => ['required','string','unique:projects'],
             'jiraquery' => ['required'],
			 'jirauri' => ['required'],
			 'sdate' => ['required'],
			 'edate' => ['required']
        ]);
       // , 'string','alpha_dash', 'max:255','unique:name']
        if (strlen($request->description)==0)
            $request->description = 'No Description';
		
		if(!isset($request->jira_dependencies))
			$request->jira_dependencies = 0;
		
		
		$user_id = Auth::user()->id;
        $project   =   Project::updateOrCreate(['id' => ''],
                    ['user_id' => $user_id, 
					 'name' => $request->name,
					 'description' => $request->description,
					 'jiraquery' => $request->jiraquery,
					 'estimation' => $request->estimation,
					 'last_synced' => $request->last_synced,
					 'jirauri' => $request->jirauri,
					 'sdate' => $request->sdate,
					 'edate' => $request->edate,
					 'jira_dependencies' =>$request->jira_dependencies,
					]);
       return Response::json($project);
    }
    public function Update(Request $request)
    {  
        $projects = Project::where('name', $request->name)->get();
        if((count($projects) == 1)&&($projects [0]->id == $request->id))
        {
			
            $request->validate([
                 'name' => ['required','string'],
                'jiraquery' => ['required'],
				'jirauri' => ['required'],
				'sdate' => ['required'],
				'edate' => ['required']
            ]);
        }
        else
        {
			
            $request->validate([
             'name' => ['required','string','unique:projects'],
             'jiraquery' => ['required'],
			 'jirauri' => ['required'],
			 'sdate' => ['required'],
			 'edate' => ['required']
            ]);
        }
		
        if (strlen($request->description)==0)
            $request->description = 'No Description';
        if(!isset($request->jira_dependencies))
			$request->jira_dependencies = 0;
		
        $user_id = Auth::user()->id;
        $project   =   Project::updateOrCreate(['id' => $request->id],
                    ['user_id' => $user_id, 
					 'name' => $request->name,
					 'description' => $request->description,
					 'jiraquery' => $request->jiraquery,
					 'estimation' => $request->estimation,
					 'jirauri' => $request->jirauri,
					 'sdate' => $request->sdate,
					 'edate' => $request->edate,
					 'jira_dependencies' =>$request->jira_dependencies,
					 'dirty' => 1,
					]);
					
		$projects = Project::where('id', $request->id)->get();
		return Response::json($projects[0]);
    }
	public function Delete($id)
	{
		$project = Project::find($id);
		$project->delete();
	}
}
