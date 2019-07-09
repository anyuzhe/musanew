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


$router->get('/', function () use ($router) {
    return 'test';
});

$router->get('/test1', function () use ($router) {

    $skills = \App\Models\Skill::all()->keyBy('id')->toArray();
    dd($skills);
    dd(\App\Models\DataMap::first()->options);
    $str = "概念级别、实践级别、指导级别、专家级别";
    $strs = explode('、', $str);
    $i=1;
    foreach ($strs as $s) {
        \App\Models\DataMapOption::create([
            'mapid'=>10,
            'key'=>$i,
            'value'=>$s
        ]);
        $i++;

    }
    dd($strs);
    return view('test');
});

//登陆
$router->any('/user/login', 'API\LoginController@login');
$router->get('/user/login/test', 'API\LoginController@test');
//注册
$router->post('/user/register', 'API\LoginController@register');
//邮箱找回密码发送验证码
$router->post('/user/findpassword/sendcode', 'API\LoginController@sendCode');
//找回密码--修改密码
$router->post('/user/findpassword/edit', 'API\LoginController@editPassword');

$router->get('/company/jobs/idName', 'API\JobsController@allListIdName');

//职位列表
$router->get('/company/jobs', 'API\JobsController@index');
$router->get('/company/jobs/{id}', 'API\JobsController@show');

//招聘列表
$router->get('/company/recruits/type/1', 'API\RecruitsController@index');
$router->get('/company/recruits/type/4', 'API\CompaniesController@entrustsList');
$router->get('/company/recruits/type/{type}', 'API\EntrustsController@index');

$router->get('/company/recruits/{id}', 'API\RecruitsController@show');

//合作第三方
$router->get('/company/thirdParty', 'API\CompaniesController@thirdPartyList');
//合作第三方
$router->get('/company/thirdParty/idName', 'API\CompaniesController@thirdPartyListIdName');

//验证职位编号
$router->post('/company/jobs/checkCode', 'API\JobsController@checkCode');




$router->group(['middleware' => 'auth.api'], function () use ($router) {
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
    $router->post('/company/entrusts/sendResumes', 'API\EntrustResumesController@sendResumes');

    //招聘下的-简历列表
    $router->get('/company/recruit/resumes', 'API\RecruitResumesController@index');

    //招聘下的-简历详情
    $router->get('/company/recruit/resumes/{id}', 'API\RecruitResumesController@show');

    //招聘下的-简历流程
    $router->post('/company/recruits/resumeFlow', 'API\RecruitResumesController@resumeFlow');

});
$router->get('/job/test', 'API\JobsController@getTest');//获取测试
//获取地区列表
$router->get('/area/tree', 'API\AreasController@getTree');
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
