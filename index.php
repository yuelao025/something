<?php 

require('./php-sdk-7.0.7/autoload.php');

use Qiniu\Auth;


class Qn
{

    public static function getToken()
    {
        $accessKey = 'Ygc_usWA7Yp2AZfo5LlSkrUC5sGVvpDhaEyo65iw';
        $secretKey = 'id2AWZIch6Yz4JC0Xmb7mp9ugTTLxRTK_-vsiW0_';
        $auth = new Auth($accessKey, $secretKey);

        // 空间名  http://developer.qiniu.com/docs/v6/api/overview/concepts.html#bucket
        $bucket = 'miaoshi';

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // echo $token;
        $reslt = array('uptoken' => $token);
        return json_encode($reslt);

//         // 要上传文件的本地路径
//         $filePath = './images/jiatu.png';

//         // 上传到七牛后保存的文件名
//         $key = 'mmiaos11111.png';
// //http://7xrn4f.media1.z0.glb.clouddn.com/my-jiatu-11.png 可以访问
//         // 初始化 UploadManager 对象并进行文件的上传
//         $uploadMgr = new UploadManager();

//         // 调用 UploadManager 的 putFile 方法进行文件的上传
//         list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
//         echo "\n====> putFile result: \n";
//         if ($err !== null) {
//             echo 11;
//             var_dump($err);
//         } else {
//             echo 22;
//             var_dump($ret);
//         }
    }
       
}

echo Qn::getToken();

