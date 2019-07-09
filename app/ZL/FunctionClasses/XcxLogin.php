<?php
/**
 * Created by PhpStorm.
 * User: zhenglong
 * Date: 2017/4/11
 * Time: 下午4:26
 */

namespace App\ZL\FunctionClasses;


use App\ZL\Library\Curl;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class XcxLogin
{
    protected $appid;
    protected $secret;

    public function __construct()
    {
        $this->appid = config('xcx.appid');
        $this->secret = config('xcx.secret');
    }

    public function getSessionKey(Request $request)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this->secret}&js_code={$request->code}&grant_type=authorization_code";
        $res = Curl::get($url);
        $data = json_decode($res,true);
        if(isset($data['session_key'])){
            $session_id = Str::random(40);
            cache([$session_id=>json_encode($data)],$data['expires_in']?$data['expires_in']/60:120);
            $res = [
                'session_id' =>$session_id,
                'openid' =>$data['openid'],
            ];
            return ['status'=>1,'response'=>$res];
        }else{
            return ['status'=>0,'message'=>$data['errmsg']];
        }
    }
}