<?php
/**
 * Created by PhpStorm.
 * User: anyuzhe
 * Date: 2017/3/27
 * Time: 10:38
 */

namespace App\ZL\Library;


class Openssl
{
    public static function encryptByAes($key,$iv,$data)
    {
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $encrypted;
    }

    public static function decryptByAes($key,$iv,$data)
    {
        $encrypted = base64_decode($data);
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $decrypted;
    }
}