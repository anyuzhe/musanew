<?php
/**
 * Created by PhpStorm.
 * User: anyuzhe
 * Date: 2017/3/24
 * Time: 10:37
 */

namespace App\ZL\ORG\Xcx;


class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            echo 'codelen:' . strlen($this->sessionKey);
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $pc = new Prpcrypt($aesKey);
        $result = $pc->decrypt($aesCipher, $aesIV);
        if ($result[0] != 0) {
            return $result[0];
        }

        $dataObj = json_decode($result[1]);
        if ($dataObj == NULL) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result[1];
        return ErrorCode::$OK;
    }
}

/**
 * Prpcrypt class
 *
 *
 */
class Prpcrypt
{
    public $key;

    function __construct( $k )
    {
        $this->key = $k;
    }

    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt( $aesCipher, $aesIV )
    {
        try {
            //解密
            $decrypted = openssl_decrypt($aesCipher, 'aes-128-cbc',$this->key, OPENSSL_RAW_DATA, $aesIV);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }

        return array(0, $decrypted);
    }

    public function encrypt($aesCipher, $aesIV)
    {
        $encrypted = openssl_encrypt($aesCipher, 'aes-128-cbc', $this->key, OPENSSL_RAW_DATA, $aesIV);
        return base64_encode($encrypted);
    }

}

/**
 * error code 说明.
 * <ul>

 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}

?>