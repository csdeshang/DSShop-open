<?php

namespace app\api\controller;

use think\facade\Lang;

/**
 * ============================================================================
 * DSShop多用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 微信登录控制器
 */
class Wxauto extends MobileMall {

    public function initialize() {
        parent::initialize();
    }

    public function get_code_url() {
        $WeixinLogin = new \weixin\WeixinLogin();
        $code_url = $WeixinLogin->get_code_url();
        ds_json_encode(10000, '', $code_url);
    }

    //根据授权Code 登录或注册
    public function checkAuth() {
        $inviter_id = intval(input('param.inviter_id')); #推荐人ID
        
        $WeixinLogin = new \weixin\WeixinLogin();
        //
        $res = $WeixinLogin->getOpenid();
        
        $userinfo = $WeixinLogin->getUserinfo($res);
        
        $member_model = model('member');
        $member_info = $member_model->getMemberInfo(array('member_wxunionid' => $userinfo['unionid']));

        //如果用户存在 则登录
        if (!empty($member_info)) {
            //获取用户Token
            $this->getUserToken($member_info);
        } else {
            //自动注册
            $logic_connect_api = model('connectapi', 'logic');
            //注册会员信息 返回会员信息
            $reg_info = array(
                'member_wxunionid' => $userinfo['unionid'],
                'nickname' => isset($userinfo['nickname']) ? $userinfo['nickname'] : get_rand_nickname(),
                'inviter_id' => $inviter_id, #推荐人ID
                'headimgurl' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
            );
            
            $wx_from = input('param.wx_from');
            switch ($wx_from) {
                case 'pc':
                    $reg_info['member_pc_wxopenid'] = $userinfo['openid'];
                    break;
                case 'h5':
                    $reg_info['member_h5_wxopenid'] = $userinfo['openid'];
                    break;
                case 'miniprogram':
                    $reg_info['member_mini_wxopenid'] = $userinfo['openid'];
                    break;
                case 'app':
                    $reg_info['member_app_wxopenid'] = $userinfo['openid'];
                    break;
            }
            

            $wx_member = $logic_connect_api->wx_register($reg_info, 'wx');

            $this->getUserToken($wx_member);
        }
    }

    /**
     * 微信小程序调用接口 用户获取小程序用户信息
     */
    public function getUser() {
        $WeixinLogin = new \weixin\WeixinLogin();
        $res = $WeixinLogin->getOpenid();
        ds_json_encode(10000, '', $res);
    }

}
