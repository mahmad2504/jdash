<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Project;
use App\Task;
use App\Jira;
use App\Tj;
use App\ConsoleLog;
use App\Utility;
use Redirect,Response;
use Auth;

class DataController extends Controller
{
    //
    private $treedata = [];
    private $jiraurl = '';
	private $blockedtasks = array();
	public function GetProjects($user)
	{
		$users = User::where('name',$user)->get();
    	if(count($users)==0)
    	{
    		return Response::json(['error'=>'User does not exist']);
    	}
    	$projects = Project::where('user_id',$users[0]->id)->get();
    	if(count($projects)==0)
    	{
    		return Response::json(['error'=>'Project does not exist']);
    	}
		
		return Response::json($projects);
	}
	private function GetProjectTree($user,$project)
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
    	$path = 'data/'.$user.'/'.$projects[0]->id;
		if(!file_exists($path."/"."tree"))
		{
			return null;
		}
		$data = file_get_contents($path."/"."tree");
    	$tree = unserialize($data);
		
		//$cal = Utility::GetHolidays("https://calendar.google.com/calendar/ical/mumtazahmad2504%40gmail.com/private-fe2463be8629ea4c59bc9374e7f8ac04/basic.ics",$tree->sdate,$tree->edate,'Holiday');
		//dd($cal);
		return $tree;
	}
    public function GetTreeViewData($user,$project)
    {
    	$tree=$this->GetProjectTree($user,$project);
		if($tree==null)
		{
			$returnData = array(
				'status' => 'error',
				'message' => 'An error occurred!'
			);
			return Response::json($returnData, 500);
		}
		$this->jiraurl = $tree->jiraurl;
    	//echo json_encode($tree);
    	$this->FormatForTreeView($tree);
		//var_dump($this->treedata);
		foreach($this->blockedtasks as $task)
		{
			$ids = explode(".",$task->extid);
			$last = '';
			$del = '';
			foreach($ids as $id)
			{
				$parentid = $last.$del.$id;
				$del = '.';
				$last = $parentid;
				if($parentid == $task->extid)
					break;
				//echo $parentid."<br>";
				//var_dump($this->treedata[$parentid]);
				if(!array_key_exists('blockedtasks',$this->treedata[$parentid]))
					$this->treedata[$parentid]['blockedtasks'] = array();
				$this->treedata[$parentid]['blockedtasks'][$task->key] = $task->key;
			}
		}
		//echo json_encode($this->blockedtasks);
		//$this->treedata = array_values($this->treedata);
    	echo json_encode($this->treedata);
    }
    public function FormatForTreeView($task)
    {
    	$row = [];
		if(($task->priority == 1)&&($task->status != 'RESOLVED'))
			$this->blockedtasks[$task->key] = $task;
		
    	$row['extid'] = $task->extid;
    	$row['pextid'] = $task->pextid;
    	$row['issuetype'] = $task->issuetype;
    	$row['summary'] = $task->summary;
    	$row['jiraurl'] = $this->jiraurl;
    	$row['key'] = $task->key;
    	$row['estimate'] = $task->estimate;
    	$row['progress'] = $task->progress;
		$row['status'] = $task->status;
		$row['priority'] = $task->priority;
		
    	$this->treedata[$task->extid] = $row;
    	foreach($task->children as $ctask)
    		$this->FormatForTreeView($ctask);
    }
    
}
