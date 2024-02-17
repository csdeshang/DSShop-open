<?php

/**
 * 邮件类
 * 邮件操作类
 */

namespace sendmsg;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require root_path(). 'vendor/phpmailer/phpmailer/src/Exception.php';
require root_path(). 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require root_path(). 'vendor/phpmailer/phpmailer/src/SMTP.php';
final class Email {

    /**
     * 邮件服务器
     */
    private $email_server;

    /**
     * 协议
     */
    private $email_secure;

    /**
     * 端口
     */
    private $email_port;

    /**
     * 账号
     */
    private $email_user;

    /**
     * 密码
     */
    private $email_password;

    /**
     * 发送邮箱
     */
    private $email_from;

    /**
     * 间隔符
     */
    private $email_delimiter = "\n";

    /**
     * 站点名称
     */
    private $site_name;

    public function get($key) {
        if (!empty($this->$key)) {
            return $this->$key;
        } else {
            return false;
        }
    }

    public function set($key, $value) {
        if (!isset($this->$key)) {
            $this->$key = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发送邮件
     *
     * @param string $email_to 发送对象邮箱地址
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @param string $from 页头来源内容
     * @return bool 布尔形式的返回结果
     */
    public function send($email_to, $subject, $message, $from = '') {
        if (empty($email_to))
            return false;

        $subject = $this->subject($subject);
        $message = $this->html($subject, $message);
        $mail = new PHPMailer;
        //Server settings
        $mail->SMTPDebug = 0;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $this->email_server;                   // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $this->email_from;                 // SMTP username
        $mail->Password = $this->email_password;                  // SMTP password
        $mail->SMTPSecure = $this->email_secure;                     // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $this->email_port;               // TCP port to connect to
        //Recipients
        $mail->setFrom($this->email_from, $this->site_name);
        $mail->addAddress($email_to);     // Add a recipient
//        $mail->addReplyTo('info@example.com', 'Information');
//        $mail->addCC('cc@example.com');
//        $mail->addBCC('bcc@example.com');
        //Attachments
//        $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $message;
		$mail->CharSet ="UTF-8";
//            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        $result = $mail->send();
        return $result;
    }

    public function send_sys_email($email_to, $subject, $message) {
        $this->set('email_server', config('ds_config.email_host'));
        $this->set('email_secure', config('ds_config.email_secure'));
        $this->set('email_port', config('ds_config.email_port'));
        $this->set('email_user', config('ds_config.email_id'));
        $this->set('email_password', config('ds_config.email_pass'));
        $this->set('email_from', config('ds_config.email_addr'));
        $this->set('site_name', config('ds_config.site_name'));
        $result = $this->send($email_to, $subject, $message);
        return $result;
    }

    /**
     * 内容:邮件主体
     *
     * @param string $subject 邮件标题
     * @param string $message 邮件内容
     * @return string 字符串形式的返回结果
     */
    private function html($subject, $message) {
        $message = preg_replace("/href\=\"(?!http\:\/\/)(.+?)\"/i", 'href="\\1"', $message);
        $tmp = "<html><head>";
        $tmp .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $tmp .= "<title>" . $subject . "</title>";
        $tmp .= "</head><body>" . $message . "</body></html>";
        $message = $tmp;
        unset($tmp);
        return $message;
    }

    /**
     * 内容:邮件标题
     *
     * @param string $subject 邮件标题
     * @return string 字符串形式的返回结果
     */
    private function subject($subject) {
        $subject = '=?' . CHARSET . '?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . $this->site_name . '] ' . $subject)) . '?=';
        return $subject;
    }

}
