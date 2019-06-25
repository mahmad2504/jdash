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

class TestController extends Controller
{
	public function Test()
	{
		$myRequest = new \Illuminate\Http\Request();
		$myRequest->setMethod('POST');
		$myRequest->request->add(['foo' => 'bar']);
		\App\Http\Controllers\ResourceController::storeobj(null);
		
	}
}
