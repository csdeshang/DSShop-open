<?php

namespace app\api\controller;
use think\facade\Lang;

/**
 * ============================================================================
 * DSShop单用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 控制器
 */
class Activity extends MobileMall {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/activity.lang.php');
    }
    
    /*
     * 显示所有活动列表
     */
    function index()
    {
        $condition = array();
        $activity_model = model('activity');
        $activitydetail_model = model('activitydetail');
        $condition[]=array('activity_type','=',1);
        $condition[]=array('activity_startdate','<=',TIMESTAMP);
        $condition[]=array('activity_enddate','>=',TIMESTAMP);
        $condition[]=array('activity_state','=',1);
        
        $activity_list = $activity_model->getActivityList($condition, 10);
        foreach($activity_list as $key => $val){
            $activity_list[$key]['activity_banner_mobile_url']=ds_get_pic(ATTACH_ACTIVITY,$val['activity_banner_mobile']);
            $condition=array();
            $condition[]=array('activity_id','=',$val['activity_id']);
            $goods_list=$activitydetail_model->getGoodsJoinList($condition,3,'activitydetail_sort asc');
            foreach($goods_list as $k => $v){
                $goods_list[$k]['goods_image_url'] = goods_cthumb($v['goods_image'], 480);
            }
            $activity_list[$key]['goods_list']=$goods_list;
            
        }
        $result = array_merge(array('activity_list' => $activity_list), mobile_page(is_object($activity_model->page_info) ? $activity_model->page_info : ''));
        ds_json_encode(10000, '', $result);
    }
    

    /**
     * 单个活动信息页
     */
    public function detail() {
        //查询活动信息
        $activity_id = intval(input('param.activity_id'));
        if ($activity_id <= 0) {
            ds_json_encode(10001,lang('param_error')); //'缺少参数:活动编号'
        }
        $cache_key='api-activity-'.$activity_id;
        $result = rcache($cache_key);
        if (empty($result)) {
            $activity = model('activity')->getOneActivityById($activity_id);
            $result=array('activity'=>$activity);
            wcache($cache_key, $result);
        }
        $activity=$result['activity'];
        
        if (empty($activity) || $activity['activity_type'] != '1' || $activity['activity_state'] != 1 || $activity['activity_startdate'] > TIMESTAMP || $activity['activity_enddate'] < TIMESTAMP) {
            ds_json_encode(10001,lang('activity_index_activity_not_exists')); //'指定活动并不存在'
        }

        $activity['activity_banner_mobile_url']=ds_get_pic(ATTACH_ACTIVITY,$activity['activity_banner_mobile']);
        
        $activitydetail_model=model('activitydetail');
        //查询活动内容信息
        $condition = array();
        $condition[] = array('activitydetail.activity_id','=',$activity_id);
        $activitydetail_list = $activitydetail_model->getGoodsJoinList($condition,$this->pagesize,'activitydetail_sort asc');
        foreach($activitydetail_list as $key => $val){
            $activitydetail_list[$key]['goods_image_url'] = goods_cthumb($val['goods_image'], 480);
        }
        $result = array_merge(array('activity'=>$activity,'activitydetail_list'=>$activitydetail_list), mobile_page(is_object($activitydetail_model->page_info) ? $activitydetail_model->page_info : ''));
        ds_json_encode(10000, '', $result);
    }

}

?>
