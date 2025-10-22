<?php

namespace app\api\controller;

use app\BaseController;

/*
 * 基类
 */
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
class MobileHome extends BaseController {

    //列表默认分页数
    protected $pagesize = 5;

    public function initialize() {
        parent::initialize();

     

        //分页数处理
        $pagesize = intval(input('param.per_page'));
        if ($pagesize > 0) {
            $this->pagesize = $pagesize;
        } else {
            $this->pagesize = 10;
        }
        /* 加入配置信息 */
        $config_list = rkcache('config', true);
        config($config_list,'ds_config');
        header('Access-Control-Allow-Origin:'.config('ds_config.h5_site_url'));
        header('Access-Control-Allow-Credentials:true');
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
        exit;
        }
    }


    /**
     * 返回过滤对应的用户信息
     * @param type $member_info
     * @return type
     */
    protected function getMemberUser($member_info) {
        return array(
            'member_id' => $member_info['member_id'],
            'member_name' => $member_info['member_name'],
            'member_truename' => $member_info['member_truename'],
            'member_nickname' => $member_info['member_nickname'],
            'member_avatar' => get_member_avatar($member_info['member_avatar']),
            'member_points' => $member_info['member_points'],
            'member_email' => $member_info['member_email'],
            'member_emailbind' => $member_info['member_emailbind'],
            'member_mobile' => $member_info['member_mobile'],
            'member_mobilebind' => $member_info['member_mobilebind'],
            'member_sex' => $member_info['member_sex'],
            'member_qq' => $member_info['member_qq'],
            'member_ww' => $member_info['member_ww'],
            'member_birthday' => date('Y-m-d',$member_info['member_birthday']),
            'member_auth_state' => $member_info['member_auth_state'],
            'member_idcard_image1_url' => get_member_idcard_image($member_info['member_idcard_image1']),
            'member_idcard_image2_url' => get_member_idcard_image($member_info['member_idcard_image2']),
            'member_idcard_image3_url' => get_member_idcard_image($member_info['member_idcard_image3']),
        );
    }
    
    
    protected function getUserToken($member_info){
        $member_model=model('member');
            $token = $member_model->getBuyerToken($member_info['member_id'], $member_info['member_name']);
            if ($token) {
                $result = array();
                $result['token'] = $token;
                $result['info'] = $this->getMemberUser($member_info);
                ds_json_encode(10000, '',$result);
            }
            else {
                ds_json_encode(10001,lang('login_fail'));
            }
    }
}

?>
