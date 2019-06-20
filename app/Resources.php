<?php
namespace App;
class Resources
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
}