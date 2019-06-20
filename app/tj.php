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
}