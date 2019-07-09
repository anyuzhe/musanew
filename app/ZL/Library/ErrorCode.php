<?php
/**
 * Created by PhpStorm.
 * User: anyuzhe
 * Date: 2017/3/28
 * Time: 15:30
 */

namespace App\ZL\Library;


class ErrorCode
{
    public static $modelCanNotFindError = [
        'code' => 430,
        'msg'  => '搜索不到数据'
    ];
    public static $modelSaveError = [
        'code' => 521,
        'msg'  => '数据库保存出错'
    ];
    public static $notSessionIdError = [
        'code' => 411,
        'msg'  => '没有登录会话id'
    ];
    public static $XcxNotLoginError = [
        'code' => 412,
        'msg'  => '用户没有登录到小程序'
    ];
    public static $canNotFindStoreError = [
        'code' => 413,
        'msg'  => '没有找到商店'
    ];
    public static $canNotFindRecordError = [
        'code' => 414,
        'msg'  => '没有找到记录'
    ];
    public static $recordBingedError = [
        'code' => 415,
        'msg'  => '该记录已经绑定会员'
    ];
    public static $goodsExistError = [
        'code' => 416,
        'msg'  => '该商品已经存在'
    ];
    public static $goodsSaleError = [
        'code' => 417,
        'msg'  => '该商品已卖出'
    ];
    public static $fieldError = [
        'code' => 422,
        'msg'  => '提交的字段有错误'
    ];
}