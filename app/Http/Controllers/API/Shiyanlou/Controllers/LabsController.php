<?php

namespace App\Http\Controllers\API\Shiyanlou\Controllers;

use DB;
use Illuminate\Support\Facades\Input;
use  App\Http\Controllers\API\CommonController;
use  App\Models\ShiYanLou;

class LabsController extends CommonController
{
    public function index()
    {
        if ($this->request->has('course_id')) {
            $path = "/api/saas/v1/courses/" . $_GET['course_id'] . "/labs/";
            $params = [];//请求参数
            if (!$params) {
                $jwt_token = ShiYanLou::getToken('', $path);//获取token
            } else {
                $sortParams = ShiYanLou::sortArr($params);//字典排序
                $jwt_token = ShiYanLou::getToken($sortParams, $path);//获取token
            }
            $params['jwt_token'] = $jwt_token;
            $url = ShiYanLou::URL . $path . '?' . http_build_query($params);
            $data = ShiYanLou::curl_get($url);
            return $this->apiReturnJson(0, $data);
        } else {
            return '参数错误';
        }
    }
}
