<?php

use App\Models\CompanyDepartment;
use App\Models\CompanyRole;
use App\Models\CompanyRolePermission;
use App\Models\CompanyUser;
use App\Models\CompanyUserRole;
use App\Models\DataMapOption;
use App\Models\User;
use App\Repositories\TokenRepository;
use Illuminate\Support\Facades\DB;

if (! function_exists('sendVerifyCode')) {
    function sendVerifyCode($mobile,$mobile_code) {
        $send_result = \App\ZL\ORG\Aliydx\Sms::sendSms(
            "洁丽雅", // 短信签名
            "SMS_126630460", // 短信模板编号
            $mobile, // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>$mobile_code,
            )
//                , "123"   // 流水号,选填
        );
        if(isset($send_result->Code) && $send_result->Code=="OK"){
            $status = 1;
            $msg = '验证码发送成功！';
//            session(['verify_tel_code'=>$mobile_code]);
//            session(['verify_tel'=>$mobile]);
        }else{
            $status = 0;
//            $msg = '验证码发送失败！';
            $msg = $send_result->Message?$send_result->Message:'验证码发送失败！';
        }
        return \App\ZL\ResponseLayout::apply($status,false,$msg);
    }
}

//
//if (! function_exists('sendVerifyCode')) {
//    function sendVerifyCode($mobile,$mobile_code) {
//        include_once '../app/ZL/ORG/Dayu/TopSdk.php';
////        $mobile_code = rand(100000,999999);
//        //   阿里大鱼
//        $msg = array(
//            'name' => (string)$mobile_code,
////            'product' => (string)$mobile
//        );
//        $tpl = 'SMS_16325180';
//        $param = json_encode($msg);
//
//        $client = new \TopClient('23812555', '5debec67dcda6ab0a976c773efa2473f');
//        $req = new \AlibabaAliqinFcSmsNumSendRequest();
//        $req->setExtend("");
//        $req->setSmsType("normal");
//        $req->setSmsFreeSignName("峥空智能");
//        $req->setSmsParam($param);
//        $req->setRecNum($mobile);
//        $req->setSmsTemplateCode($tpl);
//        $send_result = $client->execute($req, null);
//        if(isset($send_result->result) && $send_result->result->success){
//            $status = 1;
//            $msg = '验证码发送成功！';
////            session(['verify_tel_code'=>$mobile_code]);
////            session(['verify_tel'=>$mobile]);
//        }else{
//            $status = 0;
////            $msg = '验证码发送失败！';
//            $msg = $send_result->sub_msg?$send_result->sub_msg:'验证码发送失败！';
//        }
//        return \App\ZL\ResponseLayout::apply($status,false,$msg);
//    }
//}


if (! function_exists('getTitleByArray')) {
    function getTitleByArray($res,$modelArray,$dicArray,$dictionaries,$foreach=true)
    {
        $dict_array = [];
        foreach ($dicArray as $k=>$item) {
            $dict_array[$k] = $dictionaries->getByType($item);
        }
//        dd($dict_array);
        if($foreach){
            foreach ($res as $re) {
                foreach ($modelArray as $k=>$item) {
                    $_title = $item.'_title';
                    $re->$_title = $dict_array[$k][$re->$item]->text;
                }
            }
        }else{
            foreach ($modelArray as $k=>$item) {
                $_title = $item.'_title';
                $res->$_title = $dict_array[$k][$res->$item]->text;
            }
        }
        return $res;
    }
}

if (! function_exists('computeHour')) {
    function computeHour($time1,$time2)
    {
        $time1Array = explode(':',$time1);
        $time2Array = explode(':',$time2);
        $differenceValue1 = $time2Array[0]-$time1Array[0];
        $differenceValue2 = $time2Array[1]-$time1Array[1];
        return $differenceValue1+($differenceValue2/60);
    }
}

if (! function_exists('objToArray')) {
    function objToArray($array)
    {
        return json_decode(json_encode($array),true);
    }
}

if (! function_exists('request_post')) {
    function request_post($url,$post_data = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);
        curl_close($ch);


        return $output;
    }
}

if (! function_exists('getWeekDays')) {
    function getWeekDays()
    {
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+1,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+2,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+3,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+4,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+5,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+6,date("Y")));
        $data[] = date("Y-m-d",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));
        return $data;
    }
}

if (! function_exists('getConfigValue')) {
    function getConfigValue($type)
    {
        if($value = \Illuminate\Support\Facades\Cache::get('config_'.$type, null)){
            return $value;
        }
        else{
            $value = \Illuminate\Support\Facades\DB::table('configs')->where('type',$type)->first()->value;
            \Illuminate\Support\Facades\Cache::put('config_'.$type, $value, 60);
            return $value;
        }
    }
}
if (! function_exists('responseZK')) {
    function responseZK($errCode=0,$data = [],$msg = '',$other=array())
    {
        return response()->json(\App\ZL\ResponseLayout::apply($errCode,$data,$msg,$other));
    }
}
//获取sessionid
if (! function_exists('getSessionId')) {
    function getSessionId()
    {
        $session_id = request()->header('session-id');
        if($session_id){
            return $session_id;
        }
        $session_id = request()->get('session_id');
        if($session_id){
            return $session_id;
        }
        return null;
    }
}
//获取秘钥字符串
if (! function_exists('getAuthToken')) {
    function getAuthToken()
    {
        $authToken = request()->header('auth-token');
        if($authToken){
            return $authToken;
        }
        $authToken = request()->get('auth_token');
        if($authToken){
            return $authToken;
        }
    }
}
//提取session数据
if (! function_exists('getSessionData')) {
    function getSessionData()
    {
        return json_decode(cache(getSessionId()),true);
    }
}
//设置session数据
if (! function_exists('setSessionData')) {
    function setSessionData($data)
    {
        cache([getSessionId()=>json_encode($data)],isset($data['expires_in'])?$data['expires_in']:7200);
    }
}
//添加 session数据
if (! function_exists('addSessionData')) {
    function addSessionData($data)
    {
        $old = getSessionData();
        if($old){
            $new = array_merge($old,$data);
        }else{
            $new = $data;
        }
        setSessionData($new);
    }
}
//解密数据保存到session
if (! function_exists('decodeToSession')) {
    function decodeToSession()
    {
        $session_data = getSessionData();
        $token = getAuthToken();
        $key = config('app.aes_key');
        $iv = config('app.aes_iv');
        $json_str = \App\ZL\Library\Openssl::decryptByAes($key,$iv,$token);
        $encrypted = json_decode($json_str,true);

        $session_data = $session_data?$session_data:[];
        $encrypted = $encrypted?$encrypted:[];
        $all = array_merge($session_data,$encrypted);
        setSessionData($all);
        return $all;
    }
}
//获取session和秘钥 公共的数据
if (! function_exists('getSessionAllData')) {
    function getSessionAllData()
    {
        $session_data = getSessionData();
        if(isset($session_data['id'])){
            return $session_data;
        }else{
            return decodeToSession();
        }
    }
}

//获取session和秘钥 公共的数据 并且取出单一字段
if (! function_exists('getSessionField')) {
    function getSessionField($field)
    {
        $data = getSessionAllData();
        return isset($data[$field])?$data[$field]:null;
    }
}

//存入notification
if (! function_exists('saveNotification')) {
    function saveNotification($data,$title='msg')
    {
        $data = json_encode($data,256);

        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            ['json' => $data, 'title' => $title, 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }
}


if( ! function_exists('getArrayByStr')){
    function getArrayByStr($str, $segmentation = ',')
    {
        if(is_array($str)){
            $arr = $str;
        }else{
            $arr = explode($segmentation,$str);
        }
        return $arr;
    }
}

if( ! function_exists('getArrayByArray')){
    function getArrayByArray($array, $field = 'id',$is_array=1)
    {
        $new = [];
        foreach ($array as $item) {
            $new[] = $item[$field];
        }
        return $is_array?$new:implode(',',$new);
    }
}

if( ! function_exists('getIP')){
    //获取真实ip
    function getIP(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return($ip);
    }
}

if(!function_exists('getOrderTracesByJson')){
    //电商ID
    defined('EBusinessID') or define('EBusinessID', 1261234);
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
    defined('kdniao_AppKey') or define('kdniao_AppKey', '9ba7c900-0473-4572-ba63-7391b36fdf11');
//请求url
    defined('kdniao_ReqURL') or define('kdniao_ReqURL', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');

    /**
     * Json方式 查询订单物流轨迹
     */
    function getOrderTracesByJson($LogisticCode='',$ShipperCode='',$OrderCode=''){
        $requestData= "{'OrderCode':'','ShipperCode':'$ShipperCode','LogisticCode':'$LogisticCode'}";

        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = encrypt1($requestData, kdniao_AppKey);
        $result=sendPost(kdniao_ReqURL, $datas);

        //根据公司业务处理返回的信息......
        return $result;
    }

    /**
     * XML方式 查询订单物流轨迹
     */
    function getOrderTracesByXml(){
        $requestData= "<?xml version=\"1.0\" encoding=\"utf-8\" ?>".
            "<Content>".
            "<OrderCode></OrderCode>".
            "<ShipperCode>SF</ShipperCode>".
            "<LogisticCode>589707398027</LogisticCode>".
            "</Content>";

        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '1',
        );
        $datas['DataSign'] = encrypt1($requestData, kdniao_AppKey);
        $result=sendPost(kdniao_ReqURL, $datas);

        //根据公司业务处理返回的信息......

        return $result;
    }

    function encrypt1($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }


    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(!isset($url_info['port']))
        {
            $url_info['port']=80;
        }
        // echo $url_info['port'];
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }
}

/**
 * 根据key删除数组中指定元素
 * @param  array  $arr  数组
 * @param  string/int  $key  键（key）
 */
if (! function_exists('array_remove_by_key')) {
    function array_remove_by_key($arr, $key){
        if(!array_key_exists($key, $arr)){
            return $arr;
        }
        $keys = array_keys($arr);
        $index = array_search($key, $keys);
        if($index !== FALSE){
            array_splice($arr, $index, 1);
        }
        return $arr;
    }
}
if (! function_exists('postCurl')) {
    function postCurl($url,$post,$cookie=null){//get https的内容
        $post_str = '';
        foreach ($post as $k=>$v) {
            $post_str = empty($post_str)?$post_str:$post_str.'&';
            $post_str .= $k.'='.urlencode($v);
        }
        $ch = curl_init();//新建curl
        curl_setopt($ch, CURLOPT_URL, $url);//url
        curl_setopt($ch, CURLOPT_POST, 1);  //post
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);//post内容
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        if($cookie)
            curl_setopt($ch,CURLOPT_COOKIE,$cookie);
        $res =  curl_exec($ch); //输出
        $arr= json_decode($res,true);
        curl_close($ch);
        return $arr;
    }
}

function http_post_json($url, $jsonStr,$headers=null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($headers){
        $headers = array_merge($headers, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        ]);
    }else{
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        ];
    }
    $fff = [CURLOPT_SSL_VERIFYPEER=>false];
    foreach ($fff as $k=>$v) {
        curl_setopt($ch, $k, $v);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array($httpCode, $response);
}


function saveP($data,$p=null){
    $_data = [
        'text'=>$data['text'],
        'value'=>$data['value'],
    ];
    if($p){
        $_data['pid'] = $p->id;
        $_data['level'] = $p->level+1;
    }
    $has = \App\Models\Province::where('value', $_data['value'])->first();
    if($has){
        \App\Models\Province::where('id',$has['id'])->update($_data);
    }else{
        $has = \App\Models\Province::create($_data);
    }
    if(isset($data['children'])){
        $children = $data['children'];
        if(is_array($children)){
            foreach ($children as $child) {
                saveP($child,$has);
            }
        }
    }
}

function getModelArray($table_name,$key,$value,$title,$where=[],callable $func=null)
{
    $data = [];
    $data[] = $title;
    $models = \Illuminate\Support\Facades\DB::table($table_name)->where($where)->get();
    foreach ($models as $model) {
        if(is_array($value)){
            $__value = '';
            foreach ($value as $vv) {
                $__value .= $model->{$vv};
            }
            $data[$model->{$key}] = $__value;
        }else{
            $data[$model->{$key}] = $model->{$value};
        }
        if($func!==null){
            $data[$model->{$key}] .= '-'.$func($model);
        }
    }
    return $data;
}

//-----------musa--start---------------

function getMoodlePICURL($logo){
    global $CFG;
    if(!$CFG)
        requireMoodleConfig();
    require_once($CFG->dirroot . '/lib/weblib.php');
    $file = app('db')->connection('moodle')->table('files')->where('filesize','>',0)->where('itemid', $logo)->first();
    $url = \moodle_url::make_pluginfile_url($file->contextid, $file->component, $file->filearea, $file->itemid,
        $file->filepath, $file->filename, false);
    $host = $url->get_host();
    $port = $url->get_port()?$url->get_port():'80';
    $slashargument = $url->get_slashargument();
    return $url->get_scheme().'://'."$host:$port/draftfile.php$slashargument";
}

function getAvatarFullUrl($url){
    return getPicFullUrl($url, false);
}
function getPicFullUrl($url, $hasDefault=true){
    if($url){
        if(is_numeric($url)){
            return getMoodlePICURL($url);
        }else{
            return config('app.url').'/storage/'.$url;
        }
    }elseif($hasDefault){
        return config('app.url').'/static/pic/musa_logo.png';
    }else{
        return null;
    }
}

function getCompanyLogo($logo){
    if($logo)
        $full_logo = getPicFullUrl($logo);
    else
        $full_logo = config('app.url').'/static/pic/musa_logo.png';
    return $full_logo;
}

function getOptionsText(&$model){
    global $options;
    if(!$options){
        $options = \App\Models\DataMap::get(['id','name']);
        $options->load('options');
    }
//    if(){
//
//    }

    foreach ($options as $map) {
        $name = $map->name;
        $new_name = $name.'_text';
        if(isset($model[$name]) && is_int($model[$name])) {
            $_ops = $map->options->keyBy('value')->toArray();
            $model->{$new_name} = '';
            if(isset($_ops[$model->{$name}])){
                $model->{$new_name} = $_ops[$model->{$name}]['text'];
            }
        }
    }
}

/**
 * 准备工作完毕 开始计算年龄函数
 * @param  $birthday 出生时间 uninx时间戳
 * @param  $time 当前时间
 **/
function getAge($birthday){
    $birthday = strtotime($birthday);
    //格式化出生时间年月日
    $byear=date('Y',$birthday);
    $bmonth=date('m',$birthday);
    $bday=date('d',$birthday);

    //格式化当前时间年月日
    $tyear=date('Y');
    $tmonth=date('m');
    $tday=date('d');

    //开始计算年龄
    $age=$tyear-$byear;
    if($bmonth>$tmonth || $bmonth==$tmonth && $bday>$tday){
        $age--;
    }
    if($age<1)
        $age = 1;
    return $age;
}

function substr_text($str, $start, $length, $charset="utf-8", $suffix="")
{
    if(function_exists("mb_substr")){
        return mb_substr($str, $start, $length, $charset).$suffix;
    }
    elseif(function_exists('iconv_substr')){
        return iconv_substr($str,$start,$length,$charset).$suffix;
    }
    $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    return $slice.$suffix;
}



function getCurl($url,$array=[],$returnjson=true){//get https的内容
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//不输出内容
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0");
    $result= curl_exec($ch);
    curl_close($ch);
    $result = str_replace('NaN',0,$result);
    if($returnjson)
        $result= json_decode($result,true);
    return $result;
}

function requireMoodleConfig()
{
    require_once(app()->basePath().'/musa/config.php');
}

function getMoodleRoot()
{
    return app()->basePath().'/musa';
}

function getDays($start_time, $end_time=null)
{
    if(!$end_time)
        $end_time = time();
    if(!is_int($start_time))
        $start_time = strtotime($start_time);
    if(!is_int($end_time))
        $end_time = strtotime($end_time);
    return (int)ceil(($end_time - $start_time)/(3600*24));
}

function sendLogsEmail($logs)
{
    global $LOGIN_USER;
    //给负责人发送邮件通知
    if(count($logs)>0){
        $logObj = $logs[0];
        $recruit = $logObj->recruit;
        $entrust = $logObj->entrust;
        if($entrust){
            if($entrust->leading_id && $entrust->leading_id!=$LOGIN_USER->id && $entrust_leading = \App\Models\User::find($entrust->leading_id)){
                if($entrust_leading->email){
                    try {
                        \Illuminate\Support\Facades\Mail::to($entrust_leading->email)->send(new \App\Mail\RecruitResumeLogEmail($logs));
                    } catch (Exception $e) {
                    }
                }
            }
        }
        if($recruit->leading_id && $recruit->leading_id!=$LOGIN_USER->id && $leading = \App\Models\User::find($recruit->leading_id)){
            if($leading->email && (!isset($entrust_leading->email) || $entrust_leading->email!=$leading->email)){
                try {
                    \Illuminate\Support\Facades\Mail::to($leading->email)->send(new \App\Mail\RecruitResumeLogEmail($logs));
                } catch (Exception $e) {
                }
            }
        }
//        if(!$entrust || ($entrust->leading_id!=$recruit->leading_id)){
//            if($recruit->leading_id && $recruit->leading_id!=$LOGIN_USER->id && $leading = \App\Models\User::find($recruit->leading_id)){
//                if($leading->email){
//                    try {
//                        \Illuminate\Support\Facades\Mail::to($leading->email)->send(new \App\Mail\RecruitResumeLogEmail($logs));
//                    } catch (Exception $e) {
//                    }
//                }
//            }
//        }
    }
}

function sendLeadingEmail($recruit, $entrust, $user)
{
    if(!$user)
        return;

    if($user->user_id)
        $user = $user->user;
    global $LOGIN_USER;
    //给负责人发送邮件通知
    if($entrust){
        if($entrust->leading_id!=$user->id && $user->id!=$LOGIN_USER->id){
            if($user->email){
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\RecruitLeadingUpdateEmail($recruit, $entrust));
                } catch (Exception $e) {
                }
            }
        }
    }else{
//        if($recruit->leading_id!=$user->id){
        if($recruit->leading_id!=$user->id && $user->id!=$LOGIN_USER->id){
            if($user->email){
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\RecruitLeadingUpdateEmail($recruit, $entrust));
                } catch (Exception $e) {
                }
            }
        }
    }
}

//文件输出浏览器下载
function upload($path, $pdfName)
{
    if (!file_exists($path)) {
        echo '文件不存在';
        exit;
    }
    $filename = realpath($path); //文件名
    Header("Content-type:  application/octet-stream ");
    Header("Accept-Ranges:  bytes ");
    Header("Accept-Length: " . filesize($filename));
    header("Content-Disposition:  attachment;  filename= $pdfName");
    echo file_get_contents($filename);
    readfile($filename);
    unlink($path);
}

function isEmpty($str)
{
    if($str===null)
        return true;
    if($str===false)
        return true;
    if($str==='')
        return true;
    if(is_array($str) && count($str)==0)
        return true;
    return false;
}

function getEducationValue($str)
{
    if($str=='本科' or $str=='学士'){
        return 2;
    }elseif($str=='大专' or $str=='专科'){
        return 1;
    }elseif($str=='研究生' or $str=='硕士' or $str=='硕士研究生'){
        return 3;
    }elseif($str=='博士' or $str=='博士后'){
        return 4;
    }
    return 0;
}

function moreTime($at1, $at2)
{
    if(!is_int($at1))
        $at1 = strtotime($at1);
    if(!is_int($at2))
        $at2 = strtotime($at2);
    return $at1 > $at2;
}



function getObjRelationBelongsTo(&$list, $relationName, $model, $fieldName, $id='id')
{
    $ids = [];
    foreach ($list as $item) {
        $ids[] = $item->{$fieldName};
    }

    $array = $model->whereIn($id, $ids)->get()->keyBy($id)->toArray();
    foreach ($list as &$item) {
        if(isset($array[$item->{$fieldName}])){
            $item->{$relationName} = $array[$item->{$fieldName}];
        }else{
            $item->{$relationName} = null;
        }
    }
}


function checkAuthByCompany($recruit , $is_recruit=true)
{
    $company = TokenRepository::getCurrentCompany();
    if($is_recruit && $recruit->company_id!=$company->id){
        throw new \Exception("非法越权操作!");
    }elseif ($recruit->company_id!=$company->id && $recruit->third_party_id!=$company->id){
        throw new \Exception("非法越权操作!");
    }
}

function checkAuthByUser($recruit)
{
    $user = TokenRepository::getUser();
    if($user && $recruit->user_id!=$user->id){
        throw new \Exception("非法越权操作!");
    }
}

function questionPicReplace($question, $quiz)
{
    $course = $quiz->courseObj;
    $module = DB::connection('moodle')->table('course_modules')->where('course', $course->id)->where('instance', $quiz->id)->orderBy('id', 'desc')->first();
    requireMoodleConfig();
    $text = $question->questiontext;

    global $CFG;
    if($module){
        $context = DB::connection('moodle')->table('context')->where('contextlevel', 70)->where('instanceid', $module->id)
            ->orderBy('id', 'desc')->first();
        if($context){
            $attempt = DB::connection('moodle')->table('question_attempts')->where('questionid', $question->id)->orderBy('id', 'desc')->first();
            if($attempt){
//                http://39.100.105.180/pluginfile.php/168/question/questiontext/380/1/505/musa_logo.png

                $pattern="/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/";
                preg_match_all($pattern,$text,$match);
                if(isset($match[1])){
                    foreach ($match[1] as $k=>$v) {
//                        $_full = $match[0][$k];
                        $_src = $match[1][$k];
                        $_file_arr = explode('/', $_src);
                        $_file = $_file_arr[count($_file_arr)-1];
                        $url = $CFG->wwwroot."/pluginfile.php/{$context->id}/question/questiontext/{$attempt->questionusageid}/{$attempt->variant}/{$attempt->questionid}/{$_file}";
                        if($url){
                            $text = str_replace($_src, $url, $text);
                        }
                    }
                }
            }
        }
    }

    return $text;
}

function getCompanyRoleName($company, $user=null)
{
    if(isset($company->pivot)){
        $company_role_id = $company->pivot->company_role_id;
    }else{
        $company_role_id = CompanyUser::where('user_id', $user->id)->where('company_id', $company->id)->value('company_role_id');
    }
    if($company_role_id){
        $role = CompanyRole::find($company_role_id);
    }
    if(isset($role))
        return $role->name;
    else
        return '';
}

function getCompanyRoleNames($company, $user=null)
{
    if(isset($company->pivot)){
        $company_role_id = $company->pivot->company_role_id;
    }else{
        $company_role_id = CompanyUser::where('user_id', $user->id)->where('company_id', $company->id)->value('company_role_id');
    }

    $role_ids = CompanyUserRole::where('company_id', $company->id)->where('user_id', $user->id)->pluck('role_id')->toArray();
    if($company_role_id)
         array_push($role_ids, $company_role_id);

    $role_ids = array_unique($role_ids);


    $role_names = CompanyRole::whereIn('id', $role_ids)->pluck('name')->toArray();
    return implode(';', $role_names);
}

function getCompanyRole($company, $user=null)
{
    if(isset($company->pivot)){
        $company_role_id = $company->pivot->company_role_id;
    }else{
        $company_role_id = CompanyUser::where('user_id', $user->id)->where('company_id', $company->id)->value('company_role_id');
    }
    if($company_role_id){
        $role = CompanyRole::find($company_role_id);
    }
    if(isset($role))
        return $role;
    else
        return null;
}

function getCompanyRoles($company, $user=null)
{
    if(is_object($user))
        $user_id = $user->id;
    else
        $user_id = $user['id'];
    if(is_object($company))
        $company_id = $company->id;
    else
        $company_id = $company['id'];
    $company_role_id = CompanyUserRole::where('user_id', $user_id)->where('company_id', $company_id)->pluck('role_id')->toArray();
    $role_id = CompanyUser::where('user_id', $user_id)->where('company_id', $company_id)->value('company_role_id');
    if($role_id)
        $company_role_id[] = $role_id;
    $roles = CompanyRole::whereIn('id', $company_role_id)->get();
    return $roles;
}

function checkSkillsGrade($resume_skills, $job_skills)
{
    $skills_data = [];
    $job_skills_count = $job_skills->count();
    $skills_score = 0;
    //单分
    $config_skill_score = 100/(DataMapOption::where('data_map_id',10)->count());
    $config_skill_data = DataMapOption::where('data_map_id',10)->get()->keyBy('value')->toArray();
    foreach ($job_skills as $job_skill) {
        $_job_skill_name = $job_skill->name;
        $_job_skill_id = $job_skill->pivot->skill_id;
        $_job_skill_level = $job_skill->pivot->skill_level;
        if(isset($resume_skills[$_job_skill_id])){
            $resume_skill = $resume_skills[$_job_skill_id];
            if(($_job_skill_level > $resume_skill['skill_level']))
                $_score = (int)(100 - ($_job_skill_level - $resume_skill['skill_level'])*$config_skill_score);
            else
                $_score = 100;
            $skills_data[] = [
                'skill_name'=>$_job_skill_name,
                'job_level'=>$_job_skill_level,
                'job_level_text'=>$config_skill_data[$_job_skill_level]['text'],
                'resume_level'=>$resume_skill['skill_level'],
                'resume_level_text'=>$config_skill_data[$resume_skill['skill_level']]['text'],
                'sroce'=>$_score,
            ];
            $skills_score += (int)($_score/$job_skills_count);
        }else{
            $skills_score += 0;
            $skills_data[] = [
                'skill_name'=>$_job_skill_name,
                'job_level'=>$_job_skill_level,
                'job_level_text'=>$config_skill_data[$_job_skill_level]['text'],
                'resume_level'=>0,
                'resume_level_text'=>'无',
                'sroce'=>0,
            ];
        }
    }
    return [$skills_score, $skills_data];
}


/**

 * @author ja颂

 * 把数字1-1亿换成汉字表述，如：123->一百二十三

 * @param [num] $num [数字]

 * @return [string] [string]

 */
function numToWord($num)

{

    $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');

    $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');

    $chiStr = '';

    $num_str = (string)$num;

    $count = strlen($num_str);

    $last_flag = true; //上一个 是否为0

    $zero_flag = true; //是否第一个

    $temp_num = null; //临时数字

    $chiStr = '';//拼接结果

    if ($count == 2) {//两位数

        $temp_num = $num_str[0];

        $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];

        $temp_num = $num_str[1];

        $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];

    }else if($count > 2){

        $index = 0;

        for ($i=$count-1; $i >= 0 ; $i--) {

            $temp_num = $num_str[$i];

            if ($temp_num == 0) {

                if (!$zero_flag && !$last_flag ) {

                    $chiStr = $chiNum[$temp_num]. $chiStr;

                    $last_flag = true;

                }

            }else{

                $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;

                $zero_flag = false;

                $last_flag = false;

            }

            $index ++;

        }

    }else{

        $chiStr = $chiNum[$num_str[0]];

    }

    return $chiStr;

}
function getYearsText($at1, $at2){
    if(!$at1)
        return '';
    if(!is_int($at1))
        $at1 = strtotime($at1);
    if(!is_int($at2))
        $at2 = strtotime($at2);

    $time = $at2-$at1;

    $one = 3600*24*365;

    if($time<$one){
        return '不足一年';
    }
    return numToWord(floor($time/$one)).'年';
}


function handleRepeatEmailRegister($email){
    if ($has = User::where('mnethostid',1)->where('username', $email)->first()){
        $top = User::where('mnethostid',1)->where('username','like', "$email%")->orderBy('username','desc')->first();
        $has->username = $top->username.'!';
        $has->save();
    }
}

function checkPermission($permission_id,$company_id=false, $user_id=false)
{
    if(!$company_id)
        $company_id = TokenRepository::getCurrentCompany()->id;
    if(!$user_id)
        $user_id = TokenRepository::getUser()->id;
    global $CompanyUser;
    global $CompanyUserRoles;
    if(!$CompanyUser){
        $CompanyUser = CompanyUser::where('company_id', $company_id)->where('user_id', $user_id)->first();
    }
    if(!$CompanyUser)
        return false;

    if(!$CompanyUserRoles){
        //company_role_id
        $company_role_id = null;
        if($CompanyUser->company_role_id){
            $company_role_id = $CompanyUser->company_role_id;
        }

        $role_ids = CompanyUserRole::where('company_id', $company_id)->where('user_id', $user_id)->pluck('role_id')->toArray();
        if($company_role_id)
            array_push($role_ids, $company_role_id);

        $role_ids = array_unique($role_ids);

        $CompanyUserRoles = $role_ids;
    }
    if(!$CompanyUserRoles || !is_array($CompanyUserRoles) || count($CompanyUserRoles)==0){
        return false;
    }
    if(in_array(1, $CompanyUserRoles))
        return true;

    return CompanyRolePermission::whereIn('company_role_id',$CompanyUserRoles)->where('company_permission_id', $permission_id)->exists();
}
function getPermissionScope($company_id, $user_id, $permission_id)
{
    global $CompanyUser;
    global $CurrentDepartment;
    global $Department_data1;
    global $Department_data2;
    if(!$CompanyUser){
        $CompanyUser = CompanyUser::where('company_id', $company_id)->where('user_id', $user_id)->first();
    }
    if(!$CurrentDepartment && $CompanyUser->department_id){
        $CurrentDepartment = CompanyDepartment::find($CompanyUser->department_id);
    }
    $obj = \App\Models\CompanyUserPermissionScope::where('key', "{$permission_id}_{$company_id}_{$user_id}")->first();
    if(!$obj || $obj->type==1){
        return null;
    }elseif ($obj->type==2){
        if(!$Department_data1){
            if($CurrentDepartment && $CurrentDepartment->level==1){
                $Department_data1 = array_merge([$CurrentDepartment->id], $CurrentDepartment->children->pluck('id')->toArray());
            }elseif($CurrentDepartment && $CurrentDepartment->level==2){
                $parentDep = $CurrentDepartment->parent;
                $Department_data1 = array_merge([$parentDep->id], $parentDep->children->pluck('id')->toArray());
            }elseif (!$CurrentDepartment){
                $Department_data1 = [];
            }
        }
        if($obj->department_ids){
            return array_merge($Department_data1, explode(',', $obj->department_ids));
        }
        return $Department_data1;
    }elseif ($obj->type==3){
        if(!$Department_data2){
            if($CurrentDepartment && $CurrentDepartment->level==1){
                $Department_data2 = array_merge([$CurrentDepartment->id], $CurrentDepartment->children->pluck('id')->toArray());
            }elseif($CurrentDepartment && $CurrentDepartment->level==2){
                $Department_data2 = [$CurrentDepartment->id];
            }elseif (!$CurrentDepartment){
                $Department_data2 = [];
            }
        }
        if($obj->department_ids){
            return array_merge($Department_data2, explode(',', $obj->department_ids));
        }
        return $Department_data2;
    }
}

function getMonthDate($date){
    $firstday = date('Y-m-01 00:00:00', strtotime($date));
    $lastday = date('Y-m-d 23:59:59', strtotime("$firstday +1 month -1 day"));

    return [$firstday,$lastday];
}
