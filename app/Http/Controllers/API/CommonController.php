<?php

namespace App\Http\Controllers\API;

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
        require_once('./../musa/config.php');
    }

    public function getMoodleRoot()
    {
        return './../musa';
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
        $token = ExternalToken::where('token', $token)->first();

        return $token;
    }

    public function getUser()
    {
        return TokenRepository::getUser();
    }

    public function getCurrentCompany()
    {
        return TokenRepository::getCurrentCompany();
    }

    /**
     * @param $params
     * @return bool
     * 参数校验
     */
    public function parameter($params, $filter_arr)
    {
        $arr = array_keys($params);
        foreach ($filter_arr as $val) {
            if (!in_array($val, $arr)) {
                return $this->apiReturnJson(1000, null, "缺少参数 {$val}");
            } else {
                if (!isset($params[$val])) {
                    return $this->apiReturnJson(1001,null, "参数 {$val} 不能为空");
                }
            }
        }
    }

    /*
     * 生成xml数据格式
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * return string
     */
    public static function apiReturnXml($code = 0, $message = '', $data = array())
    {
        header("Content-Type:application/xml;charset=UTF-8");
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods:POST,GET");
        header("Access-Control-Allow-Headers:x-requested-with,content-type");
        if (!is_numeric($code)) {
            return '';
        }
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        //构造xml数据
        //使返回的数据以xml格式显示
        header("Content-Type:text/xml");
        //开始拼xml数据
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        //根节点
        $xml .= "<root>";
        //创建一个额外函数来构造
        $xml .= self::xmlToEncode($result);
        $xml .= "</root>";
        return $xml;
    }

    /**
     * /构造xml数据函数
     * @param $data
     * @return string
     */
    public static function xmlToEncode($data)
    {
        $xml = "";
        $attr = "";
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $attr = "id = '{$key}'";
                $key = "item";
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= is_array($value) ? self::xmlToEncode($value) : $value;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * 将xml转换为数组
     * @param string $xml :xml文件或字符串
     * @return array
     */
    public static function xmlToArray($xml)
    {
        //考虑到xml文档中可能会包含<![CDATA[]]>标签，第三个参数设置为LIBXML_NOCDATA
        if (file_exists($xml)) {
            libxml_disable_entity_loader(false);
            $xml_string = simplexml_load_file($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        } else {
            $xml_parser = xml_parser_create();       //建立一个 XML 解析器
            if (!xml_parse($xml_parser, $xml, true)) {   //开始解析一个 XML 文档 成功时返回1，失败时返回0
                xml_parser_free($xml_parser);        //释放解析器资源
                $xml_string = null;
            } else {
                xml_parser_free($xml_parser);        //释放解析器资源
                libxml_disable_entity_loader(true);
                $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        }
        $result = json_decode(json_encode($xml_string), true);
        return $result;
    }

    /**
     * @param string $url
     * @param bool $post_data
     * @param array $header
     * @return mixed|string
     * post请求
     */
    public static function curl_post($url = '', $post_data = false, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        //设置头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl验证
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output);
    }

    /**
     * @param string $url
     * @param bool $put_data
     * @param array $header
     * @return mixed|string
     * put 请求
     */
    public static function curl_put($url = '', $put_data = false, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //定义请求地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//定义是否直接输出返回流
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); //定义请求类型，必须为大写
        //curl_setopt($ch, CURLOPT_HEADER,1); //定义是否显示状态头 1：显示 ； 0：不显示
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//定义header
        curl_setopt($ch, CURLOPT_POSTFIELDS, $put_data); //定义提交的数据
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);//关闭
        return json_decode($output);
    }

    /**
     * @param $url
     * @param array $header
     * @return mixed|string
     * del 请求
     */
    public static function curl_del($url, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        //设置头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置请求头
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36');

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL认证。
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output);
    }

    /**
     * @param $url
     * @param array $header
     * @return mixed|string
     * get 请求
     */
    public static function curl_get($url, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.98 Safari/537.36');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 16);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output);
    }

    // protected function throwValidationException(Request $request, $validator)
    // {
    //     $response = [
    //         'code' => 400,
    //         'msg'  => $validator->errors()->first(),
    //         'data' => []
    //     ];
    //      throw new ValidationException($validator, $response);
    // }

    public function requestParamFilter($filters, $request) {
        foreach ($request as $key => $value) {
            if (!$value || !in_array($key, $filters)) {
                unset($request[$key]);
            }
        }
        return $request;
    }

}
