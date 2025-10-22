<?php

/**
 * 查看我发起的拼团,用户查看参团以及开团的信息,以及分享
 */

namespace app\api\controller;
use think\facade\Lang;/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 拼团控制器
 */
class Memberpintuan extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberpintuan.lang.php');
    }
    
    /*
     * 查看我发起的拼团
     */
    public function pintuangroup()
    {
        $condition = array();
        $condition[] = array('pintuangroup_headid','=',$this->member_info['member_id']);
        $ppintuangroup_model = model('ppintuangroup');
        $ppintuanorder_model = model('ppintuanorder');
        $ppintuangroup_list = $ppintuangroup_model->getPpintuangroupList($condition, 10); #获取开团信息
        foreach ($ppintuangroup_list as $key => $ppintuangroup) {
            //获取开团订单下的参团订单
            $condition = array();
            $condition[] = array('pintuangroup_id','=',$ppintuangroup['pintuangroup_id']);
            $ppintuangroup_list[$key]['pintuangroup_starttime_text'] = date('Y-m-d H:i',$ppintuangroup['pintuangroup_starttime']);
            $ppintuangroup_list[$key]['pintuangroup_endtime_text'] = date('Y-m-d H:i',$ppintuangroup['pintuangroup_endtime']);
            if($ppintuangroup['pintuangroup_is_virtual']){
                $ppintuangroup_list[$key]['order_list'] = $ppintuanorder_model->getPpintuanvrorderList($condition);
            }else{
                $ppintuangroup_list[$key]['order_list'] = $ppintuanorder_model->getPpintuanorderList($condition);
            }
        }
        $pintuangroup_state_array = $ppintuangroup_model->getPintuangroupStateArray();
        
        $result = array_merge(array('list' => $ppintuangroup_list), mobile_page($ppintuangroup_model->page_info));
        ds_json_encode(10000, '',$result);
    }
    
}