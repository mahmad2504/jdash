<?php
namespace App;
use App\ConsoleLog;
use App\ResourcesContainer;
class Task
{
	public $children = array();
	function __construct($level,$pextid,$pos,$summary=null,$query=null)
	{
		$this->summary = $summary;
		$this->query = $query;
		$this->key = '';
		$this->level = $level;
		$this->pos = $pos;
		$this->pextid = $pextid;
		if($pextid == 0)
			$this->extid = $level;
		else
			$this->extid = $this->pextid.".".$this->pos;
		
		$this->instancecount = 1;
		$this->storypoints = 0;
		$this->estimate = 0;
		$this->timeestimate = 0;
		$this->timespent = 0;
		$this->isparent = 0;
		$this->priority = 0;
		$this->ostatus ='';
		$this->status = 'OPEN';
		$this->progress = 0;
		$this->oissuetype = '';
		$this->issuetype = 'PROJECT';
		$this->assignee = null;
		$this->assignee = ResourcesContainer::Create('unassigned','unassigned','','');
	}
	
	function MapIssueType($issuetype)
	{
		if(($issuetype=='ESD Requirement')||($issuetype=='BSP Requirement'))
			return 'REQUIREMENT';
		
		if(($issuetype=='Workpackage')||($issuetype=='Project'))
			return 'WORKPACKAGE';
		
		if($issuetype=='Bug')
			return 'DEFECT';
		
		if($issuetype=='Epic')
			return 'EPIC';
		
		if(($issuetype=='Issue')||($issuetype=='Risk')||($issuetype=='Bug')||($issuetype=='Task')||($issuetype=='Story')||($issuetype=='Product Change Request')||($issuetype=='New Feature')||($issuetype=='Improvement'))
			return 'TASK';
		
		ConsoleLog::Send(time(),"Unmapped type=".$issuetype);
		exit();
		//
	}
	function MapStatus($status)
	{
		if(($status=='Requested')||($status=='Open')||($status == 'Committed')||($status == 'Draft')||($status == 'Withdrawn')||($status == 'Reopened')||($status == 'New'))
			return 'OPEN';
		if(($status=='Closed')||($status=='Resolved')||($status=='Implemented')||($status=='Validated')||($status=='Satisfied'))
			return 'RESOLVED';
		
		if($status=='Open')
			return 'OPEN';
		
		if(($status == 'In Analysis')||($status == 'In Progress')||($status == 'Code Review')||($status == 'In Review'))
			return 'INPROGRESS';
		ConsoleLog::Send(time(),"Unmapped status=".$status);
		exit();
	}

	public function ExecuteQuery($jiraconf)
	{
		$story_points = $jiraconf['storypoints']; // custom field
		
		global $estimation_method;
		global $unestimated_count;
		//ConsoleLog::Send(time(),$this->level." ".$this->key);
		if($this->query == null)
			return;
	      
		ConsoleLog::Send(time(),"Running Query ".$this->query);
		$fields = 'description,summary,status,issuetype,priority,assignee,issuelinks,';
		
		if($estimation_method == 1)
			$tasks = Jira::Search($this->query,1000,$fields.$story_points);
		else if($estimation_method == 2)
			$tasks = Jira::Search($this->query,1000,$fields.'timeoriginalestimate,aggregatetimespent');
		else
			$tasks = Jira::Search($this->query,1000,'timeoriginalestimate,aggregatetimespent,'.$fields.$story_points);
		
		
		$j=0;
		foreach($tasks as $key=>$task)
		{
			$ntask = new Task($this->level+1,$this->extid,$j++);
			$ntask->key = $key;
			$ntask->id = $task->id;
			$ntask->otatus = $task->fields->status->name;
			$ntask->status = $this->MapStatus($task->fields->status->name);
			$ntask->summary = $task->fields->summary;
			$ntask->oissuetype = $task->fields->issuetype->name;
			$ntask->issuetype = $this->MapIssueType($task->fields->issuetype->name);
			if(isset($task->fields->assignee))
			{
				$ntask->assignee = ResourcesContainer::Create($task->fields->assignee->name,
								  $task->fields->assignee->displayName,
								  $task->fields->assignee->emailAddress,
								  $task->fields->assignee->timeZone);
			}
			else
			{
				$ntask->assignee = ResourcesContainer::Create('unassigned','unassigned','','');
			}
				
			$ntask->query = null;
			if(($ntask->issuetype == 'REQUIREMENT')||($ntask->issuetype == 'WORKPACKAGE'))
				$ntask->query = 'issue in linkedIssues("'.$ntask->key.'","implemented by")';
			if($ntask->issuetype == 'EPIC')
				$ntask->query = "'Epic Link'=".$ntask->key;
			
			if(isset($task->fields->$story_points))
				$ntask->storypoints = $task->fields->$story_points;
			
			if(isset($task->fields->timeoriginalestimate))
				$ntask->timeestimate = round($task->fields->timeoriginalestimate/(28800),1);
			$ntask->estimate = $ntask->timeestimate;
			if($ntask->storypoints>0)
				$ntask->estimate = $ntask->storypoints;
			$ntask->priority = $task->fields->priority->id;
			$ntask->dependendson = [];
			foreach($task->fields->issuelinks as $issuelink)
			{
				if( strtolower($issuelink->type->name) == 'dependency')
				{
					if(isset($issuelink->outwardIssue))
					{
						$ntask->dependendson[] = $issuelink->outwardIssue->key;
					}
				}
			}
			//var_dump($ntask->dependendson);
			$ntask->timespent =  0;
			//ConsoleLog::Send(time(),"Estimate is ".$ntask->estimate);
			
			if($ntask->timespent > 0)
			{
				if($ntask->status == 'OPEN')
					$ntask->status = 'INPROGRESS';
			}
			//if($ntask->status == 'RESOLVED')
			//	$ntask->timespent = $ntask->estimate;
			//else
				
			if(isset($task->fields->aggregatetimespent))
				$ntask->timespent =  round($task->fields->aggregatetimespent/(28800),1);
			
			//echo $ntask->timespent;
			$this->isparent = 1;
			$this->children[] = $ntask;
			$buffer = '';
			if($ntask->issuetype == 'TASK')
				if($ntask->estimate == 0)
					$unestimated_count++;
			//for($i=0;$i<$ntask->level;$i++)
			//	$buffer .= '      ';
			//ConsoleLog::Send(time(),$buffer." ".$ntask->extid." ".$ntask->priority." ".$ntask->key."  ".$ntask->estimate."/".$ntask->storypoints." ".$ntask->query." ".$ntask->status);
		}
	}
}
?>