<?php

namespace app\api\controller;
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
class MobileMember extends MobileHome {

    public function initialize() {
        parent::initialize();
        $key = request()->header('X-DS-KEY');
        if(!$key){
            $key=input('param.key');//微信支付需要
        }
        if (!empty($key)) {
            $condition = array();
            $condition[] = array('platform_type', '=', 'member');
            $condition[] = array('platform_token', '=', $key);
            $platformtoken_info = model('platformtoken')->getPlatformtokenInfo($condition);
            
            if (empty($platformtoken_info)) {
                ds_json_encode(11001, '请登录');
            }
            $member_model = model('member');
            $this->member_info = $member_model->getMemberInfoByID($platformtoken_info['platform_userid']);

            if (empty($this->member_info)) {
                ds_json_encode(11001, '请登录');
            } else {
              if (!$this->member_info['member_state']) {
                    ds_json_encode(11001, lang('please_login'));
                }
                $this->member_info['member_openid'] = $platformtoken_info['platform_openid'];
                $this->member_info['member_token'] = $platformtoken_info['platform_token'];
                $level_name = $member_model->getOneMemberGrade($platformtoken_info['platform_userid']);
                $this->member_info['level_name'] = $level_name['level_name'];
 
                //考虑到模型中session
                if (session('member_id') != $this->member_info['member_id']) {
                    //避免重复查询数据库
                    $member_model->createSession(array_merge($this->member_info, $level_name));
                }
            }
        }else{
            ds_json_encode(11001, '请登录');
        }
    }


}

?>
