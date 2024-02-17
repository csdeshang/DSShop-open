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
class Memberevaluate extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/memberevaluate.lang.php');
    }

    /**
     * 订单添加评价
     */
    public function add() {
        $order_id = intval(input('order_id'));
        if (!$order_id) {
            $this->error(lang('param_error'), 'member_order/index');
        }

        $return = model('memberevaluate', 'logic')->validation($order_id, $this->member_info['member_id']);
        if (isset($return['state'])) {
            ds_json_encode(10001, $return['msg']);
        }
        extract($return['data']);

        //判断是否为页面
        if (!request()->isPost()) {
            //处理积分、经验值计算说明文字
            $expset = rkcache('config', true);
            $ruleexplain = '';
            $exppoints_rule = $expset['expset'] ? unserialize($expset['expset']) : array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['comment_exp']);

            if ($exppoints_rule['exp_comments'] > 0) {
                $ruleexplain .= lang('evaluation_completed_will_obtained');
                if ($exppoints_rule['exp_comments'] > 0) {
                    $ruleexplain .= (' “' . $exppoints_rule['exp_comments'] . lang('experience_value'));
                }
            }
            View::assign('ruleexplain', $ruleexplain);

            //不显示左菜单
            View::assign('left_show', 'order_view');
            View::assign('order_id', $order_id);
            View::assign('order_info', $order_info);
            View::assign('order_goods', $order_goods);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_evaluate');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('evaluate');
            return View::fetch($this->template_dir . 'evaluation_add');
        } else {

            //调用逻辑层方法,PC和手机端统一使用
            $return = model('memberevaluate', 'logic')->saveorderevaluate($order_info, $order_goods, $this->member_info['member_id'], $this->member_info['member_name']);

            if ($return == true) {
                ds_json_encode(10000, lang('member_evaluation_evaluat_success'));
            } else {
                ds_json_encode(10001, '评价失败');
            }
        }
    }

    /**
     * 虚拟商品评价
     */
    public function add_vr() {
        $order_id = intval(input('param.order_id'));
        if (!$order_id) {
            ds_json_encode('10001', lang('param_error'));
        }

        $vrorder_model = model('vrorder');
        $evaluategoods_model = model('evaluategoods');

        $return = model('memberevaluate', 'logic')->validationVr($order_id, $this->member_info['member_id']);
        if (isset($return['state'])) {
            ds_json_encode(10001, $return['msg']);
        }
        extract($return['data']);

        $order_goods = array($order_info);

        //判断是否为页面
        if (!request()->isPost()) {
            $order_goods[0]['goods_image_url'] = goods_cthumb($order_info['goods_image'], 240);

            //处理积分、经验值计算说明文字
            $ruleexplain = '';
            $exppoints_rule = config("ds_config.exppoints_rule") ? unserialize(config("ds_config.exppoints_rule")) : array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['exp_comments']);
            $points_comments = intval(config('ds_config.points_comments'));
            if ($exppoints_rule['exp_comments'] > 0 || $points_comments > 0) {
                $ruleexplain .= lang('evaluation_completed_will_obtained');
                if ($exppoints_rule['exp_comments'] > 0) {
                    $ruleexplain .= (' “' . $exppoints_rule['exp_comments'] . lang('experience_value'));
                }
                if ($points_comments > 0) {
                    $ruleexplain .= (' “' . $points_comments . lang('integral'));
                }
                $ruleexplain .= '。';
            }
            View::assign('ruleexplain', $ruleexplain);

            //不显示左菜单
            View::assign('left_show', 'order_view');
            View::assign('order_info', $order_info);
            View::assign('order_goods', $order_goods);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_evaluate');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('evaluate');
            return View::fetch($this->template_dir . 'evaluation_add');
        } else {
            //调用逻辑层方法,PC和手机端统一使用
            $return = model('memberevaluate', 'logic')->saveVr($order_info, $order_goods, $this->member_info['member_id'], $this->member_info['member_name']);
            if (!$return['state']) {
                ds_json_encode(10000, lang('member_evaluation_evaluat_success'));
            } else {
                ds_json_encode(10001, '失败');
            }
        }
    }

    /**
     * 评价列表
     */
    public function index() {
        $evaluategoods_model = model('evaluategoods');

        $condition = array();
        $condition[] = array('geval_frommemberid','=',session('member_id'));
        $goodsevallist = $evaluategoods_model->getEvaluategoodsList($condition, 5, 'geval_id desc');
        View::assign('goodsevallist', $goodsevallist);
        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_evaluate');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('evaluate');
        View::assign('show_page', $evaluategoods_model->page_info->render());

        return View::fetch($this->template_dir . 'index');
    }

    public function add_image() {
        $geval_id = intval(input('geval_id'));

        $evaluategoods_model = model('evaluategoods');

        $geval_info = $evaluategoods_model->getEvaluategoodsInfoByID($geval_id);

        if (!empty($geval_info['geval_image'])) {
            $this->error(lang('goods_have_been_posted'));
        }

        if ($geval_info['geval_frommemberid'] != session('member_id')) {
            $this->error(lang('param_error'));
        }
        View::assign('geval_info', $geval_info);

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_evaluate');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('evaluate');
        //不显示左菜单
        View::assign('left_show', 'order_view');
        return View::fetch($this->template_dir . 'add_image');
    }

    public function add_image_save() {
        $geval_id = intval(input('param.geval_id'));
        $geval_image = '';
        $evaluate_image_array = input('post.evaluate_image/a'); #获取数组
        foreach ($evaluate_image_array as $value) {
            if (!empty($value)) {
                $geval_image .= $value . ',';
            }
        }
        $geval_image = rtrim($geval_image, ',');

        $evaluategoods_model = model('evaluategoods');

        $geval_info = $evaluategoods_model->getEvaluategoodsInfoByID($geval_id);
        if (empty($geval_info)) {
            ds_json_encode(10001, lang('param_error'));
        }
        if ($geval_info['geval_frommemberid'] != session('member_id')) {
            ds_json_encode(10001, lang('param_error'));
        }

        $update = array();
        $update['geval_image'] = $geval_image;
        $condition = array();
        $condition[] = array('geval_id','=',$geval_id);
        $result = $evaluategoods_model->editEvaluategoods($update, $condition);
        if ($result) {
            ds_json_encode(10000, lang('ds_common_save_succ'));
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    public function getMemberItemList() {
        $menu_array = array(
            array(
                'name' => 'evaluate',
                'text' => lang('trade_reviews_orders'),
                'url' => url('Memberevaluate/index')
            ),
        );
        return $menu_array;
    }

}
