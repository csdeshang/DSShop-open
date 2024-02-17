<?php
namespace  sendmsg;
class sendMemberMsg
{
    private $code = '';
    private $member_id = 0;
    private $member_info = array();
    private $mobile = '';
    private $email = '';

    /**
     * 设置
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->$key = $value;
    }

    public function send($param = array(),$weixin_param = array(),$ali_param = array(),$ten_param = array())
    {
        $msg_tpl = rkcache('membermsgtpl', true);
        if (!isset($msg_tpl[$this->code]) || $this->member_id <= 0) {
            return false;
        }

        $tpl_info = $msg_tpl[$this->code];

        $setting_info = model('membermsgsetting')->getMembermsgsettingInfo(array('membermt_code' => $this->code,'member_id' => $this->member_id), 'membermt_isreceive');
        if (empty($setting_info) || $setting_info['membermt_isreceive']) {
            // 发送站内信
            if ($tpl_info['membermt_message_switch']) {
                $message = ds_replace_text($tpl_info['membermt_message_content'], $param);
                $this->sendMessage($message);
            }
            // 发送短消息
            if ($tpl_info['membermt_short_switch']) {
                $this->getMemberInfo();
                if (!empty($this->mobile))
                    $this->member_info['member_mobile'] = $this->mobile;
                if ($this->member_info['member_mobilebind'] && !empty($this->member_info['member_mobile'])) {
                    $message = ds_replace_text($tpl_info['membermt_short_content'], $param);
                    if(session('member_msg_short')==md5($message.'@'.$this->code.'@'.$this->member_id)){//如果发送过相同的消息则停止再发送
                        return false;
                    }else{
                        session('member_msg_short',md5($message.'@'.$this->code.'@'.$this->member_id));
                    }
                    $smslog_param=array(
                    'ali_template_code'=>$tpl_info['ali_template_code'],
                    'ali_template_param'=>$ali_param,
                    'ten_template_code'=>$tpl_info['ten_template_code'],
                    'ten_template_param'=>$ten_param,
                    'message'=>$message,
                );
                    $this->sendShort($this->member_info['member_mobile'], $smslog_param);
                }
            }
            // 发送邮件
            if ($tpl_info['membermt_mail_switch']) {
                $this->getMemberInfo();
                if (!empty($this->email))
                    $this->member_info['member_email'] = $this->email;
                if ($this->member_info['member_emailbind'] && !empty($this->member_info['member_email'])) {
                    $param['site_name'] = config('ds_config.site_name');
                    $param['mail_send_time'] = date('Y-m-d H:i:s');
                    $subject = ds_replace_text($tpl_info['membermt_mail_subject'], $param);
                    $message = htmlspecialchars_decode(ds_replace_text($tpl_info['membermt_mail_content'], $param));
                    $this->sendMail($this->member_info['member_email'], $subject, $message);
                }
            }
            // 发送微信模板消息
            if(!empty($weixin_param) && $tpl_info['membermt_weixin_switch'] && $tpl_info['membermt_weixin_code']){
                $param['site_name'] = config('ds_config.site_name');
                $this->getMemberInfo();
                    if($this->member_info['member_wxopenid']){
                        $tm_data = array(
                            "first" => array(
                                "value" => $tpl_info['membermt_name'],
                                "color" => "#ff7007"
                            ),
                            "remark" => array(
                                "value" => ds_replace_text($tpl_info['membermt_short_content'],$param),
                                "color" => "#333"
                            )
                        );
                        $wechat_model=model('wechat');
                        $wechat_model->getOneWxconfig();
                        $wechat_model->sendMessageTemplate($this->member_info['member_wxopenid'], $tpl_info['membermt_weixin_code'], $weixin_param['url'], array_merge($tm_data,$weixin_param['data']));
                    }
            }
        }
    }

    /**
     * 会员详细信息
     */
    private function getMemberInfo()
    {
        if (empty($this->member_info)) {
            $this->member_info = model('member')->getMemberInfoByID($this->member_id);
        }
    }

    /**
     * 发送站内信
     * @param unknown $message
     */
    private function sendMessage($message)
    {
        //添加短消息
        $message_model = model('message');
        $insert_arr = array();
        $insert_arr['from_member_id'] = 0;
        $insert_arr['member_id'] = $this->member_id;
        $insert_arr['msg_content'] = $message;
        $insert_arr['message_type'] = 1;
        $message_model->addMessage($insert_arr);
    }

    /**
     * 发送短消息
     * @param unknown $number
     * @param unknown $message
     */
    private function sendShort($number, $message)
    {
        model('smslog')->sendSms($number, $message,'','','0','',true);
    }

    /**
     * 发送邮件
     * @param unknown $number
     * @param unknown $subject
     * @param unknown $message
     */
    private function sendMail($number, $subject, $message)
    {
        // 计划任务代码
        $insert = array();
        $insert['mailcron_address'] = $number;
        $insert['mailcron_subject'] = $subject;
        $insert['mailcron_contnet'] = $message;
        model('mailcron')->addMailCron($insert);
    }
}