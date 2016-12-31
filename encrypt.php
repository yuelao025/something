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


    /**
     * 获取编码
     *
     * @param String $plaintext
     * @param String $salt
     * @param String $encryption
     * @param bool $show_encrypt
     * @return String
     */
    public static function getCrypted($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
    {
        $salt = self::getSalt($encryption, $salt, $plaintext);
        switch ($encryption)
        {
            case 'plain' :
                return $plaintext;
            case 'sha' :
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));
                return ($show_encrypt) ? '{SHA}' . $encrypted : $encrypted;
            case 'crypt' :
            case 'crypt-des' :
            case 'crypt-md5' :
            case 'crypt-blowfish' :
                return ($show_encrypt ? '{crypt}' : '') . crypt($plaintext, $salt);
            case 'md5-base64' :
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));
                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
            case 'ssha' :
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext . $salt) . $salt);
                return ($show_encrypt) ? '{SSHA}' . $encrypted : $encrypted;
            case 'smd5' :
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext . $salt) . $salt);
                return ($show_encrypt) ? '{SMD5}' . $encrypted : $encrypted;
            case 'aprmd5' :
                $length = strlen($plaintext);
                $context = $plaintext . '$apr1$' . $salt;
                $binary = self::_bin(md5($plaintext . $salt . $plaintext));
                for ($i = $length; $i > 0; $i -= 16)
                {
                    $context .= substr($binary, 0, ($i > 16 ? 16 : $i));
                }
                for ($i = $length; $i > 0; $i >>= 1)
                {
                    $context .= ($i & 1) ? chr(0) : $plaintext[0];
                }
                $binary = self::_bin(md5($context));

                for ($i = 0; $i < 1000; $i++)
                {
                    $new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
                    if ($i % 3)
                    {
                        $new .= $salt;
                    }
                    if ($i % 7)
                    {
                        $new .= $plaintext;
                    }
                    $new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
                    $binary = self::_bin(md5($new));
                }

                $p = array();
                for ($i = 0; $i < 5; $i++)
                {
                    $k = $i + 6;
                    $j = $i + 12;
                    if ($j == 16)
                    {
                        $j = 5;
                    }
                    $p[] = self::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
                }

                return '$apr1$' . $salt . '$' . implode('', $p) . self::_toAPRMD5(ord($binary[11]), 3);

            case 'md5-hex' :

            default :
                $encrypted = ($salt) ? md5($plaintext . $salt) : md5($plaintext);
                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
        }
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $encryption
     * @param unknown_type $seed
     * @param unknown_type $plaintext
     * @return unknown
     */
    private static function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '')
    {
        switch ($encryption)
        {
            case 'crypt' :
            case 'crypt-des' :
                if ($seed)
                {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
                }
                else
                {
                    return substr(md5(mt_rand()), 0, 2);
                }
                break;

            case 'crypt-md5' :
                if ($seed)
                {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
                }
                else
                {
                    return '$1$' . substr(md5(mt_rand()), 0, 8) . '$';
                }
                break;

            case 'crypt-blowfish' :
                if ($seed)
                {
                    return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16);
                }
                else
                {
                    return '$2$' . substr(md5(mt_rand()), 0, 12) . '$';
                }
                break;

            case 'ssha' :
                if ($seed)
                {
                    return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
                }
                else
                {
                    return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
                }
                break;

            case 'smd5' :
                if ($seed)
                {
                    return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
                }
                else
                {
                    return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
                }
                break;

            case 'aprmd5' :
                /* 64 characters that are valid for APRMD5 passwords. */
                $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

                if ($seed)
                {
                    return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
                }
                else
                {
                    $salt = '';
                    for ($i = 0; $i < 8; $i++)
                    {
                        $salt .= $APRMD5{rand(0, 63)};
                    }
                    return $salt;
                }
                break;

            default :
                $salt = '';
                if ($seed)
                {
                    $salt = $seed;
                }
                return $salt;
                break;
        }
    }

    private static function genRandom($length = 8)
    {
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $len = strlen($salt);
        $makepass = '';

        $stat = @stat(__FILE__);
        if (empty($stat) || !is_array($stat))
            $stat = array(
                php_uname()
            );

        mt_srand(crc32(microtime() . implode('|', $stat)));

        for ($i = 0; $i < $length; $i++)
        {
            $makepass .= $salt[mt_rand(0, $len - 1)];
        }

        return $makepass;
    }

    private static function _toAPRMD5($value, $count)
    {
        /* 64 characters that are valid for APRMD5 passwords. */
        $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $aprmd5 = '';
        $count = abs($count);
        while (--$count)
        {
            $aprmd5 .= $APRMD5[$value & 0x3f];
            $value >>= 6;
        }
        return $aprmd5;
    }

    private static function _bin($hex)
    {
        $bin = '';
        $length = strlen($hex);
        for ($i = 0; $i < $length; $i += 2)
        {
            $tmp = sscanf(substr($hex, $i, 2), '%x');
            $bin .= chr(array_shift($tmp));
        }
        return $bin;
    }

    /**
     * 组合一个安全的密码
     * @param $code
     * @return String
     */
    public static function makePass($code)
    {
        $salt = self::genRandom(32);
        $crypt = self::getCrypted($code . $salt, $salt);
        return $crypt . ':' . $salt;
    }

    /**
     * 验证密码
     *
     * @param String $inputPassword
     * @param String $password
     * @return bool
     */
    public static function authPassword($inputPassword, $password)
    {
        if (empty($password) || empty($inputPassword))
        {
            return false;
        }
        $passwordList = explode(':', trim($password));
        if (count($passwordList) != 2)
        {
            return false;
        }
        list($crypt, $salt) = $passwordList;
        $decode = self::getCrypted($inputPassword . $salt, $salt);
        if ($crypt != $decode)
        {
            return false;
        }
        return true;
    }


}
//简单使用
$key = 'wsd222222222222111222ddee';
$data = new Encrypt();
//加密
$enString = $data->encode("abcdefghijk!!",$key);
echo $enString."\n";
//解密
echo $data->decode($enString,$key);
echo "\n\n***************\n\n";

//或者生产使用
$passwd = $data->makePass("adads!#%%$");//产生密码
echo $passwd."\n";
var_dump($data->authPassword("adads!#%%$",$passwd));//比较

