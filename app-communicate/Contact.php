<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 17/1/7
 * Time: 下午7:58
 */

class Contact
{
    /**
     * 密钥
     * @var array
     */
    static private $privateKey = array(
        'iphone' => 'a85bb0674e08986c6b115d5e3a111111',
        'android' => 'fd4ad5fcfa0de589ef238c0e7331b585'
    );

    /**
     * 初始化
     * @param array $params
     * @throws \Exception
     */
    static protected function init(array $params)
    {
        $clientSecret = $params['client_secret'];
        unset($params['client_secret'], $params['project'], $params['version'],
            $params['class_name'], $params['method_name']);
        $params['private_key'] = self::$privateKey[strtolower($params['client_type'])];
        $_params = self::packageSort($params);
        $_makeKey = self::makeSign($_params);
        $verifySign = self::verifySign($_makeKey, $clientSecret);
        if ($verifySign == false) {
            throw new \Exception($message = "数据验证错误", $code = 500);
        }
    }

    /**
     * 排序参数
     * @param array $package
     * @return array
     */
    static protected function packageSort(array $package)
    {
        ksort($package);
        reset($package);
        return $package;
    }

    /**
     * 组合签名
     * @param array $package
     * @return string
     */
    static protected function makeSign(array $package)
    {
        $packageList = array();
        foreach ($package as $key => $val) {
            $packageList[] = trim($key . '=' . urldecode($val));
        }
        return strtolower(md5(implode('&', $packageList)));
    }

    /**
     * 校验签名
     * @param $submitSign
     * @param $makeSign
     * @return bool
     */
    static protected function verifySign($submitSign, $makeSign)
    {
        return strtolower($submitSign) == strtolower($makeSign);
    }

    /**
     * 匹配UA
     * @return array
     */
    static protected function matchUserAgent()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return array();
        }
        $subject = $_SERVER['HTTP_USER_AGENT'];
        preg_match("/\((.*?)\)/i", $subject, $matches);
        $userAgentData = array();
        if (count($matches) == 2) {
            $ua = $matches[1];
            $_userAgent = explode(';', $ua);
            foreach ($_userAgent as $uaVal) {
                $uaData = explode('/', $uaVal);
                if (count($uaData) == 2) {
                    list($key, $val) = explode('/', $uaVal);
                    $userAgentData[$key] = $val;
                }
            }
        }
        return $userAgentData;
    }


    /**
     * @param $code
     * @param null $message
     * @param null $data
     * @return array
     */
    static protected function result($code, $message = null, $data = null)
    {
        $dataString = json_encode($data);
        return array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'md5' => md5($dataString)
        );
    }
}