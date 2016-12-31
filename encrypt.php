<?php
/**
 * Created by PhpStorm.
 * User: wanmin
 * Date: 16/12/31
 * Time: 下午5:04
 *  加解密 同一个密码会生成不同的字符串!不易替换登录
 */

class Encrypt
{

    /**
     * 验证编码
     *
     * @param String $string
     * @param String $operation
     * @param Integer $expiry
     * @param String $key
     * @return String
     */
    private static function auth($string, $key, $expiry = 0, $operation = 'decode')
    {
        $ckey_length = 4;
        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string = $operation == 'decode' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++)
        {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++)
        {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'decode')
        {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16))
            {
                return substr($result, 26);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
    /**
     * 解密
     *
     * @param String $string
     * @param String $key
     * @param Integer $expiry
     * @return String
     */
    public static function decode($string, $key, $expiry = 0)
    {
        return self::auth($string, $key, $expiry, __FUNCTION__);
    }

    /**
     * 加密
     *
     * @param String $string
     * @param String $key
     * @param Integer $expiry
     * @return String
     */
    public static function encode($string, $key, $expiry = 0)
    {
        return self::auth($string, $key, $expiry, __FUNCTION__);
    }

}

$key = 'wsd222222222222111222ddee';
$data = new Encrypt();
$enString = $data->encode("abcdefghijk!!",$key);
echo $enString."\n";
echo $data->decode($enString,$key);