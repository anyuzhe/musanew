<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiYanLou extends Model
{
    const URL = "https://www.shiyanlou.com";
    const ACCESSKEY = 'D0gEGVIIqPoUEnfJEFkpsTkpusxJcnRH';
    const ACCESSSECRET = 'BAR6tEpjwNtG1XCtA6Q2uNln1zqE4A7U';

    private static $header = array(
        'alg' => 'HS256', //生成signature的算法
        'typ' => 'JWT'    //类型
    );

    /**
     * @param array $data
     * @return bool|string
     * 获取令牌
     */
    public static function getToken($str, $path)
    {
        # 注意不要把 jwt_token 一起加入到 query_string 里做签名
        $method = "GET";
        $body = '';
        $message = $method . "\n" . $path . "\n" . $str . "\n" . $body;
        $payload = array(
            "iss" => self::ACCESSKEY,
            "iat" => time(),
            'exp' => time() + 600,
            'jti' => md5(uniqid('JWT')) . time(),
            'sig' => hash("sha256", $message),
        );

        $base64header = self::base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
        $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, self::ACCESSSECRET);
        return $token;
    }

    /**
     * HMACSHA256签名   https://jwt.io/  中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg 算法方式
     * @return mixed
     */
    private static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config = array(
            'HS256' => 'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }

    /**
     * @param $arr
     * @return bool|string
     * 数组字典排序
     */
    public static function sortArr($arr)
    {
        if ($arr) {
            if (!is_array($arr)) {
                return false;
            }
            ksort($arr);
            $data = [];
            foreach ($arr as $key => $val) {
                $data[] = $key . $val;
            }
            return implode('', $data);
        } else {
            return '';
        }

    }

    /**
     * base64UrlEncode
     * @param  $input 需要编码的字符串
     * @return
     */
    private static function base64UrlEncode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
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

//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//ssl验证
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
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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

//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL认证。
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
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            return "CURL Error:" . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output);
    }
}
