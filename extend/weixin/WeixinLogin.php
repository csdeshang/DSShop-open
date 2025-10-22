<?php

namespace weixin;

class WeixinLogin {

    public function __construct() {
        $wx_from = input('param.wx_from');
        $this->wx_from = $wx_from;
        if ($wx_from == 'app') {
            $this->appId = config('ds_config.weixin_app_appid');
            $this->appSecret = config('ds_config.weixin_app_secret');
        } else {
            $this->appId = config('ds_config.weixin_h5_appid');
            $this->appSecret = config('ds_config.weixin_h5_secret');
        }
    }
    
    //获取 微信浏览器 code_url
    public function get_code_url() {
        $ref = htmlspecialchars_decode(input('param.ref'));
        $type = input('param.type');
        
        if(!in_array($type, array('login','bind'))){
            ds_json_encode(10001, '参数错误');
        }
        
        if($type == 'login'){
            $redirect_uri = config('ds_config.h5_site_url') . "/pages/home/memberlogin/Login?ref=" . urlencode($ref);
        }else{
            $redirect_uri = config('ds_config.h5_site_url') . "/pages/member/setting/AccountSet?ref=" . urlencode($ref);
        }
        
        //H5 前端获取授权Code
        $code_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=snsapi_userinfo&state=dsmall#wechat_redirect"; // 获取code
        return $code_url;
    }
    
    
    /**
     * 小程序登录：https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/login.html
     * 移动应用登录：https://developers.weixin.qq.com/doc/oplatform/Mobile_App/WeChat_Login/Development_Guide.html
     * 微信网页登陆:https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
     */
    //获取  unionid 以及 openid
    public function getOpenid() {
        $code = input('param.code');
        if(!in_array($this->wx_from,array('pc','h5','miniprogram','app'))){
            ds_json_encode(10001, '微信登录参数错误');
        }
        if (!empty($code)) {
            
            if ($this->wx_from == 'miniprogram') {
                //小程序支付获取openid
                $xcx_appid = config('ds_config.weixin_xcx_appid');
                $xcx_appsecret = config('ds_config.weixin_xcx_secret');
                //说明：https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
                $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $xcx_appid . "&secret=" . $xcx_appsecret . "&js_code=" . $code . "&grant_type=authorization_code";
                if (!$xcx_appid || !$xcx_appsecret) {
                    ds_json_encode(10001, lang('xcx_not_set'));
                }
            } else {
                //微信自动登录
                $wxappid = $this->appId;
                $wxappsecret = $this->appSecret;
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$wxappid&secret=$wxappsecret&code=$code&grant_type=authorization_code";
                if (!$wxappid || !$wxappsecret) {
                    ds_json_encode(10001, lang('wechat_not_set'));
                }
            }

            //获取 用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
            $res = json_decode(http_request($url), true);
            if (isset($res['errcode'])) {
                ds_json_encode(10001, lang('error_code') . $res['errcode'] . '，' . $res['errmsg']);
            }
            
            //https://developers.weixin.qq.com/doc/offiaccount/User_Management/Get_users_basic_information_UnionID.html#UinonId
            if (empty($res['unionid']) ||  strlen($res['unionid']) != 28) {
                ds_json_encode(10001, lang('unionid_not_get'));
            }
            return $res;
        } else {
            ds_json_encode(10001, lang('openid_not_get'));
        }
    }
    
    
    public function getUserinfo($res){
        if(!in_array($this->wx_from,array('pc','h5','miniprogram','app'))){
            ds_json_encode(10001, '微信登录参数错误');
        }
        //非小程序，单独拉取用户信息  https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html#3
        if ($this->wx_from != 'miniprogram') {
            //获取 当前用户的 头像及昵称
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $res['access_token'] . "&openid=" . $res['openid'] . "&lang=zh_CN";
            $userinfo = json_decode(http_request($url), true);
            if (isset($userinfo['errcode'])) {
                ds_json_encode(10001, $userinfo['errcode'] . '，' . $userinfo['errmsg']);
            }
        } else {
            $userinfo = $res;
        }
        
        return $userinfo;
        
    }
    
    
    
    
    
}
