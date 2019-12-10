<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Repositories\TokenRepository;
use DB;
use App\Models\ExternalToken;
use App\ZL\ResponseLayout;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommonController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function requireMoodleConfig()
    {
        return requireMoodleConfig();
    }

    public function getMoodleRoot()
    {
        return getMoodleRoot();
    }

    /*
      * 生成json数据格式
      * @param integer $code 状态码
      * @param string $message 提示信息
      * $param array $data 数据
      * return string
      */
    public function apiReturnJson($code, $data = [], $message = '', $other = array())
    {
        return ResponseLayout::apply($code,$data,$message,$other);
    }

    public function getToken()
    {
        $token = $this->request->get('token');
        if(!$token)
            $token = $this->request->header('token');
        if(!$token)
            $token = $this->request->header('Token');

        return $token;
    }

    public function getAdmin()
    {
        return TokenRepository::getAdmin();
    }
}
