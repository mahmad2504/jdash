<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
	 protected $fillable = [
        'name', 'description', 'jiraquery','user_id','last_synced','estimation','jirauri','sdate','edate','jira_dependencies','dirty'
		
    ];
	public function user()
	{
		$this->belongsTo('App\User');
	}
	
}
