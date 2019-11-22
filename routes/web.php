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

use App\Models\Area;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Moodle\CourseCategory;
use App\Models\Recruit;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Repositories\EntrustsRepository;
use App\Repositories\RecruitRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\SkillsRepository;
use App\Repositories\TestsRepository;
use App\User;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    echo phpinfo();
//    return view('welcome');
});


Route::get('/test', function () {
    $recruits = Recruit::where('status', 4)->get();
    foreach ($recruits as $recruit) {
        foreach ($recruit->entrusts as $entrust) {
            if($entrust->status!=-1){
                $entrust->status = -1;
                $entrust->end_at = $recruit->end_at;
                $entrust->save();
                app()->build(RecruitRepository::class)->generateEndLog($recruit, $entrust);
            }
        }
    }
//    dd(preg_match('/^(\w*(?=\w*\d)(?=\w*[A-Za-z])\w*){6,16}$/', '2123_!@1231'));
    dd(preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&^*()_+=-])[A-Za-z\d$@$!%*#?&^*()_+=-]{8,16}$/', 'a233221'));
//    dd(preg_match('/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{6,16}/', '2123!ddddddA1'));
    dd(env('APP_DEBUG'));
    $testres = app()->build(TestsRepository::class);
    $resumeres = app()->build(ResumesRepository::class);
    $resumes = Resume::all();
    foreach ($resumes as $obj) {
        $v = $resumeres->getEducation(ResumeEducation::where('resume_id', $obj->id)->get());
        if($v){
            dump($v);
            $obj->education = $v;
            $obj->save();
        }
    }
    dd($testres->getTestData(Course::find(7), \App\Models\User::find(55)));
    dd(date('Y-m-d H:i:s', strtotime("Tue Oct 01 2019 00:00:00 GMT+0800 (中国标准时间)")));
    $resumeres = app()->build(ResumesRepository::class);
    dd($resumeres->mixResumes(Resume::find(272),Resume::find(173)));
//    $testres = app()->build(EntrustsRepository::class);
//    dd($testres->getEntrustsAmount(Entrust::all()));
//    dd(Resume::create([]));
    dd($testres->getTestData(Course::find(7), \App\Models\User::find(43)));
    dd(SkillsRepository::getTestCateId());
    $first = DB::connection('musa')->table('company_job_recruit')
        ->select(DB::raw('id, company_id, job_id, 0, need_num, done_num, resume_num, leading_id, created_at'))
//        ->select('id','company_id','0')
        ->where('status', 1);

    $test = DB::connection('musa')->table('company_job_recruit_entrust')
        ->select(DB::raw('id, third_party_id, job_id, company_job_recruit_id, 0, done_num, resume_num, 0, created_at'))
        ->where('status', 1)
//        ->whereNull('last_name')
        ->union($first)
        ->orderBy('id', 'desc')
        ->get();
    dd($test);
    $r = Area::find(110102);
    dd($r->parent);
    $r = Resume::find(41);
    $r->birthdate = "1987-08-01";
    $r->save();
    dd(strlen($r->birthdate));
    dd($r);
    $file = Storage::disk(config('voyager.storage.disk'))->get('/resumes/doc.NO.4.docx');
    $data = [
        'filename'=>'doc.NO.4.docx',
        'content'=>base64_encode((string)$file),
        'need_avatar'=>0
    ];
    $headers = [
        'X-API-KEY: izrNtgTds8XEi3fwvJu88klg6X9Im9Jx'
    ];
    $url = "https://www.belloai.com/v2/open/resume/parse";
    $res = http_post_json($url, json_encode($data, 256) ,$headers);

    if($res[0]=='200'){
        $array = json_decode($res[1], true);
        dd($array);
    }
    dd($res);    $data = RecruitResumeLog::where('company_job_recruit_resume_id',13)->orderBy('id','asc')->groupBy('company_job_recruit_resume_id')->get()->toArray();
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

Route::get('/resume/uploadTest', 'Admin\ResumesController@uploadTest');
Route::get('/resume/{id}', 'Admin\ResumesController@show');
Route::get('/resume/dumpPdf/{id}', 'Admin\ResumesController@dumpPdf');

Route::get('/company/data/statistics/excel/{type}', 'API\CompaniesController@dataStatisticsExcel');
Route::get('/company/data/statistics/detail/excel/{type}', 'API\CompaniesController@dataStatisticsDetailExcel');


