@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/logger.css') }}" />
<link rel="stylesheet" href="{{ asset('css/loading.css') }}" />
<link rel="stylesheet" href="{{ asset('css/screen.css') }}" media="screen" />

@endsection
@section('style')
.modal-content {
background-clip: border-box;
border: none !important;
}
.progress {height: 5px;}

@endsection
@section('content')
<div class="container">
	<div class="loading">Loading&#8230;</div>
	<button rel="tooltip" title="Create New Project" id="add_project_button" class="btn btn-primary float-left" data-toggle="modal" data-target="#editmodal">Add Project</button>
	<br>
	<br>
	<br>
	<div class="card_container">
	</div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editmodal">
  <div class="modal-dialog">
	<div class="modal-content">

	  <!-- Modal Header -->
	  <div class="modal-header">
		<h4 class="modal-title"></h4>
		<button type="button" class="close" data-dismiss="modal">&times;</button>
	  </div>
	 
	  <!-- Modal body -->
	  <div class="modal-body">
		 <form name="edit_form" id='edit_form' action="#" method="get">
			<input id="editmodel_id"  type="hidden"  name="id" value="" readonly>
			<input id="editmodel_last_synced"  type="hidden"  name="last_synced" value="Never" readonly>
			<div class="d-flex form-group">
				<label style=" padding: 0; margin-top:3px;" for="jirauri">Server&nbsp&nbsp</label>
				<select class="form-control-sm" id="editmodal_jirauri" name="jirauri">
					@for($i=0;$i<count(config('jira.servers'));$i++)
						<option value="{{$i}}">{{config('jira.servers')[$i]['uri']}}</option>
					@endfor
				</select>&nbsp&nbsp
				<div class="form-group">
					<input id="editmodel_jiradependencies" style="margin-top:10px;" class="" type="checkbox" name="jira_dependencies" value="0">Jira Dependencies</input>
				</div>
			</div>
			<div class="d-flex form-group">
				<label style="padding:0;margin-top:3px;" for="name">Name&nbsp&nbsp&nbsp</label>
				<input id="editmodel_name" type="text" class="form-control-sm form-control" placeholder="Name" name="name">
			</div>
			<div class="form-group">
				<label for="name">Description</label>
				<textarea id="editmodel_description" class="form-control-sm form-control" rows="2" placeholder="Enter description" name="description"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="form-group">
				<label for="name">Query</label>
				<textarea id="editmodel_jiraquery" class="form-control-sm form-control" rows="2" placeholder="Enter Valid Jira Query" name="jiraquery"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="d-flex">
				<!--Date picker -->
				<div class="form-group">
					<label for="sdate">Start&nbsp&nbsp</label>
					<input class="form-control-sm" id="editmodal_sdate" type="date" name="sdate"></input>
				</div>
				<!--Date picker -->
				<div style="margin-left: 50px;" class="form-group">
					<label for="edate">End&nbsp&nbsp</label>
					<input class="form-control-sm" id="editmodal_edate" type="date" name="edate"></input>
				</div>
			</div>
			<div class="form-group d-flex">
				<label style="margin-top:5px;" for="name">Estimation</label>&nbsp&nbsp
				<select class="form-control-sm" id="editmodal_estimation" name="estimation">
					<option value="0">Mix</option>
					<option value="1">Story Points</option>
					<option value="2">Time</option>
				</select>
			</div>
			<small  id="editmodel_error" class="text-danger form-text"></small><br>
			<button id="editmodel_create_button" type="submit" class="btn btn-primary d-none">Create</button>
			<button id="editmodel_update_button" type="submit" class="btn btn-primary d-none">Update</button>
			<button id="editmodel_delete_button" class="btn btn-danger float-right d-none">Delete</button>
		</form>
	  </div>
	</div>
  </div>
</div>
<!-- End Edit Modal -->
<!-- Modal For Sync-->
<div class="modal fade" id="syncmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="overflow-y: initial;">
		<div class="modal-content">
			<div class="modal-body" style="width:500px;  height: 600px; overflow-y: auto; ">
				<div class="d-flex">
					<h1>Hello</h1>
					<button data-dismiss="modal" class="ml-auto close">×</button>
				</div>
				<hr>
				<button style="margin-left:20px;" url='' projectid='' id="sync">Sync</button>
				<button style="" id="rebuild">Rebuild</button>
				<button style="margin-left:40px;" id="close">Disconnect</button>
				<span style="float:right;margin-right:20px;margin-top:5px;" id="connection"></span>
				<hr>
				<div  style="display: block;margin-top: 20px;" id="log"></div>
			</div>
		</div>
	</div>
</div>
<!-- End Sync Modal -->
<script src="{{ asset('js/common.js') }}" ></script>
<script src="{{ asset('js/eventsource.min.js') }}" ></script>
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')

var userid = {{$user->id}};
var username =  "{{$user->name}}";
var projects = null;

function ShowEditModalButtons(create=0,update=0,delete_button=0)
{
	if(create == 0)
		$('#editmodel_create_button').addClass('d-none');
	else	
		$('#editmodel_create_button').removeClass('d-none');
	
	if(update == 0)
		$('#editmodel_update_button').addClass('d-none');
	else	
		$('#editmodel_update_button').removeClass('d-none');
	
	if(delete_button == 0)
		$('#editmodel_delete_button').addClass('d-none');
	else	
		$('#editmodel_delete_button').removeClass('d-none');
}

function SetEditModalFields(settings)
{
	$('#editmodel_id').val(settings.id);
	$('#editmodel_last_synced').val(settings.last_synced);
	$('#editmodel_name').val(settings.name);
	$('#editmodel_description').val(settings.description);
	$('#editmodel_jiraquery').val(settings.jiraquery);
	$('#editmodel_error').text(settings.error);
	$('#editmodal_estimation').prop('selectedIndex',settings.estimation);
	$('#editmodal_jirauri').prop('selectedIndex',settings.jirauri);
	$('#editmodal_sdate').val(settings.sdate);
	$('#editmodal_edate').val(settings.edate);
	$('#editmodel_jiradependencies').prop('checked', settings.jira_dependencies);
	//console.log($('#editmodel_jiradependencies').prop('checked'));
}

function AddCard(project,row)
{
	if(project.estimation == 1)
		estimation = 'Story Points';
	else if(project.estimation == 2)
		estimation = 'Time';
	else
		estimation = 'Story Points/Time';
	
	color='';
	if(project.dirty == 1)
		color='red';
	console.log(color);
	var col=$('<div class="col-sm-4">');
	var card=$('<div  class="card bg-white rounded bg-white shadow">');
	var progress=project.progress;
	var progress = '<div class="shadow-lg progress position-relative" style=""><div class="progress-bar" role="progressbar" style="background-color:green !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
			
	
	var headerstr ='<div  class="card-header border-success" style="background-color: #FFFAFA;">';
		headerstr +='<div class="d-flex">';
		headerstr   +='<img src="/images/greenpulse.gif" style="margin-left:-10px;margin-right:10px;width:20px;height:20px"></img>';
		headerstr   +='<h5 rel="tooltip" title="Project Name" id="card-name-'+project.id+'">'+project.name+'</h5>';
		headerstr +='</div>';
		headerstr +='<small style="margin-top:-10px;margin-left:20px;float:left" rel="tooltip" title="Estimation Method" class="float-left text-muted">'+estimation+'</small></div>';
		headerstr   +=progress;
	var header = $(headerstr);
	var body=$('<div class="card-body">');
	var desc=$('<p  rel="tooltip" title="Description" class="card-text" style="font-size:100%;">'+project.description+'</p>');
	var query=$('<p  rel="tooltip" title="Seed Jira Query" class="card-text" style="font-size:100%;">'+project.jiraquery+'</p>');
	var footer=$('<div class="card-footer bg-transparent"><i projectid="'+project.id+'" class="editbutton far fa-edit icon float-left" rel="tooltip" title="Edit Project" data-toggle="modal" data-target="#editmodal"></i><i projectid="'+project.id+'" rel="tooltip" title="Sync With Jira" class="syncbutton fas fa-sync icon float-left ml-1"></i><a href='+'"dashboard/'+username+'/'+project.name+'"><i projectid="'+project.id+'" rel="tooltip" title="Dashboard" class="icon fas fa-list-alt float-right"></i></a>'+'<p class="card-text" rel="tooltip" title="Last Sync time" style="color:'+color+';margin-left:70px;font-size:70%;">Last sync '+project.last_synced+'</p>'+'</div>');

	body.append(desc);
	body.append(query);
	card.append(header);
	card.append(body);
	card.append(footer);
	col.append(card);
	row.append(col);
}
function PopulateCard(projects)
{
	var j=1;
	var rownum = 1;
	$('.card_container').empty();
	var row=$('<div id="'+'row_'+rownum+'" class="row">');
	console.log("Creating Cards");
	$('.card_container').append(row);
	for(i=0;i<projects.length;i++)
	{
		AddCard(projects[i],row);
		if(j%3==0)
		{
			console.log("Appending");
			rownum++;
			row=$('<br><div id="'+'row_'+rownum+'" class="row">');
			
			rownum++;
			$('.card_container').append(row);
		}
		j++;
	}
}
function FindProject(id)
{
	for(var i=0;i<projects.length;i++)
	{
		if(projects[i].id == id)
			return projects[i];
	}
}

function OnEdit(event) // when edit button is pressed on card to show edit dialog
{
	event.preventDefault(); 
	console.log("Showing Edit Project Dialog");
	$element  = $(event.target);
	project  = FindProject($element.attr('projectid'));
	ShowEditModalButtons(0,1,1);
	$('.modal-title').text("Edit Project");
	
	settings = {};
	settings.id = project.id;
	settings.last_synced = project.last_synced;
	settings.name = project.name;
	settings.description = project.description;
	settings.jiraquery = project.jiraquery;
	settings.estimation = project.estimation;
	settings.jirauri =  project.jirauri;
	settings.sdate = project.sdate;
	settings.edate = project.edate;
	settings.jira_dependencies = project.jira_dependencies;
	settings.error = '';
	SetEditModalFields(settings);
}
function OnSyncModalClosed()
{
	var id = $('#sync').attr('projectid');
	closeConnection();
	$('.loading').show();
	LoadProjectsData('/data/projects/'+username, null,OnProjectsDataLoad);

}
function OnSync(event)
{
	event.preventDefault(); 
	$element  = $(event.target);
	project  = FindProject($element.attr('projectid'));
	
	console.log("Sync button pressed");
	$('#sync').attr('projectid', project.id);
	$('#sync').attr('url', "sync/"+username+"/"+project.name);
	Clear();
	$('#syncmodal').modal('show');	
}
function OnProjectsDataLoad(response)
{
	console.log("Projects Data Received");
	$('.loading').hide();
	projects = response;
	console.log(projects);
	PopulateCard(projects);
	$('.editbutton').on('click', OnEdit); 
	$('.syncbutton').on('click', OnSync);
}
function LoadProjectsData(url,data,onsuccess,onfail)
{
	$.ajax({
		type:"GET",
		url:url,
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}
function OnLoadFailure(response)
{
	$('.loading').hide();
	alert(response);
}
function OnUpdateProject(event)
{
	event.preventDefault(); 
	
	var form = $(event.target);
	if(ValidateFormData(form.serializeArray())==-1)
		return;
	var serializedData = form.serialize();
	console.log(serializedData);
	$('.loading').show();
	
	$.ajax(
	{
		type:"GET",
		url:'/project/update',
		data:form.serialize(),
		success: function(response){
			$('#editmodal').modal('hide');
			console.log(response);  
			LoadProjectsData('/data/projects/'+username, null,OnProjectsDataLoad);
			
		},
		error: function (error) 
		{
			if(error.status == 422)
			{
				if(error.responseJSON.errors.name !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.name[0]);

				if(error.responseJSON.errors.jiraquery !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.jiraquery[0]);

				console.log(error.responseJSON.errors.name[0]);
			}
			else
				alert("Unknown Error");
		}
	});
	return false;
}
function ValidateFormData(data)
{
	console.log(data);
	$(data).each(function(i, field)
	{
		data[field.name] = field.value;
	});
	$('#editmodel_error').text('');
	
	if(data['name'].trim().length == 0)
	{
		$('#editmodel_error').text('Project Name field cannot be empty');
		return -1;
	}
	if(data['jiraquery'].trim().length == 0)
	{
		$('#editmodel_error').text('Jira Query field cannot be empty');
		return -1;
	}
	console.log(data['sdate']);
	console.log(data['edate']);
	result = dates.compare(data['sdate'],data['edate']);
	console.log(result);
	if(result !== -1)
	{
		$('#editmodel_error').text('Project Ends before start');
		return -1;
	}
	
	return 0;
}
function OnCreateProject(event) 
{
	console.log("Creating Project");
	event.preventDefault(); 
	var form = $('#edit_form');
	if(ValidateFormData(form.serializeArray())==-1)
		return;
	
	var serializedData = form.serialize();
	$('.loading').show();
	$.ajax(
	{
		type:"GET",
		url:'/project/create',
		data:form.serialize(),
		success: function(response)
		{
			$('#editmodal').modal('hide');
			console.log(response);  
			LoadProjectsData('/data/projects/'+username, null,OnProjectsDataLoad);
		},
		error: function (error) 
		{
			if(error.status == 422)
			{
				if(error.responseJSON.errors.name !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.name[0]);

				if(error.responseJSON.errors.jiraquery !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.jiraquery[0]);

				console.log(error.responseJSON.errors.name[0]);
			}
			else
				$('#editmodel_error').text('Unknown Error');
		}
	});
}
function OnDeleteProject(event)
{
	console.log("Deleting Project");
	event.preventDefault(); 
	var formdata = $('#edit_form').serializeArray();
	
	var projectid = '';
	for(var i=0;i<formdata.length;i++)
	{
		if(formdata[i].name == 'id')
		{
			projectid = formdata[i].value;
			break;
		}
	}
	$('.loading').show();
	$.ajax(
	{
		type:"GET",
		url:'/project/delete/'+projectid,
		data:null,
		success: function(response)
		{
			$('#editmodal').modal('hide');
			console.log(response);  
			LoadProjectsData('/data/projects/'+username, null,OnProjectsDataLoad);
		},
		error: function (error) 
		{
			if(error.status == 422)
			{
				if(error.responseJSON.errors.name !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.name[0]);

				if(error.responseJSON.errors.jiraquery !== undefined)
					$('#editmodel_error').text(error.responseJSON.errors.jiraquery[0]);

				console.log(error.responseJSON.errors.name[0]);
			}
			else
				$('#editmodel_error').text('Unknown Error');
		}
	});
	return false;
}


$(document).ready(function()
{
	console.log("Loading Home Page");
	$('.navbar').removeClass('d-none');
	LoadProjectsData('/data/projects/'+username, null,OnProjectsDataLoad);
	$('#add_project_button').on('click', function(event)
	{
		console.log("Load Add Project dialog");
		$('.modal-title').text("New Project");
		
		settings = {};
		settings.id = '';
		settings.last_synced = 'Never';
		settings.name = '';
		settings.description = '';
		settings.jiraquery = '';
		settings.estimation = 0;
		settings.error = '';
		settings.sdate = MakeDate(dates.day(),dates.month()+1,dates.year());
		settings.edate = MakeDate(dates.day(),dates.month()+1,dates.year()+1);
		settings.jira_dependencies = 0;
		
		SetEditModalFields(settings);
		ShowEditModalButtons(1,0,0)
		
	});
	$('#editmodel_create_button').on('click', OnCreateProject);
	$("#edit_form").submit(OnUpdateProject);
	$('#editmodel_delete_button').on('click', OnDeleteProject);
	$('#syncmodal').on('hidden.bs.modal',OnSyncModalClosed);
	$("#editmodel_jiradependencies").on('click', OnJiraDependenciesClick);
	
});

function OnJiraDependenciesClick(event)
{
	 var element  = $(event.target);
	 console.log(element);
     if (element.prop('checked')){
          element.attr('value', 1);
     } else {
          element.attr('value', 0);
     }
}

@endsection

