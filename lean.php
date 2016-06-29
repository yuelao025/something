<?php 


require('./LeanCloud/autoload.php');

use LeanCloud\LeanClient;
use LeanCloud\LeanObject;
use LeanCloud\CloudException;


class Lean
{
   
   public  static function demo()
    {
// 参数依次为 appId, appKey, masterKey
        LeanClient::initialize("VR6qospg7s8JIq7eGMJ6GhnJ-gzGzoHsz", "qQLD81bSwGU1CNflkSJgP3fG", "IeuoNdKJHhCkHCxU46PcAUnY");

// 我们目前支持 CN 和 US 区域，默认使用 CN 区域，可以切换为 US 区域
//        LeanClient::useRegion("US");

//        var_dump( LeanClient::get("/date")); // 获取服务器时间


        $obj = new LeanObject("miaoshitutu");
        $obj->set("name", "alice");
        $obj->set("height", 620.0);
        $obj->set("weight", 4.5);
        $obj->set("birthdate", new \DateTime());
        try {
            $obj->save();
        } catch (CloudException $ex) {
            // CloudException 会被抛出，如果保存失败
        }

// 获取字段值
        echo $obj->get("height");
        var_dump( $obj->get("birthdate"));

// 原子增加一个数
        $obj->increment("age", 1);

// 在数组字段中添加，添加唯一，删除
// 注意: 由于API限制，不同数组操作之间必须保存，否则会报错
        $obj->addIn("colors", "blue");
        $obj->save();
        $obj->addUniqueIn("colors", "orange");
        $obj->save();
        $obj->removeIn("colors", "blue");
        $obj->save();

// 在云存储上删除它
//        $obj->destroy();

    }

}

Lean::demo();


