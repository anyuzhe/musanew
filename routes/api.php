<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/course/jump', function () {
    requireMoodleConfig();
    global $CFG;
    $token = \App\Repositories\TokenRepository::getToken();
    $course_id = request('course_id');
    getCurl($CFG->wwwroot."/webservice/rest/server.php?wsfunction=enrol_self_enrol_user&wstoken={$token}&courseid={$course_id}&moodlewsrestformat=json");
    return redirect($CFG->wwwroot.'/login/course_jump.php?token='.$token."&course_id=".$course_id);
});

$router->get('/', function () use ($router) {
    $request = app('request');
    dd($request->all());
    return 'test';
});

$router->get('/test1', function () use ($router) {
    $validator = \Illuminate\Support\Facades\Validator::make(['code'=>'0019'], [
        'code' => [
            'required',
            \Illuminate\Validation\Rule::unique('jobs')->ignore(10)->where(function ($query) {
                return $query->where('status', '!=',-1);
            }),
        ],
    ],[
        'code.required'=>'编号编号必须填写',
        'code.unique'=>'编号必须唯一',
    ]);
    if($validator->fails()){
        $errors = $validator->errors();
        dd($errors);
    }
dd(1);
    $strs = [
        '系统开发生命周期',
'需求分析',
'用户体验',
'数据分析',
'竞品分析',
'运营思维'
    ];
    foreach ($strs as $s) {
        \App\Models\Skill::create([
            'name'=>$s,
            'category_l1_id'=>1,
            'category_l2_id'=>14
        ]);

    }
    dd($strs);
    return view('test');
});

//登陆
$router->any('/user/login', 'API\LoginController@login');

$router->any('/skip/course', 'API\LoginController@skipCourse');

$router->get('/user/login/test', 'API\LoginController@test');
//注册
$router->any('/user/register', 'API\LoginController@register');
//邮箱找回密码发送验证码
$router->post('/user/findpassword/sendcode', 'API\LoginController@sendCode');
//邮箱注册发送验证码
$router->post('/user/register/sendcode', 'API\LoginController@sendCodeByRegister');
//找回密码--修改密码
$router->any('/user/findpassword/edit', 'API\LoginController@editPassword');


//------------首页相关接口-------------------
//公开的招聘信息
$router->get('/public/recruits', 'API\PublicRecruitsController@index');
//公开的招聘信息详情
$router->get('/public/recruits/detail', 'API\PublicRecruitsController@detail');

//简历上传
$router->post('/company/resume/upload', 'API\EntrustResumesController@upload');

//图片上传
$router->post('/upload', 'Voyager\VoyagerController@uploadNew');

$router->group(['middleware' => 'auth.api'], function () use ($router) {
    $router->get('/company/jobs/idName', 'API\JobsController@allListIdName');

//职位列表
    $router->get('/company/jobs', 'API\JobsController@index');
    $router->get('/company/jobs/{id}', 'API\JobsController@show');

//招聘列表
    $router->get('/company/recruits/type/1', 'API\RecruitsController@index');
    $router->get('/company/recruits/type/4', 'API\CompaniesController@entrustsList');
    $router->get('/company/recruits/type/{type}', 'API\EntrustsController@index');
    $router->get('/company/entrust/{id}', 'API\EntrustsController@show');
//外包的招聘列表
    $router->get('/company/recruits/outsource', 'API\RecruitsController@outsourceList');

    $router->get('/company/recruits/{id}', 'API\RecruitsController@show');

//合作第三方
    $router->get('/company/thirdParty', 'API\CompaniesController@thirdPartyList');
//合作第三方
    $router->get('/company/thirdParty/idName', 'API\CompaniesController@thirdPartyListIdName');
//需求方
    $router->get('/company/demandSides/idName', 'API\CompaniesController@demandSideListIdName');

//验证职位编号
    $router->post('/company/jobs/checkCode', 'API\JobsController@checkCode');

    //个人信息
    $router->get('/user/info', 'API\UsersController@info');
    $router->post('/user/info', 'API\UsersController@setInfo');
    $router->post('/user/setCurrentCompany', 'API\UsersController@setCurrentCompany');

    //职位相关
    $router->post('/company/jobs', 'API\JobsController@store');
    $router->post('/company/jobs/{id}', 'API\JobsController@update');
    $router->any('/company/jobs/delete/{id}', 'API\JobsController@destroy');
    $router->any('/company/jobs/checkDelete/{id}', 'API\JobsController@checkDelete');

    //人员招聘
    //添加招聘职位
    $router->post('/company/recruits', 'API\RecruitsController@store');
    //更新招聘
    $router->post('/company/recruits/{id}', 'API\RecruitsController@update');
    //结束
    $router->post('/company/recruits/finish', 'API\RecruitsController@finish');
    //重新开启
    $router->post('/company/recruits/restart', 'API\RecruitsController@restart');
    //暂停
    $router->post('/company/recruits/pause', 'API\RecruitsController@pause');
    //开启
    $router->post('/company/recruits/start', 'API\RecruitsController@start');
    //招聘职位提交申请给第三方
    $router->post('/company/entrusts/applyEntrust', 'API\EntrustsController@applyEntrust');
    //招聘职位提交申请给第三方-取消
    $router->post('/company/entrusts/cancel', 'API\EntrustsController@cancelEntrust');
    //第三方转交给需求方
    $router->post('/company/entrusts/return', 'API\EntrustsController@returnEntrust');
    //第三方接受招聘需求
    $router->post('/company/entrusts/accept', 'API\EntrustsController@acceptEntrust');
    //第三方拒绝招聘需求
    $router->post('/company/entrusts/reject', 'API\EntrustsController@rejectEntrust');

    //简历

//简历附件上传
    $router->post('/company/resume/attachment/upload', 'API\EntrustResumesController@attachmentUpload');

    //删除简历附件
    $router->post('/company/resume/attachment/delete/{id}', 'API\EntrustResumesController@attachmentDestroy');

//    $router->get('/user/resume', 'ResumesController@view');
    //列表
    $router->get('/company/resumes', 'API\EntrustResumesController@index');
//详情
    $router->get('/company/resumes/{id}', 'API\EntrustResumesController@show');

    //删除
    $router->post('/company/resumes/delete/{id}', 'API\EntrustResumesController@destroy');

    //第三方添加简历
    $router->post('/company/entrusts/resume', 'API\EntrustResumesController@store');
    //更新简历
    $router->post('/company/entrusts/resume/{id}', 'API\EntrustResumesController@update');

    //投递简历
    $router->post('/company/entrusts/sendResumes', 'API\EntrustResumesController@entrustSendResumes');
    $router->post('/company/resume/sendRecruits', 'API\EntrustResumesController@resumeSendEntrust');

    //招聘下的-简历列表
    $router->any('/company/recruit/resumes', 'API\RecruitResumesController@index');

    //招聘下的-简历详情
    $router->get('/company/recruit/resumes/{id}', 'API\RecruitResumesController@show');

    //招聘下的-简历流程
    $router->post('/company/recruits/resumeFlow', 'API\RecruitResumesController@resumeFlow');

    //企业信息修改
    $router->get('/company/currentInfo', 'API\CompaniesController@getCurrentInfo');
    $router->post('/company/currentInfo', 'API\CompaniesController@updateCurrentInfo');

    //代办事项
    $router->get('/company/backlog', 'API\CompaniesController@getBacklog');

    $router->get('/company/users', 'API\CompaniesController@getUsers');
    //日历
    $router->get('/company/calendar', 'API\CompaniesController@getCalendarData');
    //数据分析
    $router->get('/company/count/statistics/{type}', 'API\CompaniesController@countStatistics');
    $router->get('/company/data/statistics/{type}', 'API\CompaniesController@dataStatistics');
    $router->get('/company/data/statistics/detail/{type}', 'API\CompaniesController@dataStatisticsDetail');

    //收藏和拉黑简历
    $router->any('/company/resume/relation', 'API\CompaniesController@resumeRelationSet');
    //企业部门
    $router->any('/company/departments', 'API\CompaniesController@getDepartments');


    //----------------个人中心-------------------------
    //简历列表
    $router->get('/user/resumes', 'API\UserResumesController@index');
    //可投简历列表
    $router->get('/user/resumes/used', 'API\UserResumesController@usedList');
//详情
    $router->get('/user/resumes/{id}', 'API\UserResumesController@show');

    //删除
    $router->post('/user/resumes/delete/{id}', 'API\UserResumesController@destroy');

    //第三方添加简历
    $router->post('/user/resumes', 'API\UserResumesController@store');
    //更新简历
    $router->post('/user/resumes/{id}', 'API\UserResumesController@update');

});
$router->get('/job/test', 'API\JobsController@getTest');//获取测试
//获取地区列表
$router->get('/area/tree', 'API\AreasController@getTree');
//获取热门城市
$router->get('/area/hot/city', 'API\AreasController@getHotCity');
//获取行业列表
$router->get('/industry/tree', 'API\IndustriesController@getTree');
//获取技能树列表
$router->get('/skills/tree', 'API\SkillsController@getTree');

//获取选项
$router->get('/data/map', 'API\DataMapController@extendMap');

//获取企业所有课程
$router->get('/shiyanlou/course', 'API\Shiyanlou\Controllers\CourseController@index');
//获取实验课程列表
$router->get('/shiyanlou/labs', 'API\Shiyanlou\Controllers\LabsController@index');
//实验楼登陆
$router->get('/shiyanlou/login/{id}', 'API\Shiyanlou\Controllers\LoginController@index');
//获取企业课程下的学生信息
$router->get('/shiyanlou/student', 'API\Shiyanlou\Controllers\StudentController@index');
//获取所有用户
$router->get('/shiyanlou/user', 'API\Shiyanlou\Controllers\UserController@index');
//实验楼 事件推送
$router->post('/shiyanlou/webhook', 'API\Shiyanlou\Controllers\WebhookController@index');
