<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/sync/{user}/{project}','SyncController@SyncProject')->name('syncproject');
//Route::get('/sync/{user}/{project}','SyncController@Test')->name('syncproject');

Route::get('/chart/{name}', function () {
    return view('chart');
});
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/test','WidgetController@Test');

Route::get('/treeview/{user}/{project}','WidgetController@RequirementsTreeView')->name('requirementstreeviewdata');

Route::get('/dashboard/{user}/{project}','DashboardController@Show')->name('dashboard');

Route::get('/data/projects/{user}','DataController@GetProjects')->name('userprojects');
Route::get('/data/treeview/{user}/{project}','DataController@GetTreeViewData')->name('treeviewdata');

Route::get('/projects/{user_id}','ProjectController@UserProjects')->name('userprojects');

Route::get('/project/create', 'ProjectController@Create')->name('createproject');
Route::get('/project/update', 'ProjectController@Update')->name('updateproject');
Route::get('/project/delete/{id}', 'ProjectController@Delete')->name('deleteproject');
Route::get('/project/{name}','ProjectController@Project')->name('project');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/', 'HomeController@index')->name('default');
Route::get('/adminpanel/{user}', 'AdminController@index');