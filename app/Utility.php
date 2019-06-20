<?php
namespace App;
use App\iCal;
class Utility
{
	public static function GetToday($format)
	{
		//return "2017-08-12";
		return Date($format);
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