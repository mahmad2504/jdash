<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Project;
use App\Task;
use App\Jira;
use App\ConsoleLog;
use App\iCal;
use Redirect,Response;
use Auth;


class SyncController extends Controller
{
    //
    private $jiraurl = '';
	private $jiraconfig = null;
	private $tasks = [];
    public function SyncProject(Request $request,$user,$project)
    {
		global $estimation_method;
    	header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		set_time_limit(300);

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
		/// 
		
		$project = $projects[0];
		
		//var_dump($project->jirauri);
		$this->jiraurl = config('jira.servers')[$project->jirauri]['uri'];
		$this->jiraconfig = config('jira.servers')[$project->jirauri];
		//
		
    	$path = 'data/'.$user.'/'.$project->id;

    	$rebuild = $request->input("rebuild");
   		if($rebuild == 1)
   		{
   			ConsoleLog::Send(time(),"Cleaning all Jira filter data");
   			array_map('unlink', glob($path."/*"));
   		}
    	if(!file_exists($path))
    		mkdir($path, 0, true);

    	$estimation_method = $project->estimation;
    	$seed = new Task(1,0,0,$project->name,$project->jiraquery);


    	ConsoleLog::Send(time(),"Syncing Now");
    	
    	Jira::Initialize($this->jiraurl,'himp','hmip',$path);

    	$this->Populate($seed);
    	$this->ComputeStatus($seed);
    	$this->ComputeEstimate($seed);
    	$this->ComputeTimeSpent($seed);
    	$this->ComputeProgress($seed);
		$this->FindDuplicates($seed);
    	global $unestimated_count;
    	$seed->unestimated_count = $unestimated_count;
    	$seed->jiraurl = $this->jiraurl;
    	//PrintTree($seed);
		
		$totalestimate = 0;
		$totaltimespent = 0;
		
		foreach($this->tasks as $task)
		{
			$totalestimate += $task->estimate;
			$totaltimespent += $task->timespent;
		}
		$totalprogress=$totalestimate/$totaltimespent;
		$totalprogress = round($totaltimespent/$totalestimate*100,1);
		//ConsoleLog::Send(time(),$totalestimate." ".$seed->estimate);
		//ConsoleLog::Send(time(),$totaltimespent." ".$seed->timespent);
		//ConsoleLog::Send(time(),$totalprogress." ".$seed->progress);
		
		$seed->progress = $totalprogress;
		$seed->estimate = $totalestimate;
		$seed->timespent = $totaltimespent;
		
		$seed->sdate = $project->sdate;
		$seed->edate = $project->edate;
		
    	$data = serialize($seed);
    	file_put_contents($path."/"."tree", $data);
		$last_synced = date ("Y/m/d H:i" , filemtime($path."/"."tree"));
		Project::where('id', $project->id)->update(array('last_synced' => $last_synced,'dirty'=>0, 'progress'=>$seed->progress));
    	ConsoleLog::Send(time(),"Sync Completed Successfully");
		
		
		
		//$this->PrintTree($seed);
		//echo "Unestimated = ".$unestimated_count;
    	//echo($user);
    	//echo($project);
    }
	public function Test()
	{
		  header("Content-Type: text/event-stream");
		  header("Cache-Control: no-store");
		  header("Access-Control-Allow-Origin: *");
		  
		  header('Cache-Control: no-cache');
		  while(1)
		  {
			  ConsoleLog::Send(time(),"Sync Completed Successfully");
			  sleep(1);
		  }
		  ConsoleLog::Send(time(),"Sync Completed Successfully");
		  ConsoleLog::Send(time(),"Sync Completed Successfully");

	}
	function Populate($task)
	{
		$task->ExecuteQuery($this->jiraconfig);
		foreach($task->children as $stask)
			$this->Populate($stask);
	}
	function FindDuplicates($task)
	{
		if($task->isparent == 0)
		{
			if(array_key_exists($task->key,$this->tasks))
			{
				ConsoleLog::Send(time(),'Warning::'.$task->key." Duplicate in plan");
				$this->tasks[$task->key]->instancecount++;
			}
			else
			{
				$this->tasks[$task->key]=$task;
			}
		}
		foreach($task->children as $stask)
			$this->FindDuplicates($stask);
	}
	function ComputeProgress($task)
	{
		$estimate = $task->estimate;
		if($estimate == 0)
			$estimate = 1;
				
		$task->progress = round($task->timespent/$estimate*100,1);
		//echo $task->progress." ".$task->timespent." ".$estimate."\r\n";
		if($task->progress > 100)
			$task->progress = 100;
		
		if($task->status == 'RESOLVED')
			$task->progress = 100;
		
			
		$children = $task->children;
		
		foreach($task->children as $child)
			$this->ComputeProgress($child);
	}
	function ComputeTimeSpent($task)
	{
		if($task->isparent == 0)
		{
			if($task->status == 'RESOLVED')
				$task->timespent = $task->estimate;
			return $task->timespent;
		}
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeTimeSpent($child);
		
		$task->timespent = $acc;
		if($task->status == 'RESOLVED')
			$task->timespent = $task->estimate;
		
		return $task->timespent;
	}
	function ComputeEstimate($task)
	{
		if($task->isparent == 0)
			return $task->estimate;
		$children = $task->children;
		$acc = 0;
		foreach($task->children as $child)
			$acc += $this->ComputeEstimate($child);
		
		$task->estimate = $acc;
		return $task->estimate;
	}
	function ComputeStatus($task)
	{
		if($task->isparent == 0)
		{
			if($task->status == 'OPEN')
				if($task->timespent > 0)
					$task->status = 'INPROGRESS';
				
			return $task->status;
		}
		$children = $task->children;
		foreach($task->children as $child)
		{
			$status = $this->ComputeStatus($child);
			$status_array[$status] = 1;
		}
		
		if (array_key_exists("INPROGRESS",$status_array))
			$task->status = "INPROGRESS";
		else if (array_key_exists("OPEN",$status_array))
			$task->status = "OPEN";
		else if (array_key_exists("RESOLVED",$status_array))
			$task->status = "RESOLVED";
		
		return $task->status;
	}
	function PrintTree($task)
	{
		if($task->instancecount > 1)
			ConsoleLog::Send(time(),$task->key." ".$task->instancecount);
		
		//$spaces ='';
		//for($i=1;$i<$task->level;$i++)
		//	$spaces .= '  ';
		//ConsoleLog::Send(time(),$spaces." ".$task->extid." ".$task->priority." ".$task->key."  ".$task->timespent."/".$task->estimate." ".$task->progress."%"."  ".$task->query." ".$task->status);
		foreach($task->children as $child)
			$this->PrintTree($child);
			
		
	}
}
