<?php
/**
 * Created by PhpStorm.
 * User: anyuzhe
 * Date: 2017/3/24
 * Time: 09:59
 */

namespace App\ZL\ORG\Xcx;

use App\ZL\Library\Curl;
use Illuminate\Http\Request;

class Common
{
    protected $appId;
    protected $appSecret;
    protected $accessToken;
    protected $errorCodeStr= [
        '-41001'=> 'encodingAesKey 非法',
        '-41003'=> 'aes 解密失败',
        '-41004'=> '解密后得到的buffer非法',
        '-41005'=> 'base64加密失败',
        '-41016'=> 'base64解密失败',
    ];

    public function __construct($appId,$appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    Protected $autoCheckFields = false;

    public function getSessionKey($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code=$code&grant_type=authorization_code";
        return Curl::get($url);
    }

    public function checkHasAndSaveInfo($user)
    {
        $unionid = $user['unionId'];
        $hasuser = D('Home/Wechat','Logic')->checkHas($unionid);
        if(!$hasuser){
            $data = [
                'nickname'=>$user['nickName'],
                'logo'=>$user['avatarUrl'],
                'sex'=>$user['gender'],
                'xcy_openid'=>$user['openId'],
                'unionid'=>$user['unionId'],
                'city'=>$user['city'],
                'created_at'=>time(),
//                'start_time'=>time(),
//                    'end_time'=>time()+3600*24*30,
                'updated_at'=>time(),
            ];
            $id = M('User')->add($data);
            return $id;
        }else{
            $data = [
                'nickname'=>$user['nickName'],
                'logo'=>$user['avatarUrl'],
                'sex'=>$user['gender'],
                'xcy_openid'=>$user['openId'],
                'city'=>$user['city'],
                'updated_at'=>time(),
            ];
            M('User')->data($data)->where(['id'=>$hasuser['id']])->save();
            return $hasuser['id'];
        }
    }

    public function checkHas($unionid)
    {
        $hasone = M('User')->where(['unionid'=>$unionid])->find();
        return $hasone;
    }

    public function codeToStr($code)
    {
        if(isset($this->errorCodeStr[$code]))
            return $this->errorCodeStr[$code];
        else
            return '未知错误';
    }

    public function saveToken($id,$sessionKey)
    {
        M('User')->data([
            'id'=>$id,
            'token'=>$sessionKey
        ])->save();
    }

    public function getAccessToken()
    {
        if($this->accessToken){
            return $this->accessToken;
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
            $res = json_decode(Curl::get($url),true);
            cache('xcy_access_token',$res['access_token'],$res['expires_in']-60);
            return $res['access_token'];
        }
    }

    public function decryptData(Request $request, $session_key, &$data)
    {
        $pc = new WXBizDataCrypt($this->appId, $session_key);
        return $pc->decryptData($request->encryptedData, $request->iv, $data);
    }
}