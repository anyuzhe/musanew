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

Route::get('/', function () {
    echo phpinfo();
//    return view('welcome');
});


Route::get('/test', function () {

    \Illuminate\Support\Facades\Mail::to('68067348@qq.com')->send(new App\Mail\RecruitResumeLogEmail());
    return new App\Mail\RecruitResumeLogEmail();

    require_once(getMoodleRoot().'/user/lib.php');
    dd(getMoodleRoot());
    return view('test');
//    DB::getDoctrineColumn('users', 'id')->getType()->getName();
    DB::getDoctrineColumn('users', 'id')->getType()->getName();
});

Route::get('/course/jump', function () {
    requireMoodleConfig();
    global $CFG;
    $token = \App\Repositories\TokenRepository::getToken();
    $course_id = request('course_id');
    getCurl($CFG->wwwroot."/webservice/rest/server.php?wsfunction=enrol_self_enrol_user&wstoken={$token}&courseid={$course_id}&moodlewsrestformat=json");
    return redirect($CFG->wwwroot.'/login/course_jump.php?token='.$token."&course_id=".$course_id);
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::get('/company/data/statistics/excel/{type}', 'API\CompaniesController@dataStatisticsExcel');
Route::get('/company/data/statistics/detail/excel/{type}', 'API\CompaniesController@dataStatisticsDetailExcel');


