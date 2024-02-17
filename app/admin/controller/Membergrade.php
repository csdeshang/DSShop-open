<?php

/**
 * 会员等级
 */

namespace app\admin\controller;
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
class Membergrade extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/Membergrade.lang.php');
    }

    public function index() {
        if (request()->isPost()) {
            $update_arr = array();
            if (!empty(input('post.mg/a'))) {
                $mg_arr = array();
                $i = 1;
                $max_exppoints = '-1';#用户判断 下级会员等级积分应大于上级等级积分
                foreach (input('post.mg/a') as $k => $v) {
                    $mg_arr[$i]['level'] = $i;
                    $level_name = $v['level_name'];
                    $exppoints  = intval($v['exppoints']);
                    if(empty($level_name)){
                        $this->error(lang('param_error'));
                    }
                    $mg_arr[$i]['level_name'] = $level_name;
                    //所需经验值
                    if($max_exppoints>=$exppoints){
                        $this->error($level_name.'经验值应大于'.$max_exppoints);
                    }else{
                        $mg_arr[$i]['exppoints'] = $exppoints;
                    }
                    $max_exppoints = $exppoints;
                    $i++;
                }
                $update_arr['member_grade'] = serialize($mg_arr);
            } else {
                $this->error('必须设置会员等级');
            }
            $result = true;
            if ($update_arr) {
                $config_model = model('config');
                $result = $config_model->editConfig($update_arr);
            }
            if ($result) {
                $this->log(lang('ds_edit') . lang('ds_member_grade'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit') . lang('ds_member_grade'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            $list_config = rkcache('config', true);
            $membergrade_list = $list_config['member_grade'] ? unserialize($list_config['member_grade']) : array();
            foreach ($membergrade_list as $key => $value) {
                $maxlevel[]=$value['level'];
            }
            View::assign('maxlevel', max($maxlevel)+1);
            View::assign('membergrade_list', $membergrade_list);
            $this->setAdminCurItem('index');
            return View::fetch();
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '管理',
                'url' => url('Membergrade/index')
            )
        );
        return $menu_array;
    }

}
