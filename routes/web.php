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

use App\Models\RecruitResumeLog;

Route::get('/', function () {
    echo phpinfo();
//    return view('welcome');
});


Route::get('/test', function () {
    $data = RecruitResumeLog::where('company_job_recruit_resume_id',13)->orderBy('id','asc')->groupBy('company_job_recruit_resume_id')->get()->toArray();
    dd($data);
    dd(1);
    $log = \App\Models\RecruitResumeLog::where('id',1)->orderBy('id', 'desc')->first();
    return new \App\Mail\RecruitResumeLogEmail([$log]);
    sendLogsEmail([$log]);
    die(2);
    foreach ($logs as $log) {
        $recruitResume = $log->recruitResume;
        $log->company_job_recruit_id = $recruitResume->company_job_recruit_id;
        $log->company_job_recruit_entrust_id = $recruitResume->company_job_recruit_entrust_id;
        $log->job_id = $recruitResume->job_id;
        $log->save();
    }
    die(1);
    $rs = \App\Models\Recruit::all();
    foreach ($rs as $r) {
        if(!$r->leading_id)
            continue;
        foreach ($r->entrusts as $entrust) {
            $entrust->leading_id = $r->leading_id;
            $entrust->save();
        }
    }
//    \Illuminate\Support\Facades\Mail::to('68067348@qq.com')->send(new App\Mail\RecruitResumeLogEmail(\App\Models\RecruitResumeLog::find(1)));
    return new App\Mail\RecruitResumeUntreatedEmail(\App\Models\RecruitResume::find(1));

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
Route::any('/api/admin/login', 'Voyager\VoyagerAuthController@frontPostLogin');

Route::get('/resume/{id}', 'Admin\ResumesController@show');
Route::get('/resume/dumpPdf/{id}', 'Admin\ResumesController@dumpPdf');

Route::get('/company/data/statistics/excel/{type}', 'API\CompaniesController@dataStatisticsExcel');
Route::get('/company/data/statistics/detail/excel/{type}', 'API\CompaniesController@dataStatisticsDetailExcel');


