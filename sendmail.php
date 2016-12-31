<?php


require('./PHPMailer-master/PHPMailerAutoload.php');


class mail
{

    /**
     * Send对象
     *
     * @var Phpmailer
     */
    private $_mailer = null ;
    
    /**
     * 模板目录（默认为当前目录下的templates目录）
     *
     * @var unknown_type
     */
    public $template_dir = '' ;
    /**
     * 模板文件后缀
     *
     * @var unknown_type
     */
    public $template_suffix = '.phtml' ;
    

    /**
     * 邮件配置
     *
     * @var unknown_type
     */
    public $mail_config = array(
        'Username'  =>    'no-reply', // 邮局用户名(请填写完整的email地址)
        'Password'  =>    '51dh123456', //邮局密码
        'From'      =>    'no-reply@xxx.cn',//邮件发送者email地址
        'Host'      =>    'smtp.163.com',//企业邮局域名
        'SMTPAuth'  =>    true,// 启用SMTP验证功能
        'CharSet'   =>    'utf-8',//设置邮件编码
    );

    /**
     * 初始化邮件配置
     *
     * @return void
     */
    private function _init ()
    {
        foreach ($this->mail_config as $name => $value)
        {
            if ($value !== '')
            {
                $this->_mailer->$name = $value ;
            }
        }
    }


    /**
     * 构造函数-初始化配置
     *
     * @param string $template_dir 模板所有的目录(可省略,默认为当前的templates目录)
     */
    public function __construct($template_dir = '')
    {
        $this->_mailer = new PHPMailer();
        $this->setConfig($this->mail_config);
        $this->_mailer->IsHTML(true);
        if ($template_dir == '') 
        {
            $template_dir = dirname(__FILE__)."/".'templates';
        }
        $this->template_dir = $template_dir ;
    }
    

    /**
     * 获取模板
     *
     * @param unknown_type $tpl
     * @param unknown_type $parames
     * @return unknown
     */
    public function getContent($tpl,$parames)
    {
        ob_start();
        extract($parames);
//        var_dump($tpl);
        include  $this->template_dir . "/" .$tpl.$this->template_suffix ;
        $content = ob_get_clean();
        return $content ;
    }
    

    /**
     * 设置邮件配置
     *
     * @param array $config 配置 ，详细参数见成员变量
     * @return void
     */
    public function setConfig ($config)
    {
        if (is_array($config))
        {
            $this->mail_config = array_merge($this->mail_config,$config);
        }
        $this->_init();
    }

     /**
     * 发送邮件
     *
     * @param string $subject 标题
     * @param string $tpl 模板
     * @param string $tomail 要发送的邮箱地址
     * @param string $params 模板所对应的参数
     * @param string $fromAlias 别名
     * @return boolean
     */
    public function send($subject, $tpl, $tomail, $params)
    {
        $emailMatch = "/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$/i";
        //正确的邮箱
        $rightEmail = '';
        if(preg_match($emailMatch,$tomail) && strlen($subject))
        {
            $rightEmail = $tomail;
        }
        else
        {
            return false;
        }

        $this->isSMTP();    
        $fromEmail = "wanminny@163.com";                                  // Set mailer to use SMTP
        $this->_mailer->Host = 'smtp.163.com';  // Specify main and backup SMTP servers
        $this->_mailer->SMTPAuth = true; // Authentication must be disabled
        $this->_mailer->Username = $fromEmail;
        $this->_mailer->Password = '51dh123456'; // Leave this blank //可以使用一年时间默认
        $this->_mailer->SMTPSecure = '';
        $this->_mailer->Port = 25;

        $this->_mailer->From = $fromEmail;   //发送账号和前面的要一致否则是553！发送失败
        $this->_mailer->FromName = '来自于xxxxx网！';
        $this->_mailer->addAddress($tomail);               // Name is optional

//        $mail->addCC('41622358@qq.com');
//        $mail->addBCC('41622358@qq.com');
        $this->_mailer->CharSet='UTF-8';//否则默认是乱码
        
        // $mail->SMTPDebug =  true;       //开启邮件的协议调试信息； $mail->isSMTP();    要设置；！
        $this->_mailer->Subject =  $subject;

        //邮件内容
        $content = $this->getContent($tpl,$params) ;

        $this->_mailer->Body    = $content;
        // $this->_mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
        if(!$this->_mailer->send()) {
            echo '邮件发送错误:';
            echo 'Mailer Error: ' . $this->_mailer->ErrorInfo;
        } else {
            echo '发送成功！';
        }




//        //收件人地址,格式是AddAddress("收件人email","收件人姓名")
//        $this->_mailer->AddAddress($rightEmail,'');
//        //发送者别名
//        $this->_mailer->FromName = $fromAlias;
//        //邮件标题,设置标题编码
//        $this->_mailer->Subject = "=?UTF-8?B?".base64_encode($subject) ."?=";
//        //邮件内容
//        $content = $this->getContent($tpl,$params) ;
//        //设置内容
//        $this->_mailer->Body = $content;
//        $this->isSmtp();
//        // $this->_mailer->SMTPDebug = true ;
//        //邮件发送成功
//        $send = $this->_mailer->Send() ;
//        if($send === true)
//        {
//            //清除发送人
//            $this->_mailer->ClearAddresses();
//            return true;
//        }
//        else
//        {
//            return false;
//        }
    }


    /**
     * 是否使用SMTP方式发送
     *
     * @param unknown_type $boolean
     * @return void
     */
    public function isSmtp()
    {
        $this->_mailer->IsSMTP();
    }
    /**
     * 指定是否使用HTML格式 (默认使用HTML发送)
     *
     * @param boolean $boolean
     * @return void
     */
    public function isHtml($boolean = true)
    {
        //是否使用HTML格式
        $this->_mailer->IsHTML($boolean);  
    }



	public static function actionMail()
    {
        $mail = new PHPMailer;

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.163.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Authentication must be disabled
        $mail->Username = 'wanminny@163.com';
        $mail->Password = '51dh123456'; // Leave this blank //可以使用一年时间默认
        $mail->SMTPSecure = '';
        $mail->Port = 25;

        $mail->From = 'wanminny@163.com';   //发送账号和前面的要一致否则是553！发送失败
        $mail->FromName = '来自于51订货网！';
        $mail->addAddress('41622358@qq.com');               // Name is optional

//        $mail->addCC('41622358@qq.com');
//        $mail->addBCC('41622358@qq.com');
        $mail->CharSet='UTF-8';//否则默认是乱码
        
        // $mail->SMTPDebug =  true;       //开启邮件的协议调试信息； $mail->isSMTP();    要设置；！
        $mail->Subject =  "hzongw z中文主体主题";
        $mail->Body    = "sdfdsa中文内容";
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
            echo '邮件发送错误:';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo '发送成功！';
        }
    }
}

$email = "wanminny@163.com";
$params = array();
$params['img'] = "http://www.163.com";
$params['email'] = "wanminny@163.com";
$params['link'] = "www.yihaojf.com" . '/?code=' .'009';
$params['text'] = "aaaaaaaaaaa";
$demo = new mail();

$demo->send('!社区邀请', 'invite', $email, $params);

$demo->send('!checkemail', 'checkemail', $email, $params);
$demo->send('findpwd!社区邀请', 'findpwd', $email, $params);
$demo->send('modifyemail!社区邀请', 'modifyemail', $email, $params);
