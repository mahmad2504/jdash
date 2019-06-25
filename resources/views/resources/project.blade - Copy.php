@extends('layouts.app')

@section('csslinks')

<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/bootstrap-year-calendar.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/loading.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />

@endsection

@section('style')
.modal-header h3 { width: 90% }
.modal-content {
background-clip: border-box;
border: none !important;

.boxshadow 
{
  -moz-box-shadow: 3px 3px 5px #535353;
  -webkit-box-shadow: 3px 3px 5px #535353;       
  box-shadow: 3px 3px 5px #535353;
}
.roundbox
{  
  -moz-border-radius: 6px 6px 6px 6px;
  -webkit-border-radius: 6px;  
  border-radius: 6px 6px 6px 6px;
}

}
@endsection

@section('content')

<div style="width:80%; margin-left: auto; margin-right: auto; text-align:center;color:grey" class="center">
	<div style="display:none;" class="loading">Loading&#8230;</div>
	<div id="table">
	</div>
</div>

<div  style="display: none; overflow-y: auto;" class="modal" id="calendar-modal">
    <div  class="modal-dialog mw-100 w-75">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" onclick="$('#calendar-modal').hide();" <span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">
					
				</h4>
			</div>
			<div class="modal-body">
				<div style="width:100%;" id="calendar"></div>
			</div>
		</div>
	</div>

	<!-- Edit Modal -->

	<div  style="display: none;overflow-y: auto;" class="modal" id="event-modal">
		<div  class="modal-dialog">
			<div  class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">
						
					</h4>
				</div>
				<div class="modal-body">
					<input value="" name="event-index" type="hidden">
					<form class="form-horizontal">
						<div class="form-group">
							<label for="min-date" class="col-sm-4 control-label">Name</label>
							<div class="col-sm-12">
								<input name="event-name" class="form-control" type="text" value="fff">
							</div>
						</div> 
						<!-- <div class="form-group">
							<label for="min-date" class="col-sm-4 control-label">Location</label>
							<div class="col-sm-12">
								<input name="event-location" class="form-control" type="text">
							</div> 
						</div>-->
						<div class="form-group">
							<label for="min-date" class="col-sm-4 control-label">Dates</label>
							<div class="col-sm-12">
								<div class="input-group input-daterange" data-provide="datepicker">
									<input name="event-start-date" class="form-control" value="2012-04-05" type="text">
										<span class="input-group-text">to</span>

										<input name="event-end-date" class="form-control" value="2012-04-19" type="text">
										
										
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="save-event">
						Save
					</button>
				</div>
			</div>
		</div>
	</div>
	<div  style="display: none; overflow-y: auto;" class="modal" id="message-modal">
		<div style="" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" onclick="$('#message-modal').hide();" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">
						
					</h4>
				</div>
				<div class="modal-body">
					
					<div style="width:100%;" id="dd">Do you want to delete</div>
				</div>
			</div>
		</div>
	</div>
</div>
		
<div id="context-menu">
</div>
<script src="{{ asset('js/common.js') }}" ></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}" ></script>
<script src="{{ asset('js/bootstrap-year-calendar.min.js') }}" ></script>
<script src="{{ asset('js/resources/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/resources/calendar.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection

@section('script')
var userid = {{$user->id}};
var username =  "{{$user->name}}";
var projects = null;
async function OnSaveCalendar(data)
{
	$('.loading').show();
	console.log(data);
	await sleep(5000);
	$('.loading').hide();
}
$(document).ready(function()
{
	console.log("Loading Resource Page");
	var table = new Tabulator("#table", InitTabulator());
	InitCalendar(OnSaveCalendar);
	$('#calendar-modal').show();
	/*$.ajax({
		type:"GET",
		url:'/resources/data/calendar/'+username,
		cache: false,
		data:data,
		success: OnCalendarDataLoad,
		error: onfail
	});*/
})
@endsection