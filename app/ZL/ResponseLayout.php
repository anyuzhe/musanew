<?php

namespace App\ZL;

class ResponseLayout
{
    public static function apply($code, $data = [], $message = '', $other = array())
    {
        $res = [];
        $res['data'] = null;
        if ($data || is_array($data)) $res['data'] = $data;
        $res['code'] = $code . '';
        if ($message === '') {
            if (isset(config('errCode')[$code])) {
                $res['message'] = config('errCode')[$code];
            } else {
                if ($res['code'] == 0 && $res['code'] == '0000') {
                    $res['message'] = '成功';
                } else {
                    $res['message'] = '失败';
                }
            }
        } else {
            $res['message'] = $message;
        }
        if (!empty($other)) {
            $res = array_merge($res, $other);
        }
        $res['code'] = (int)$res['code'];
        return $res;
    }
}