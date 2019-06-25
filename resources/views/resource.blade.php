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

<script src="{{ asset('js/eventsource.min.js') }}" ></script>
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')
var userid = {{$user->id}};
var username =  "{{$user->name}}";
var projects = null;
$(document).ready(function()
{
	console.log("Loading Resource Page");
}
@endsection

