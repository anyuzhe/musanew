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


$router->get('/course/jump', function () {
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
//测验 列表
$router->get('/quizzes', 'API\QuizzesController@index');
$router->get('/quizzes/{id}', 'API\QuizzesController@show');
$router->post('/quizzes/submit/answers/{id}', 'API\QuizzesController@submit');

//简历上传
$router->post('/company/resume/upload', 'API\EntrustResumesController@upload');

//图片上传
$router->post('/upload', 'Voyager\VoyagerController@uploadNew');

$router->post('/company/change/manager/affirm', 'API\CompaniesController@changeManagerAffirm');

$router->group(['middleware' => 'auth.api'], function () use ($router) {
    $router->get('/company/jobs/idName', 'API\JobsController@allListIdName');
    $router->get('/company/recruit/jobs/idName', 'API\JobsController@recruitJobListIdName');

    $router->get('/company/thirdParty/jobs/idName', 'API\EntrustsController@thirdPartyJobListIdName');

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
//合作需求方
    $router->get('/company/demandSide', 'API\CompaniesController@demandSideList');

//合作第三方
    $router->get('/company/thirdParty/idName', 'API\CompaniesController@thirdPartyListIdName');
//需求方
    $router->get('/company/demandSides/idName', 'API\CompaniesController@demandSideListIdName');

//验证职位编号
    $router->post('/company/jobs/checkCode', 'API\JobsController@checkCode');
//验证职位编号
    $router->post('/company/recruits/check', 'API\RecruitsController@checkExist');

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
    //第三方转包
    $router->post('/company/entrusts/subcontract', 'API\EntrustsController@subcontract');
    //第三方转包检测
    $router->post('/company/entrusts/check/subcontract', 'API\EntrustsController@checkSubcontract');
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


    //获取招聘简历相关人员
    $router->get('/company/recruit/resumes/personnel', 'API\RecruitResumesController@personnel');

    //招聘下的-简历详情
    $router->get('/company/recruit/resumes/{id}', 'API\RecruitResumesController@show');
    //招聘下的-简历流程
    $router->post('/company/recruits/resumeFlow', 'API\RecruitResumesController@resumeFlow');

    //更新招聘
    $router->post('/company/recruits/{id}', 'API\RecruitsController@update');

    //企业信息修改
    $router->get('/company/currentInfo', 'API\CompaniesController@getCurrentInfo');
    $router->post('/company/currentInfo', 'API\CompaniesController@updateCurrentInfo');
    //更换企业管理员
    $router->post('/company/change/manager', 'API\CompaniesController@changeManager');

    //代办事项
    $router->get('/company/backlog', 'API\CompaniesController@getBacklog');

    $router->get('/company/users', 'API\CompaniesController@getUsers');
    $router->get('/company/users/list', 'API\CompaniesController@getUserList');
    $router->get('/company/users/{id}', 'API\CompaniesController@userShow');
    $router->get('/company/users/{id}/permission/scope', 'API\CompaniesController@getUserPermissionScope');
    $router->post('/company/users/{id}/permission/scope', 'API\CompaniesController@setUserPermissionScope');
    $router->post('/company/users', 'API\CompaniesController@storeUser');
    $router->post('/company/users/{user_id}', 'API\CompaniesController@updateUser');
    $router->post('/company/users/delete/{user_id}', 'API\CompaniesController@deleteUser');
    //日历
    $router->get('/company/calendar', 'API\CompaniesController@getCalendarData');
    //数据分析
    $router->get('/company/count/statistics/{type}', 'API\CompaniesController@countStatistics');
    $router->get('/company/data/statistics/{type}', 'API\CompaniesController@dataStatistics');
    $router->get('/company/data/statistics/detail/{type}', 'API\CompaniesController@dataStatisticsDetail');
    $router->get('/company/thirdParty/statistics', 'API\CompaniesController@thirdPartyStatistics');
    //收藏和拉黑简历
    $router->any('/company/resume/relation', 'API\CompaniesController@resumeRelationSet');
    //企业部门
    $router->any('/company/departments', 'API\CompaniesController@getDepartments');

    //修改委托
    $router->post('/company/entrusts/{id}', 'API\EntrustsController@update');
    //角色管理
    $router->resource('company/roles', 'API\RolesController');

    //简历匹配分数设置
    $router->get('/company/settings/resume/grade', 'API\CompanySettingsController@getResumeGrade');
    $router->post('/company/settings/resume/grade', 'API\CompanySettingsController@setResumeGrade');


    //简历匹配分数设置--新
    $router->resource('/company/resume/grade/settings', 'API\CompanyResumeGradeSettingsController');

    //操作日志
    $router->get('/company/logs', 'API\CompanyLogsController@index');
    //企业 信息 通知
    $router->get('/company/notifications', 'API\CompanyNotificationsController@index');
    $router->get('/company/notifications/{id}', 'API\CompanyNotificationsController@show');
    $router->post('/company/notifications/read', 'API\CompanyNotificationsController@setRead');
    //操作日志
    $router->get('/company/recruit/logs', 'API\CompanyRecruitLogsController@index');

    //----------------个人中心-------------------------
    //简历列表
    $router->get('/user/resumes', 'API\UserResumesController@index');

    //删除
    $router->post('/user/resumes/delete/{id}', 'API\UserResumesController@destroy');

    //个人添加简历
    $router->post('/user/resumes', 'API\UserResumesController@store');
    $router->post('/user/resumes/upload', 'API\UserResumesController@upload');

    //更新简历
    $router->post('/user/resumes/{id}', 'API\UserResumesController@update');

    //------------首页相关接口-------------------
    //可投简历列表
    $router->get('/user/resumes/used', 'API\UserResumesController@usedList');

//详情
    $router->get('/user/resumes/{id}', 'API\UserResumesController@show');

    //职位测试情况
    $router->get('/user/recruits/tests/{id}', 'API\UserTestsController@getTestByRecruitId');
    $router->get('/user/recruits/matching', 'API\UserTestsController@getMatching');

    //个人投递简历
    $router->post('/user/recruits/sendResume', 'API\UserResumesController@sendResume');
    //个人测评列表
    $router->get('/user/tests', 'API\UsersController@getTestData');

    //个人招聘信息列表
    $router->get('/user/recruitResumes', 'API\RecruitResumesController@userRecruitList');
    // v2.1
});

//角色管理
$router->resource('/roles', 'API\RolesController');
$router->any('/user/activate', 'API\LoginController@activate');

$router->post('/admin/new/login', 'API\Admin\LoginController@login');
$router->post('/admin/new/logout', 'API\Admin\LoginController@logout');

$router->group(['middleware' => 'admin.api', 'prefix'=>'admin'], function () use ($router) {
    $router->get('/info', 'API\Admin\AdminsController@info');
    $router->get('/auth/list', 'API\Admin\AdminsController@authList');
    //图片上传
    $router->post('/upload', 'Voyager\VoyagerController@uploadNew');

    //管理员
    $router->resource('/admins', 'API\Admin\AdminsController');
    $router->resource('/roles', 'API\Admin\RolesController');
    //菜单
    $router->resource('/menus', 'API\Admin\MenusController');
    //集团
    $router->resource('/conglomerates', 'API\Admin\ConglomeratesController');
    //公司
    $router->resource('/companies', 'API\Admin\CompaniesController');
    //用户
    $router->resource('/users', 'API\Admin\UsersController');

    //获取技能树列表
    $router->get('/skills/tree', 'API\SkillsController@getTree');
    $router->post('/skills/tree', 'API\SkillsController@saveTree');

    //技能
    $router->resource('/skills', 'API\Admin\SkillsController');
    //技能分类
    $router->resource('/skillCategories', 'API\Admin\SkillCategoriesController');


//招聘列表
    $router->get('/recruits/type/1', 'API\Admin\RecruitsController@index');
    $router->get('/recruits/type/{type}', 'API\Admin\EntrustsController@index');
    $router->get('/entrust/{id}', 'API\Admin\EntrustsController@show');
//外包的招聘列表
    $router->get('/recruits/outsource', 'API\Admin\RecruitsController@outsourceList');
    $router->get('/recruits/{id}', 'API\Admin\RecruitsController@show');
    $router->put('/recruits/{id}', 'API\Admin\RecruitsController@update');
    $router->put('/entrust/{id}', 'API\Admin\EntrustsController@update');

    //招聘下的-简历列表
    $router->any('/recruit/resumes', 'API\Admin\RecruitResumesController@index');
    //招聘下的-简历详情
    $router->get('/recruit/resumes/{id}', 'API\Admin\RecruitResumesController@show');
    $router->put('/recruit/resumes/{id}', 'API\Admin\RecruitResumesController@update');
    $router->put('/recruit/resumes/log/{id}', 'API\Admin\RecruitResumesController@updateLog');
    //招聘下的-简历修改
    $router->get('/recruitResumes/{id}/users', 'API\Admin\RecruitResumesController@getUsers');

    //公司下的员工列表
    $router->get('/company/{id}/users', 'API\Admin\CompaniesController@getUsers');
    $router->get('/company/{id}/users/{user_id}', 'API\Admin\CompaniesController@userShow');
    $router->post('/company/{id}/users', 'API\Admin\CompaniesController@storeUser');
    $router->post('/company/{id}/users/{user_id}', 'API\Admin\CompaniesController@updateUser');
    $router->post('/company/users/delete/{user_id}', 'API\Admin\CompaniesController@deleteUser');
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


//操作日志
$router->get('/test/logs', 'API\CompanyLogsController@index');
