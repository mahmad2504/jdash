@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/screen.css') }}" media="screen" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/loading.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />


@endsection
@section('style')
.progress {height: 10px;}
@endsection
@section('content')
<div class="container">
	<div class="loading">Loading&#8230;</div>
	<p id='description'>Description</p>
	<table id="treetable" style="display:none;" class="table">
		<caption style="caption-side:top;text-align: center">
		  <a href="#"  onclick="jQuery('#treetable').treetable('expandAll'); return false;">Expand all</a>&nbsp|
		  <a href="#" onclick="jQuery('#treetable').treetable('collapseAll'); return false;">Collapse all</a>
		</caption>
		<col style="width:45%;border-right:1pt solid lightgrey;">
		<col style="width:10%;border-right:1pt solid lightgrey;">
		<col style="width:20%;border-right:1pt solid lightgrey;">
		<col style="width:10%;border-right:1pt solid lightgrey;">
		<col style="width:16%;border-right:1pt solid lightgrey;">
		
		<thead style="background-color: SteelBlue;color: white;font-size: .8rem;">
		  <tr>
			<th>Title</th>
			<th>Jira</th>
			<th>Blockers</th>
			<th id='estimatecolumn'></th>
			<th>Progress</th>
		  </tr>
		</thead>
		<tbody id="tablebody">
		</tbody>
		
	</table>
	<div id="legend">
		<span>Project<span style="margin-top:20px;padding:5px;" class="PROJECT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Requirement<span style="margin-top:20px;padding:5px;" class="REQUIREMENT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Epic<span style="margin-top:20px;padding:5px;" class="EPIC">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Task<span style="margin-top:20px;padding:5px;" class="TASK">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Defect<span style="margin-top:20px;padding:5px;" class="DEFECT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Workpackage<span style="margin-top:20px;padding:5px;" class="WORKPACKAGE">&nbsp&nbsp&nbsp</span></span>
	</div>
</div>
<script src="{{ asset('js/jquery.treetable.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')
var userid = @if(Auth::check()) {{ Auth::user()->id}}; @else -1; @endif
var username =  @if(Auth::check()) "{{ Auth::user()->name}}";@else null; @endif

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
function OnProjectDataReceived(response)
{
	console.log(response.description);
	$('#description').append(response.description);
	if(response.estimation == 1)
		header = 'Story Points';
	if(response.estimation == 2)
		header = 'Time Estimates';
	if(response.estimation == 0)
		header = 'Estimates';
	$('#estimatecolumn').append(header);
}

$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"/dashboard/{{$user}}/{{$project}}");
	LoadProjectsData('/project/{{$project}}',null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('treeviewdata',[$user,$project]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			console.log(response);
			ShowTree(JSON.parse(response)) ;
		},
		error: function (error) 
		{
			$('.loading').hide();
			console.log(error);  
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){window.location.href = "/";})
		}
	});
	function round(value, precision) 
	{
		var multiplier = Math.pow(10, precision || 0);
		return Math.round(value * multiplier) / multiplier;
	}
	function ShowTree(response)
	{
		console.log(response);
		var data = 
		[
			['10','','file','Title file1','http://www.google.com','HMIP','23','25'],
			['10-1','10','file','Title file2','http://www.google.com','HMIP','23','25'],
			['10-1-1','10-1','file','Title file2','http://www.google.com','HMIP','23','25'],
			['10-1-1-1','10-1-1','file','Title file2','http://www.google.com','HMIP','23','25']
		];
		data = response;
		
		for (var exitid in data)
		{
			var row = data[exitid];
			var id = row['extid'];
			var pid = row['pextid'];
			var _class =row['issuetype'];
			var title=row['summary'];
			var link=row['jiraurl'];
			var linktext=row['key'];
			var estimate=round(row['estimate'],1);
			var progress=round(row['progress'],1);
			var status=row['status'];
			var priority=row['priority'];
			var blockedtasks=row['blockedtasks'];
			var blockedtasksstr = '';
			var del ='';
			for (var key in blockedtasks)
			{
				blockedtasksstr += del+"<a href='"+link+"/browse/"+key+"'>"+key+"</a>";
				del="&nbsp&nbsp";
			}
			var progressbar_animation_class = 'progress-bar-striped progress-bar-animated';
			
			if(_class == 'TASK')
			{
				if(status == 'OPEN')
					_class = 'TASK_OPEN';
				if(status == 'RESOLVED')
				{
					_class = 'TASK_RESOLVED';
				}
			}
			color = '';
			progressbar_color = 'green';
			if(status == 'RESOLVED')
			{
				progressbar_animation_class = '';
				progressbar_color = 'darkgreen';
			}
			else
			{
				if(priority == 1)
					color = 'red';
				if(priority == 2)
					color = 'orange';
			}
			var rowstr = '<tr ';
			rowstr += "data-tt-id='"+id+"' ";

			if(pid != '')
				rowstr += "data-tt-parent-id='"+pid+"'";
			
			blockers ='fff';
					
			rowstr += "style='border-bottom:1pt solid grey;' class='branch expanded'>";
			rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'><span class='"+_class+"'>";
			rowstr += id+" "+title+"</span></td>";
			rowstr += "<td><a style='font-size:.6rem; color:"+color+";' href='"+link+"/browse/"+linktext+"'>"+linktext+'</a></td>';
			rowstr += "<td  style='font-size:.6rem;'>"+blockedtasksstr+"</td>";
			rowstr += "<td>"+estimate+"</td>";
			var str = '<div class="shadow-lg progress position-relative" style="background-color:grey"><div class="progress-bar '+progressbar_animation_class+'" role="progressbar" style="background-color:'+progressbar_color+' !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
			
			
			rowstr += "<td>"+str+"</td>";
			rowstr += "</tr>";
			//console.log(rowstr);
			$('#tablebody').append(rowstr);
		}
		$("#treetable").treetable({ expandable: true });
		$("#treetable").show();
		$("#legend").show();
		$("#treetable").treetable("expandNode", "1");
	}
})
@endsection