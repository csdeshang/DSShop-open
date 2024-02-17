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
class Memberpoints extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/memberpoints.lang.php');
    }

    /**
     * 积分日志列表
     */
    public function index() {
        $condition = array();
        $condition[] = array('pl_memberid','=',session('member_id'));
        if (input('param.stage')) {
            $condition[] = array('pl_stage','=',input('param.stage'));
        }

        $saddtime = input('get.stime');
        $etime = input('get.etime');
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $saddtime);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_time ? strtotime($saddtime) : null;
        $end_unixtime = $if_end_time ? strtotime($etime) : null;
        if ($start_unixtime) {
            $condition_arr[] = array('pl_addtime','>=', $start_unixtime);
        }
        if ($end_unixtime) {
            $end_unixtime=$end_unixtime+86399;
            $condition_arr[] = array('pl_addtime','<=', $end_unixtime);
        }

        $pl_desc = input('get.description');
        if (!empty($pl_desc)) {
            $condition[] = array('pl_desc','like', '%' . $pl_desc . '%');
        }
        //分页
        //查询积分日志列表
        $points_model = model('points');
        $list_log = $points_model->getPointslogList($condition, '10', '*', '');
        $member_points=model('member')->getMemberInfo(array('member_id'=>session('member_id')),'member_points');
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_points');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('points');
        View::assign('show_page', $points_model->page_info->render());
        View::assign('list_log', $list_log);
        View::assign('member_points', $member_points);
        return View::fetch($this->template_dir . 'index');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @param array $array 附加菜单
     * @return
     */
    protected function getMemberItemList() {
        $menu_array = array(
            array(
                'name' => 'points',
                'text' => lang('ds_member_path_points'),
                'url' => url('Memberpoints/index')
            ),
        );
        return $menu_array;
    }

}
