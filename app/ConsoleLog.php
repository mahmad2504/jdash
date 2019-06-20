<?php
namespace App;
use App\ConsoleLog;
class ConsoleLog 
{       
    static $count;     
    public static function Send($id , $msg) 
    {
    	$msg = str_replace('"', "'", $msg);
    	
		echo "id: $id" . PHP_EOL;
		echo "data: {\n";
		echo "data: \"msg\": \"$msg\", \n";
		echo "data: \"id\": $id\n";
		echo "data: }\n";
		echo PHP_EOL;
		ob_flush();
		flush();
	}
} 


?>