<?php
namespace App\ZL\Library;

use Illuminate\Support\Str;

class Context
{
    protected static $data;

    public static function set($array)
    {
        self::$data = $array;
    }

    public static function add($name,$val)
    {
        self::$data[$name] = $val;
    }

    public static function get($name=null)
    {
        if($name){
            return self::$data[$name];
        }
        return self::$data;
    }
}
