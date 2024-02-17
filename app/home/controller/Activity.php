<?php

namespace app\home\controller;
use think\facade\View;
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
class Activity extends BaseMall {

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
        $condition[]=array('activity_type','=',1);
        $condition[]=array('activity_startdate','<=',TIMESTAMP);
        $condition[]=array('activity_enddate','>=',TIMESTAMP);
        $condition[]=array('activity_state','=',1);
        
        $activity_list = $activity_model->getActivityList($condition, 10);
        View::assign('activity_list', $activity_list);
        View::assign('show_page', $activity_model->page_info->render());
        View::assign('html_title', config('ds_config.site_name') . ' - '.lang('activity_list'));
        return View::fetch($this->template_dir.'activity_index');
    }
    

    /**
     * 单个活动信息页
     */
    public function detail() {
        //得到导航ID
        $nav_id = intval(input('param.nav_id'));
        View::assign('index_sign', $nav_id);
        //查询活动信息
        $activity_id = intval(input('param.activity_id'));
        if ($activity_id <= 0) {
            $this->error(lang('param_error')); //'缺少参数:活动编号'
        }
        $activity = model('activity')->getOneActivityById($activity_id);
        if (empty($activity) || $activity['activity_type'] != '1' || $activity['activity_state'] != 1 || $activity['activity_startdate'] > TIMESTAMP || $activity['activity_enddate'] < TIMESTAMP) {
            $this->error(lang('activity_index_activity_not_exists')); //'指定活动并不存在'
        }
        View::assign('activity', $activity);
        //查询活动内容信息
        $condition = array();
        $condition[] = array('activitydetail.activity_id','=',$activity_id);
        $activitydetail_list = model('activitydetail')->getActivitydetailAndGoodsList($condition);
        View::assign('activitydetail_list', $activitydetail_list);
        View::assign('html_title', config('ds_config.site_name') . ' - ' . $activity['activity_title']);
        return View::fetch($this->template_dir.'activity_show');
    }

}

?>
