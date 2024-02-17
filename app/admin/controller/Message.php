<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Lang;
use AlibabaCloud\Client\AlibabaCloud;
/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 控制器
 */
class Message extends AdminControl {

    public function initialize() {
        parent::initialize();

        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/message.lang.php');
    }

    /**
     * 邮件设置
     */
    public function email() {
        $config_model = model('config');
        if (!(request()->isPost())) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);

            $this->setAdminCurItem('email');
            return View::fetch('email');
        } else {
            $update_array = array();
            $update_array['email_host'] = input('post.email_host');
            $update_array['email_secure'] = input('post.email_secure');
            $update_array['email_port'] = input('post.email_port');
            $update_array['email_addr'] = input('post.email_addr');
            $update_array['email_id'] = input('post.email_id');
            $update_array['email_pass'] = input('post.email_pass');

            $result = $config_model->editConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit') . lang('email_set'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit') . lang('email_set'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 短信平台设置
     */
    public function mobile() {
        $config_model = model('config');
        if (!(request()->isPost())) {
            $list_config = rkcache('config', true);
            $smscf_num = '';
            if($list_config['smscf_type']=='wj' && !empty($list_config['smscf_wj_username'])&&!empty($list_config['smscf_wj_key'])){
                //如果配置了信息,可以查看具体可用短信条数
                $smscf_num = http_request('http://www.smschinese.cn/web_api/SMS/?Action=SMS_Num&Uid='.$list_config['smscf_wj_username'].'&Key='.$list_config['smscf_wj_key'],'get');
            }
            View::assign('smscf_num', $smscf_num);
            View::assign('list_config', $list_config);
            
            $this->setAdminCurItem('mobile');
            return View::fetch('mobile');
        } else {
            $update_array = array();
            $update_array['smscf_type'] = input('post.smscf_type');
            $update_array['smscf_ali_id'] = input('post.smscf_ali_id');
            $update_array['smscf_ali_secret'] = input('post.smscf_ali_secret');
            $update_array['smscf_ten_id'] = input('post.smscf_ten_id');
            $update_array['smscf_ten_secret'] = input('post.smscf_ten_secret');
            $update_array['smscf_sign'] = input('post.smscf_sign');
            $update_array['smscf_wj_username'] = input('post.smscf_wj_username');
            $update_array['smscf_wj_key'] = input('post.smscf_wj_key');
            $update_array['sms_register'] = input('post.sms_register');
            $update_array['sms_login'] = input('post.sms_login');
            $update_array['sms_password'] = input('post.sms_password');
            $result = $config_model->editConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit') . lang('message_mobile'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit') . lang('message_mobile'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }
    
    /**
     * 短信发送日志
     */
    public function smslog()
    {
        $condition = array();
        
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            if ($add_time_from !== false) {
                $condition[]=array('smslog_smstime','>=', $add_time_from);
            }
        }
        if (trim($add_time_to) != '') {
            $add_time_to = strtotime(trim($add_time_to));
            if ($add_time_to !== false) {
                $add_time_to=$add_time_to+86399;
                $condition[]=array('smslog_smstime','<=', $add_time_to);
            }
        }
        $member_name = input('get.member_name');
        if(!empty($member_name)){
            $condition[] = array('member_name','like',"%" . $member_name . "%");
        }
        $smslog_phone = input('get.smslog_phone');
        if(!empty($smslog_phone)){
            $condition[] = array('smslog_phone','like',"%" . $smslog_phone . "%");
        }
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        $condition[]=array('smslog_state','<>',0);
        $smslog_model = model('smslog');
        $smslog_list = $smslog_model->getSmsList($condition,10);
        View::assign('smslog_list', $smslog_list);
        View::assign('show_page', $smslog_model->page_info->render());
        
        
        $this->setAdminCurItem('smslog');
        return View::fetch();
    }

    /**
     * 短信日志删除
     */
    public function smslog_del(){
        $smslog_id = input('param.smslog_id');
        $smslog_id_array = ds_delete_param($smslog_id);
        if ($smslog_id_array === FALSE) {
            ds_json_encode(10001, lang('param_error'));
        }
        $condition = array();
        $smslog_model = model('smslog');
        $condition[] = array('smslog_id','in', $smslog_id_array);
        $smslog_list = $smslog_model->delSmsLog($condition);
        if ($smslog_list){
            ds_json_encode(10000, lang('ds_common_del_succ'));
        }else{
            ds_json_encode(10001, lang('ds_common_del_fail'));
        }
    }

    /**
     * 邮件模板列表
     */
    public function email_tpl() {
        $mailtemplates_model = model('mailtemplates');
        $templates_list = $mailtemplates_model->getTplList();
        View::assign('templates_list', $templates_list);
        $this->setAdminCurItem('email_tpl');
        return View::fetch('email_tpl');
    }

    /**
     * 编辑邮件模板
     */
    public function email_tpl_edit() {
        $mailtemplates_model = model('mailtemplates');
        if (!request()->isPost()) {
            if (!(input('param.code'))) {
                $this->error(lang('mailtemplates_edit_code_null'));
            }
            $templates_array = $mailtemplates_model->getTplInfo(array('mailmt_code' => input('param.code')));
            View::assign('templates_array', $templates_array);
            $this->setAdminCurItem('email_tpl_edit');
            return View::fetch('email_tpl_edit');
        } else {
            $data = array(
                'code' => input('post.code'),
                'title' => input('post.title'),
                'content' => input('post.content'),
            );

            $message_validate = ds_validate('mailtemplates');
            if (!$message_validate->scene('email_tpl_edit')->check($data)) {
                $this->error($message_validate->getError());
            } else {
                $update_array = array();
                $update_array['mailmt_code'] = input('post.code');
                $update_array['mailmt_title'] = input('post.title');
                $update_array['mailmt_content'] = input('post.content');
                $result = $mailtemplates_model->editTpl($update_array, array('mailmt_code' => input('post.code')));
                if ($result>=0) {
                    $this->log(lang('ds_edit') . lang('email_tpl'), 1);
                    $this->success(lang('mailtemplates_edit_succ'), 'Message/email_tpl');
                } else {
                    $this->log(lang('ds_edit') . lang('email_tpl'), 0);
                    $this->error(lang('mailtemplates_edit_fail'));
                }
            }
        }
    }

    /**
     * 测试邮件发送
     *
     * @param
     * @return
     */
    public function email_testing() {
        /**
         * 读取语言包
         */
        $email_host = trim(input('post.email_host'));
        $email_secure = trim(input('post.email_secure'));
        $email_port = trim(input('post.email_port'));
        $email_addr = trim(input('post.email_addr'));
        $email_id = trim(input('post.email_id'));
        $email_pass = trim(input('post.email_pass'));
        $email_test = trim(input('post.email_test'));
        $subject = lang('test_email');
        $site_url = HOME_SITE_URL;
        
        /**
        //邮件发送测试
        $email_host = 'smtp.126.com';
        $email_secure = 'tls';//tls ssl
        $email_port = '25';//465 25
        $email_addr = '';
        $email_id = '';
        $email_pass = '';
        $email_test = '181814630@qq.com';
        */
        

        $site_name = config('ds_config.site_name');
        $message = '<p>' . lang('this_is_to') . "<a href='" . $site_url . "' target='_blank'>" . $site_name . '</a>' . lang('test_email_set_ok') . '</p>';

        $obj_email = new \sendmsg\Email();
        $obj_email->set('email_server', $email_host);
        $obj_email->set('email_secure', $email_secure);
        $obj_email->set('email_port', $email_port);
        $obj_email->set('email_user', $email_id);
        $obj_email->set('email_password', $email_pass);
        $obj_email->set('email_from', $email_addr);
        $obj_email->set('site_name', $site_name);
        $result = $obj_email->send($email_test, $subject, $message);
        if ($result === false) {
            $data['msg'] = lang('test_email_send_fail');
            echo json_encode($data);exit;
        } else {
            $data['msg'] = lang('test_email_send_ok');
            echo json_encode($data);exit;
        }
    }

    /**
     * 测试手机短信发送
     *
     * @param
     * @return
     */
    public function mobile_testing() {
        $mobile = input('param.mobile_test');
        $content = input('param.mobile_test_content');
        $smscf_type = input('param.smscf_type');
        $smscf_ali_id = input('param.smscf_ali_id');
        $smscf_ali_secret = input('param.smscf_ali_secret');
        $ali_template_param=input('param.ali_template_param');
        $ali_template_code=input('param.ali_template_code');
        $ali_template_content=input('param.ali_template_content');
        $smscf_ten_id = input('param.smscf_ten_id');
        $smscf_ten_secret = input('param.smscf_ten_secret');
        $ten_template_param = input('param.ten_template_param');
        $ten_template_code = input('param.ten_template_code');
        $ten_template_content = input('param.ten_template_content');
        $user_id = urlencode(input('param.smscf_wj_username')); // 这里填写用户名
        $key = urlencode(input('param.smscf_wj_key')); // 这里填接口安全密钥
        $smscf_sign = input('param.smscf_sign');
        config('ds_config.smscf_type',$smscf_type);
        config('ds_config.smscf_wj_username',$user_id);
        config('ds_config.smscf_wj_key',$key);
        config('ds_config.smscf_ali_id',$smscf_ali_id);
        config('ds_config.smscf_ali_secret',$smscf_ali_secret);
        config('ds_config.smscf_ten_id', $smscf_ten_id);
        config('ds_config.smscf_ten_secret', $smscf_ten_secret);
        config('ds_config.smscf_sign', $smscf_sign);
        $smslog_param = array(
            'ali_template_code' => $ali_template_code,
            'ali_template_param' => array(),
            'ten_template_code' => $ten_template_code,
            'ten_template_param' => array(),
        );
        $smslog_param=array(
                    'ali_template_code'=>$ali_template_code,
                    'ali_template_param'=>array(),
                );
        if($smscf_type=='wj'){
            $smslog_param['message']=$content;
        }elseif($smscf_type=='ali'){
            $param=json_decode(htmlspecialchars_decode($ali_template_param),true);
            if(!$param){
                echo json_encode(array('msg'=>lang('ali_template_param_error')));exit;
            }
            $smslog_param['message'] = ds_replace_text(htmlspecialchars_decode($ali_template_content), $param);
            $smslog_param['ali_template_param']= $param;
        }elseif ($smscf_type == 'ten') {
            $param = json_decode(htmlspecialchars_decode($ten_template_param), true);
            if (!$param) {
                echo json_encode(array('msg' => lang('ten_template_param_error')));
                exit;
            }
            $smslog_param['message'] = ds_replace_text(htmlspecialchars_decode($ten_template_content), $param);
            $smslog_param['ten_template_param'] = $param;
        }else{
            echo json_encode(array('msg' => lang('param_error')));
            exit;
        }
        
        $result=model('smslog')->sendSms($mobile, $smslog_param);

        if ($result['code'] == 10000){
            $data['msg'] = '测试手机短信发送成功';
        }else{
            $data['msg'] = $result['message'];
        }
        echo json_encode($data);
        exit;
    }

    /**
     * 用户消息模板
     */
    public function member_tpl() {
        $mmtpl_list = model('membermsgtpl')->getMembermsgtplList(array());
        View::assign('mmtpl_list', $mmtpl_list);
        $this->setAdminCurItem('member_tpl');
        return View::fetch('member_tpl');
    }

    /**
     * 用户消息模板编辑
     */
    public function member_tpl_edit() {
        if (!request()->isPost()) {
            $code = trim(input('param.code'));
            if (empty($code)) {
                $this->error(lang('param_error'));
            }
            $where = array();
            $where[] = array('membermt_code','=',$code);
            $mmtpl_info = model('membermsgtpl')->getMembermsgtplInfo($where);
            View::assign('mmtpl_info', $mmtpl_info);
            $this->setAdminCurItem('member_tpl_edit');
            return View::fetch('member_tpl_edit');
        } else {
            $code = trim(input('post.code'));
            $type = trim(input('post.type'));
            if (empty($code) || empty($type)) {
                $this->error(lang('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->member_tpl_update_message();
                    break;
                case 'short':
                    $this->member_tpl_update_short();
                    break;
                case 'mail':
                    $this->member_tpl_update_mail();
                    break;
                case 'weixin':
                    $this->member_tpl_update_weixin();
                    break;
            }
        }
    }
    public function ali_tpl(){
        $mmtpl_list = model('membermsgtpl')->getMembermsgtplList(array());
        $mailtemplates_model = model('mailtemplates');
        $templates_list = $mailtemplates_model->getTplList(array(array('mailmt_code','<>','bind_email')));
        View::assign('mmtpl_list',$mmtpl_list);
        View::assign('templates_list',$templates_list);
        $this->setAdminCurItem('message_ali_tpl');
        return View::fetch();
    }
    
    public function ali_tpl_edit(){
        $type=input('param.type');
        $code=input('param.code');
        $name=input('param.name');
        switch($type){
            case 'membermsgtpl':
                if(!model('membermsgtpl')->editMembermsgtpl(array('membermt_code'=>$name),array('ali_template_code'=>$code))){
                    ds_json_encode(10001, lang('ds_common_op_fail'));
                }else{
                    ds_json_encode(10000, lang('ds_common_op_succ'));
                }
                break;
            case 'mailmsgtemlates':
                if(!model('mailtemplates')->editTpl(array('ali_template_code'=>$code),array('mailmt_code'=>$name))){
                    ds_json_encode(10001, lang('ds_common_op_fail'));
                }else{
                    ds_json_encode(10000, lang('ds_common_op_succ'));
                }
                break;
            default:
                ds_json_encode(10001, lang('param_error'));
        }
    }
    
    public function ali_tpl_query() {
        $code = input('param.code');

        AlibabaCloud::accessKeyClient(config('ds_config.smscf_ali_id'), config('ds_config.smscf_ali_secret'))
                ->regionId('cn-hangzhou')
                ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                    ->product('Dysmsapi')
                    // ->scheme('https') // https | http
                    ->version('2017-05-25')
                    ->action('QuerySmsTemplate')
                    ->method('POST')
                    ->host('dysmsapi.aliyuncs.com')
                    ->options([
                        'query' => [
                            'RegionId' => "cn-hangzhou",
                            'TemplateCode' => $code,
                        ],
                    ])
                    ->request();
            
        } catch (\Exception $e) {
            ds_json_encode(10001, $e->getErrorMessage());
        }
        ds_json_encode(10000, lang('ds_common_op_succ'),$result->toArray());
    }

    public function ten_tpl() {
        $mstpl_list = model('storemsgtpl')->getStoremsgtplList(array());
        $mmtpl_list = model('membermsgtpl')->getMembermsgtplList(array());
        $mailtemplates_model = model('mailtemplates');
        $templates_list = $mailtemplates_model->getTplList(array(array('mailmt_code','<>', 'bind_email')));
        View::assign('mstpl_list', $mstpl_list);
        View::assign('mmtpl_list', $mmtpl_list);
        View::assign('templates_list', $templates_list);
        $this->setAdminCurItem('message_ten_tpl');
        return View::fetch();
    }

    public function ten_tpl_edit() {
        $type = input('param.type');
        $code = input('param.code');
        $name = input('param.name');
        switch ($type) {
            case 'membermsgtpl':
                if (!model('membermsgtpl')->editMembermsgtpl(array('membermt_code' => $name), array('ten_template_code' => $code))) {
                    ds_json_encode(10001, lang('ds_common_op_fail'));
                } else {
                    ds_json_encode(10000, lang('ds_common_op_succ'));
                }
                break;
            case 'storemsgtpl':
                if (!model('storemsgtpl')->editStoremsgtpl(array('storemt_code' => $name), array('ten_template_code' => $code))) {
                    ds_json_encode(10001, lang('ds_common_op_fail'));
                } else {
                    ds_json_encode(10000, lang('ds_common_op_succ'));
                }
                break;
            case 'mailmsgtemlates':
                if (!model('mailtemplates')->editTpl(array('ten_template_code' => $code), array('mailmt_code' => $name))) {
                    ds_json_encode(10001, lang('ds_common_op_fail'));
                } else {
                    ds_json_encode(10000, lang('ds_common_op_succ'));
                }
                break;
            default:
                ds_json_encode(10001, lang('param_error'));
        }
    }

    //接口
    public function ten_tpl_query() {
        $code = input('param.code');
        // 短信应用 SDK AppID
        $appid = config('ds_config.smscf_ten_id'); // SDK AppID 以1400开头
        // 短信应用 SDK AppKey
        $appkey = config('ds_config.smscf_ten_secret');
        try {
            $cred = new Credential($appid,$appkey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred,"",$clientProfile);

            $req = new DescribeSmsTemplateListRequest();

            $params = array($code);
            $req->fromJsonString($params);

            $result = $client->DescribeSmsTemplateList($req);

            $rsp = json_decode($result);
        } catch (\Exception $e) {
            echo var_dump($e);
        }
        ds_json_encode(10000, lang('ds_common_op_succ'), $rsp->toArray());
    }

    /**
     * 商家消息模板更新站内信
     */
    private function member_tpl_update_message() {
        $message_content = trim(input('post.message_content'));
        if (empty($message_content)) {
            $this->error('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where[] = array('membermt_code','=',trim(input('post.code')));
        // 数据
        $update = array();
        $update['membermt_message_switch'] = intval(input('post.message_switch'));
        $update['membermt_message_content'] = $message_content;
        $result = model('membermsgtpl')->editMembermsgtpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新短消息
     */
    private function member_tpl_update_short() {
        $short_content = trim(input('post.short_content'));
        if (empty($short_content)) {
            $this->error('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where[] = array('membermt_code','=',trim(input('post.code')));
        // 数据
        $update = array();
        $update['membermt_short_switch'] = intval(input('post.short_switch'));
        $update['membermt_short_content'] = $short_content;
        $result = model('membermsgtpl')->editMembermsgtpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新邮件
     */
    private function member_tpl_update_weixin() {
        $weixin_code = trim(input('post.weixin_code'));
        if (empty($weixin_code)) {
            $this->error(lang('param_error'));
        }
        // 条件
        $where = array();
        $where[] = array('membermt_code','=',trim(input('post.code')));
        // 数据
        $update = array();
        $update['membermt_weixin_switch'] = intval(input('post.weixin_switch'));
        $update['membermt_weixin_code'] = $weixin_code;
        $result = model('membermsgtpl')->editMembermsgtpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }
    /**
     * 商家消息模板更新邮件
     */
    private function member_tpl_update_mail() {
        $mail_subject = trim(input('post.mail_subject'));
        $mail_content = trim(input('post.mail_content'));
        if ((empty($mail_subject) || empty($mail_content))) {
            $this->error('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where[] = array('membermt_code','=',trim(input('post.code')));
        // 数据
        $update = array();
        $update['membermt_mail_switch'] = intval(input('post.mail_switch'));
        $update['membermt_mail_subject'] = $mail_subject;
        $update['membermt_mail_content'] = $mail_content;
        $result = model('membermsgtpl')->editMembermsgtpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    private function member_tpl_update_showmessage($result) {
        if ($result>=0) {
            $this->success(lang('ds_common_op_succ'), url('Message/member_tpl'));
        } else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'email',
                'text' => '邮件设置',
                'url' => url('Message/email')
            ),
            array(
                'name' => 'mobile',
                'text' => '短信平台设置',
                'url' => url('Message/mobile')
            ),
            array(
                'name' => 'smslog',
                'text' => '短信记录',
                'url' => url('Message/smslog')
            ),
            array(
                'name' => 'member_tpl',
                'text' => '用户消息模板',
                'url' => url('Message/member_tpl')
            ),
            array(
                'name' => 'email_tpl',
                'text' => '其他模板',
                'url' => url('Message/email_tpl')
            ),
        );
        if(config('ds_config.smscf_type')=='ali'){
            array_splice($menu_array, 2, 0, array(array(
                'name' => 'message_ali_tpl',
                'text' => lang('message_ali_tpl'),
                'url' => url('Message/ali_tpl')
            )));
        }
        if (config('ds_config.smscf_type') == 'ten') {
            array_splice($menu_array, 2, 0, array(array(
                'name' => 'message_ten_tpl',
                'text' => lang('message_ten_tpl'),
                'url' => url('Message/ten_tpl')
            )));
        }
        if (request()->action() == 'seller_tpl_edit') {
            $menu_array[] = array(
                'name' => 'seller_tpl_edit',
                'text' => '编辑平台消息模板',
                'url' => "javascript:void(0)"
            );
        }
        if (request()->action() == 'member_tpl_edit') {
            $menu_array[] = array(
                'name' => 'member_tpl_edit',
                'text' => '编辑用户消息模板',
                'url' => "javascript:void(0)"
            );
        }
        if (request()->action() == 'email_tpl_edit') {
            $menu_array[] = array(
                'name' => 'email_tpl_edit',
                'text' => '编辑其他消息模板',
                'url' => "javascript:void(0)"
            );
        }


        return $menu_array;
    }

}

?>
