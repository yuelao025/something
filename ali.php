<?php 

require('./aliyun-oss-php-sdk-2.0.6/autoload.php');

use OSS\OssClient;
use OSS\Core\OssException;


class ali
{
	  /**
     * ali oss demo
    */
    public  static function actionAli()
    {
        $accessKeyId = "Ty60ASvP39nV2bwf"; ;
        $accessKeySecret = "Qt8wxVgZkIJkaUuhEhOQWET1mzcT53";
        $endpoint = "oss-cn-shanghai.aliyuncs.com";
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
//            var_dump($ossClient);
        } catch (OssException $e) {
            print $e->getMessage();
        }

        $bucket = "miaoshi1";
        $object = "sae";
        $content = "Hello, OSS!"; // 上传的文件内容
        try {
            echo 11;
            $ossClient->putObject($bucket, $object, $content);
        } catch (OssException $e) {
            echo 22;
            print $e->getMessage();
        }


//        $bucket = "mydemotest123";
//        try {
//            echo 11;
//            $ossClient->createBucket($bucket);
//        } catch (OssException $e) {
//            echo 22;
//            print $e->getMessage();
//        }

    }
}

ali::actionAli();
