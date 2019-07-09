<?php

namespace App\Http\Controllers\API\Shiyanlou\Controllers;

use DB;
use Illuminate\Support\Facades\Input;
use  App\Http\Controllers\API\CommonController;
use  App\Models\ShiYanLou;

class LoginController extends CommonController
{
    public function index()
    {
        $course_id = $this->request->id;
        $course_id = $course_id?$course_id:88;
        $params = ['uid' => 'boo', 'course_id' => $course_id, 'next' => 'learning_page'];//请求参数
        $path = '/api/saas/v1/auth/union-login/';
        $sortParams = ShiYanLou::sortArr($params);//字典排序
        $jwt_token = ShiYanLou::getToken($sortParams, $path);//获取token
        $params['jwt_token'] = $jwt_token;
        $url = ShiYanLou::URL . $path . '?' . http_build_query($params);
        header("location:$url");
        exit;
    }
}
