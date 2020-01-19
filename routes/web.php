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
use App\Models\Company;
use App\Models\CompanyLog;
use App\Models\CompanyPermission;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Conglomerate;
use App\Models\Course;
use App\Models\Entrust;
use App\Models\Job;
use App\Models\Moodle\CourseCategory;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Models\UserBasicInfo;
use App\Repositories\EntrustsRepository;
use App\Repositories\RecruitRepository;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\SkillsRepository;
use App\Repositories\TestsRepository;
use App\Repositories\UserRepository;
use App\User;
use App\ZL\ORG\Excel\ExcelHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    echo phpinfo();
//    return view('welcome');
});


Route::get('/test', function () {
    set_time_limit(0);
    $third_party_recruit_entrust_ids = Entrust::where('third_party_id', 20200001)->pluck('id')->toArray();
    $res = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)->get();
    $data = [];
    $res->load('resume');
    $res->load('job');
    $rrs =  app()->build(RecruitResumesRepository::class);
    foreach ($res as $re) {
        $rrs->addFieldText($re);
        $_data = [];
        $_data[] = $re->resume->name;
        $_data[] = $re->job->name;
        $_data[] = $re->status_str;
        $data[] = $_data;
//        dump($re->resume->name.'--'.$re->job->name);
    }
    $excelHelper = new ExcelHelper();
    $excelHelper->dumpExcel(['名字','职位名称','招聘状态'],$data,'数据', "数据");
    dd(1);
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-31 23:59:59');
    $res = RecruitResume::whereIn('company_job_recruit_entrust_id', $third_party_recruit_entrust_ids)
        ->where('created_at','>', $start_date)->where('created_at','<=', $end_date)->get();
    foreach ($res as $v) {
        dump($v->job->name);
        dump($v->resume->name);
        dump((string)$v->created_at);
    }
    dd($res);

    $es = Entrust::where('status', -1)->get();
    foreach ($es as $e) {
        $r = $e->recruit;
        $r->status = 4;
        $r->end_at = $e->end_at;
        $r->save();
    }
    dd(1);
    $res = Entrust::select(DB::raw('company_job_recruit_id,SUM(done_num) as total_done_num,SUM(resume_num) as total_resume_num,SUM(new_resume_num) as total_new_resume_num'))->where('company_job_recruit_id', 9)->first()->toArray();
    dd($res);
    $users = \App\Models\User::where('id',110)->get();
    foreach ($users as $user) {
        if($info = $user->info){
            $info->created_at = date('Y-m-d H:i:s', $user->timecreated);
            dd($info->created_at);
            $info->save();
        }
    }
    dd(1);
//        【·】【！】
//
//【@】【#】【$】【%】【^】【&】【*】【(】【)】【-】【+】【/】【.】【￥】【=】【\】【|】【{】【}】
//【》】【《】【，】【。】【？】
//    $password = '1234A!';
//    $res = preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!！%*#?&^*()_+=-·?？.<>《》,，.。￥|{}\\\\\/])[A-Za-z\d@$!！%*#?&^*()_+=-·?？.<>《》,，.。￥|{}\\\\\/]{6,16}$/', $password);
//    dd($res);
    requireMoodleConfig();

    global $CFG;
    require_once($CFG->dirroot . '/user/editlib.php');
    require_once($CFG->libdir . '/authlib.php');
    require_once(getMoodleRoot().'/login/lib.php');
    function userSignup(&$user, $notify=true, $confirmationurl = null) {
        global $CFG, $DB, $SESSION;
        require_once(getMoodleRoot().'/user/profile/lib.php');
        require_once(getMoodleRoot().'/user/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference('auth_email_wantsurl', $SESSION->wantsurl, $user);
        }

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();
        ##发送确认邮箱
//        if (! send_confirmation_email($user, $confirmationurl)) {
//            print_error('auth_emailnoemail', 'auth_email');
//        }

//        if ($notify) {
        return true;
//        } else {
//            return true;
//        }
    }
    $names = [
        'Jack.Qian'=>'jack-qian@163.com',
        'Perry.Gu'=>'perry-gu@163.com',
        'Aaron.Shen'=>'aaron-shen@163.com',
        'Robbin.Zhang'=>'robbin-zhang@163.com',
        'Peter.Hu'=>'peter-hu@163.com',
        'Paul'=>'paul@163.com',
        'Ye.Hui'=>'ye-hui@163.com',
        'Raidy.Lin'=>'raidy-lin@163.com',
        'Robin'=>'robin@163.com',
        'Bob'=>'bob@163.com',
        'Jenifer'=>'jenifer@163.com',
        'Eason'=>'eason@163.com',
        'Lina.Chen'=>'lina-chen@163.com',
    ];
    foreach ($names as $name=>$email) {
        $has = \App\Models\User::where('email', $email)->first();
        if($has){
            UserBasicInfo::where('user_id', $has->id)->update(['realname'=>$name]);
            $user_id = $has->id;
        }else{
            $user = new stdClass();
            $user->username = $email;
            $user->email = $email;
            $user->password = '123456';
            $user = signup_setup_new_user($user);
            userSignup($user, true);
            \App\Models\User::where('id', $user->id)->update([
                'confirmed'=>1,
                'firstname'=>'测',
                'lastname'=>'试'
            ]);
            UserBasicInfo::create(['user_id'=>$user->id,'realname'=>$name, 'email'=>$email]);
            $user_id = $user->id;
        }
        $hasC = CompanyUser::where('user_id', $user_id)->where('company_id',20200018)->first();
        if(!$hasC)
            CompanyUser::create([
                'user_id'=>$user_id,
                'company_id'=>20200018,
                'company_role_id'=>62,
            ]);
    }

    $names = [
        'lina.chen'=>'lina-chen@163.com',
        '信必优'=>'xinbiyou@163.com',
        '天钧华'=>'tianjunhua@163.com',
        '罗思怀'=>'luosihuai@163.com',
        'Alice'=>'alice@163.com',
        '杰普'=>'jiepu@163.com',
        '魏建国'=>'weijianguo@163.com',
        '王雪仪'=>'wangxueyi@163.com',
        '亿达'=>'yida@163.com',
        '恒赢智'=>'hengyingzhi@163.com',
        '文思'=>'wensi@163.com',
        'Mia.Lin'=>'mia-lin@163.com',
        '韬源'=>'taoyuan@163.com',
        '携银'=>'xieyin@163.com',
    ];
    foreach ($names as $name=>$email) {
        $has = \App\Models\User::where('email', $email)->first();
        if($has){
            UserBasicInfo::where('user_id', $has->id)->update(['realname'=>$name]);
            $user_id = $has->id;
        }else{
            $user = new stdClass();
            $user->username = $email;
            $user->email = $email;
            $user->password = '123456';
            $user = signup_setup_new_user($user);
            userSignup($user, true);
            \App\Models\User::where('id', $user->id)->update([
                'confirmed'=>1,
                'firstname'=>'测',
                'lastname'=>'试'
            ]);
            UserBasicInfo::create(['user_id'=>$user->id,'realname'=>$name, 'email'=>$email]);
            $user_id = $user->id;
        }
        $hasC = CompanyUser::where('user_id', $user_id)->where('company_id',20200001)->first();
        if(!$hasC)
            CompanyUser::create([
                'user_id'=>$user_id,
                'company_id'=>20200001,
                'company_role_id'=>64,
            ]);
    }
    //20200018 62  20200001 61



    dd(2);
    $userRe = app()->build(UserRepository::class);
    $user = $userRe->generateInviteUser("68067348@qq.com");
    dd($user);
    dd(CompanyRole::find(56)->users);
    $t = CompanyPermission::where('level', 3)->get();

    foreach ($t as $v) {
        $v->full_key = $v->parent->full_key.'.'.$v->key;
        $v->save();
    }
    dd(1);
    $t = new Recruit();
    $t = $t->where('id','>',1);
    dd($t->getQuery()->wheres);
    dd(request('i'));
    set_time_limit(0);
    $uis = UserBasicInfo::all();
    foreach ($uis as $ui) {
        $ui->email = $ui->user->email;
        $ui->save();
    }
    dd(1);
    $moduleArr = CompanyPermission::where('level','<',3)->get()->keyBy('key')->toArray();
    $operationArr = CompanyPermission::where('level',3)->get()->groupBy('pid')->toArray();
    foreach ($moduleArr as $item) {
        if(isset($operationArr[$item['id']])){
            foreach ($operationArr[$item['id']] as $v) {
                CompanyLog::create([
                    'company_id'=>20190003,
                    'user_id'=>106,
                    'operation'=>$v['key'],
                    'content'=>'内容--test',
                    'module'=>$item['key'],
                ]);
            }
        }
    }
    dd(1);
    $us = UserBasicInfo::whereNull('realname')->get();
    foreach ($us as $u) {
        $u->realname = $u->user_id;
        $u->save();
    }
    dd(CompanyUser::whereIn('company_id', Company::where('status','!=', 1)->pluck('id'))->update(['status'=>-1]));
    $user = \App\Models\User::find(111);
    dd(app()->build(UserRepository::class)->checkCurrentCompany($user));
    $user = \App\Models\User::find(55);
    dd($user->companies()->where('is_current', 1)->first()->pivot);
    $q = \App\Models\Moodle\Quiz::find(12);
    dd($q->courseObj);
    $text = "<p>假设A类有如下定义，设a是A类的一个实例，下列语句调用哪个是错误的？（  ）</p>
<p><img src=\"@@PLUGINFILE@@/musa_logo.png\" alt=\"\" width=\"1905\" height=\"1296\" /><img src=\"@@PLUGINFILE@@/favicon.jpg\" alt=\"\" width=\"36\" height=\"32\" /></p>";
    dump($text);
    $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/";
    preg_match_all($pattern,$text,$match);
    if(isset($match[1])){
        foreach ($match[1] as $k=>$v) {
            $_full = $match[0][$k];
            $_src = $match[1][$k];
            $_file_arr = explode('/', $_src);
            $_file = $_file_arr[count($_file_arr)-1];
            dd($_file);
            $url = $this->getPicture($_true_url,'topic');
            if($url){

                $text = str_replace($_src, $url, $text);
            }
        }
    }
    dd($match);

    dd($s);
    $r = RecruitResume::find(1);
    $r->updated_at = '2019-12-26 16:33:52';
    $r->save();die;
    $user = \App\Models\User::find(75);
    $has = $user->companies()->where('company_id',1)->first();
    dd($has);
    $obj = new stdClass();
    $data['natures'] = ['is_third_party', 'is_demand_side'];
    if(isset($data['natures']) && is_array($data['natures'])){
        $is_third_party = 0;
        $is_demand_side = 0;
        foreach ($data['natures'] as $v) {
            if($v=='is_third_party'){
                $is_third_party = 1;
            }elseif($v=='is_demand_side'){
                $is_demand_side = 1;
            }
        }
        $obj->is_third_party = $is_third_party;
        $obj->is_demand_side = $is_demand_side;
    }
    dd($obj);
    $t = CompanyRole::find(3);
    dd($t->users);
    dd(in_array(0,[0,1]));
    $recruits = Recruit::all();
    foreach ($recruits as $recruit) {
        $count = RecruitResume::where('company_job_recruit_id', $recruit->id)->count();

        $c = $count - $recruit->resume_num;
        if($recruit->resume_num!=$count){
            $recruit->resume_num = $count;
            $recruit->new_resume_num += $c;
            $recruit->save();
        }
    }
    $j = Job::find(18);
    dd($j->necessarySkills);
    $oldId = Conglomerate::max('id');
    dd(strlen($oldId));
    dd(Company::find(1)->manager());
    if(0.232>'0.33'){
        dd(1);
    }else{
        dd(0);
    }
    $quizzes = \App\Models\Moodle\Quiz::all();
    $quizzes->load('gradeObj');
    dd($quizzes);
//    dd(!in_array('updated', ['created', 'updated', 'deleted']));
    $recruit = Recruit::where('id', 1)->first();
//    $recruit->update(['modifier_id'=>3]);
    if($recruit->leading_id==55)
        $recruit->leading_id = 1;
    else
        $recruit->leading_id=55;
    $recruit->save();
    die;
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
Route::get('/company/thirdParty/statistics/excel', 'API\CompaniesController@thirdPartyStatisticsExcel');

Route::get('/company/logs/excel', 'API\CompanyLogsController@exportExcel');

