<?php


require('./PHPMailer-master/PHPMailerAutoload.php');


class mail
{
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


mail::actionMail();


