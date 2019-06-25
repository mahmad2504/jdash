<?php
namespace App;
use App\iCal;
use Redirect,Response;
class Utility
{
	public static function GetProjectTree($user,$project)
	{
		$users = User::where('name',$user)->get();
    	if(count($users)==0)
    	{
			return 'user no found';
    	}
		$projects = Project::where('name',$project)->get();
    	if(count($projects)==0)
    	{
			return 'project no found';
    	}
    	$path = 'data/'.$user.'/'.$projects[0]->id;
		
		if(!file_exists($path."/"."tree"))
		{
			return 'project data no found, sync it';
		}
		$data = file_get_contents($path."/"."tree");
    	$tree = unserialize($data);
		return $tree;
	}
	public static function GetToday($format)
	{
		//return "2017-08-12";
		return Date($format);
	}
	public static function arrayToObject($d) 
	{
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object) array_map(__FUNCTION__, $d);
        }
        else {
            // Return object
            return $d;
        }
    }
	public static function GetHolidays($url,$sdate,$edate,$title='Holiday')
	{
		$cal = [];
		$iCal = new iCal($url);
		$events = $iCal->eventsByDateBetween($sdate,$edate);
		foreach ($events as $date => $event)
		{
			foreach ($event as $evt)
			{
				$evt->dateStart = date('Y-m-d',strtotime($evt->dateStart));
				$begin = new \DateTime($evt->dateStart);
				$end = new \DateTime($evt->dateEnd);
				$interval = \DateInterval::createFromDateString('1 day');
				$period = new \DatePeriod($begin, $interval, $end);
				foreach ($period as $dt) 
				{
					if($title == $evt->summary)
						$cal[$dt->format("Y-m-d")] = $dt->format("Y-m-d");
				}
			}
		}
		return $cal;
	}
}