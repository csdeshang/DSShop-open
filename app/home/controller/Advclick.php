<?php

/*
 * 广告统计
 */

namespace app\home\controller;

use think\facade\Lang;
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
class Advclick extends BaseMall {

    /**
     * 广告点击率统计
     */
    public function advclick() {
        /**
         * 取广告的相关信息
         */
        $adv_model = model('adv');
        $adv_id = intval(input('param.adv_id'));
        if($adv_id<=0){
            $this->error(lang('param_error'));
        }
        

        $adv_info = $adv_model->getOneAdv(array(array('adv_id','=',$adv_id)));
        
        if(empty($adv_info['adv_link'])){
            $adv_info['adv_link'] = HOME_SITE_URL;
        }
        $url = str_replace(array('&amp;'), array('&'), $adv_info['adv_link']);
        
        /**
         * 写入点击率表
         */
        $adv_param['adv_clicknum'] = $adv_info['adv_clicknum'] + 1;
        $adv_model->editAdv($adv_id,$adv_param);
        /**
         * 广告链接跳转
         */
        $this->redirect($url);
    }
}

?>
