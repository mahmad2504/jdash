<?php
namespace App;
use App\ConsoleLog;
use App\Utility;
class Tj
{
	public static  function FlushProjectHeader($project)
	{
		$today = Utility::GetToday("Y-m-d");
		$start  =  $project->sdate;
		$end = $project->edate;
		$name = $project->summary;
		if($end == null) // No end defined so schedule from start or from today
		{
			if(strtotime($start) < strtotime($today))
				$header =  'project acs "'.$name.'" '.$today;
			else
				$header =  'project acs "'.$name.'" '.$start;
		}
		else
		{
			if(strtotime($start) > strtotime($today))
			{
				$header =  'project acs "'.$name.'" '.$start;
			}
			else
			{
				if(strtotime($end) > strtotime($today))
					$header =  'project acs "'.$name.'" '.$today;
				else
					$header =  'project acs "'.$name.'" '.$end;
			}
		}
		$header = $header." +48m"."\n";
		$header = $header.'{ '."\n";
		$header = $header.'   timezone "Asia/Karachi"'."\n";
		$header = $header.'   timeformat "%Y-%m-%d"'."\n";
		$header = $header.'   numberformat "-" "" "," "." 1 '."\n";
		$header = $header.'   currencyformat "(" ")" "," "." 0 '."\n";
		$header = $header.'   now 2017-07-21-01:00'."\n";
		$header = $header.'   currency "USD"'."\n";
		$header = $header.'   scenario plan "Plan" {}'."\n";
		$header = $header.'   extend task { text Jira "Jira"}'."\n";
		$header = $header.'} '."\n";
		return $header;
	}
	public static function FlushLeavesHeader($holidays)
	{
		$header = "";
		foreach($holidays as $holiday)
			$header = $header.'leaves holiday "holiday "'.$holiday."\n";
		return $header;
	}
	public static function FlushResourceHeader($resources)
	{
		$header =  "macro allocate_developers ["."\n";
		foreach($resources as $resource)
			$header = $header."   allocate ".$resource->name."\n";
 
		$header = $header."]"."\n";
		$header = $header.'resource abcds "Developers" {'."\n";
		foreach($resources as $resource)
		{
			$calendar = $resource->calendar;
			$header = $header.'    resource '.$resource->name.' "'.$resource->name.'" {'."\n";
			
			foreach($calendar as $holiday)
			{
				$header = $header.'      leaves annual '.$holiday."\n"; 
			}
			$header = $header.'       efficiency '.$resource->efficiency."\n"; 
			$header = $header.'    }'."\n";
		}
		$header = $header.'}'."\n";

		
		return $header;
	}
	function FlushTask($task)
	{	
		$tname = trim($task->extid)." ".substr($task->summary,0,10);
		$pos  = strpos($task->summary,'$');// Task summary with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("$","-",$task->summary);
		else
			$taskname = $task->summary;
		
		$pos  = strpos($taskname,';');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace(";","-",$taskname);
	
		$pos  = strpos($taskname,'(');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("(","-",$taskname);
		
		$pos  = strpos($taskname,'\\');// Task name with $ sign causes schedular error
		if($pos != FALSE)
			$taskname = str_replace("\\","-",$taskname);
		
		$taskname = trim($task->extid)." ".substr($taskname,0,15);
		$header = "";
		$spaces = "";
		for($i=0;$i<$task->level-1;$i++)
			$spaces = $spaces."     ";
		
			
		$tag = str_replace(".", "a", $task->extid);
		$header = $header.$spaces.'task t'.$tag.' "'.$taskname.'" {'."\n";
		
		if($task->isparent == 0)
			$header = $header.$spaces."   complete ".round($task->progress,0)."\n";4
		dheader = null;
		//$dheader = $this->DependsHeader($task);
		
		if($dheader != null)
			$header = $header.$spaces."   depends ".$dheader."\n";
		
		
		$sdate = $task->startconstraint;
		if($sdate != null)
		{
			if(strtotime($sdate) > strtotime(Utility::GetToday("Y-m-d")))
				$header = $header.$spaces."   start ".$sdate."\n";
		}
		if($task->isparent == 0)
		{
			if($task->priority >= 1000)
				$task->priority = 1000;
			
			if($task->priority >= 0)
				$header = $header.$spaces.'   priority '.$task->priority."\n";
			if(count($task->key)>0)
				$header = $header.$spaces.'   Jira "'.$task->key.'"'."\n";
			$remffort  = $task->estimate - $task->timespent;
			if($task->IsExcluded)
			{
				//echo $task->JiraId." Excluded".EOL;
				$remffort = 0;
			}
			//$remffort = $remffort1 + ($remffort1 - $remffort1*$task->Efficiency);
			//echo $task->Jira." ".$task->Resource." ".$remffort1." ".$remffort.EOL;
			if($remffort > 0)
			{
				$header = $header.$spaces."   effort ".$remffort."d"."\n";
				if(count($task->Resources) == 0) // Unallocated
				{
					if($task->IsParent == 0)
						$header = $header.$spaces."   allocate u"."\n";
				}
				else if(count($task->Resources) == 1) // Allocated to single resource
					$header = $header.$spaces."   allocate ".$task->Resources[0]->Name."\n";
				else
				{
					$team = $task->Resources;
					
					$header = $header.$spaces."   allocate ".$team[0]->Name." { alternative ";
					$delim = "";
					$str = "";
					for($i=1;$i<count($team);$i++)
					{
						$str = $str.$delim.$team[$i]->Name;
						$delim = ",";
					}
					$header = $header.$str." select order persistent }"."\n";
				}
			}
		}
		
		foreach($task->Children as $stask)
			$header = $header.$this->FlushTask($stask);
		
		$header = $header.$spaces.'}'."\n";
		return $header;
		
	}
}