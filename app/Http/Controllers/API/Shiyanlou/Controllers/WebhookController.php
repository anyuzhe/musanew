<?php

namespace App\Http\Controllers\API\Shiyanlou\Controllers;

use DB;
use Illuminate\Support\Facades\Input;
use  App\Http\Controllers\API\CommonController;
use  App\Models\ShiYanLou;

class WebhookController extends CommonController
{
    public function index()
    {
        header("Content-type: text/html; charset=utf-8");
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/webhook';
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) { //创建和写入权限
                return '文件夹创建失败';
            };
        }
        $content = file_get_contents('php://input');
        if ($content) {
            $post = (array)json_decode($content, true);
            if (!$post['id']) {
                $post['id'] = 'webhook' . time();
            }
            $file = $dir . '/' . $post['id'] . '.txt';
            file_put_contents($file, json_encode($post) . PHP_EOL, FILE_APPEND);
            return '推送成功';

        } else {
            return '暂无推送信息';

        }
    }
}
