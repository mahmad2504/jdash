<?php
namespace App;
class ResourcesContainer
{
	private static $resources=[];
	public static function Add($resource)
	{
		if(array_key_exists($resource->name,self::$resources))
			return self::$resources[$resource->name];
		else
		
			self::$resources[$resource->name] = $resource;
		return self::$resources[$resource->name];
	}
	public static function Create($name,$displayname,$email,$timezone)
	{
		$resource = new \StdClass();
		$resource->name = $name;
		$resource->email = $email;
		$resource->displayname = $displayname;
		$rpath = 'data/resources';
		$filename = $rpath."/".$resource->name;
		$resource->vacations_data = $filename;
		$resource->efficiency = 1.0;
		$resource->rate =  1.0;
		$resource->team = [];
		$resource->timezone = $timezone;
		return [self::Add($resource)];
	}
	public static function Get()
	{
		return array_values(self::$resources);
	}
}