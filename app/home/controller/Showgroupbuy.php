<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
use think\facade\Db;
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
class Showgroupbuy extends BaseMall
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/groupbuy.lang.php');
        //检查抢购功能是否开启
        if (intval(config('ds_config.groupbuy_allow')) !== 1){
            $this->error(lang('groupbuy_unavailable'),'index');
        }
        if (request()->action() != 'groupbuy_detail') {
            // 抢购价格区间
            $this->groupbuy_price = rkcache('groupbuyprice', true);
            View::assign('price_list', $this->groupbuy_price);

            $groupbuy_model = model('groupbuy');

            // 线上抢购分类
            $this->groupbuy_classes = $groupbuy_model->getGroupbuyClasses();
            
            View::assign('groupbuy_classes', $this->groupbuy_classes);

            // 虚拟抢购分类
            $this->groupbuy_vr_classes = $groupbuy_model->getGroupbuyVrClasses();
            View::assign('groupbuy_vr_classes', $this->groupbuy_vr_classes);

        }
    }

    /**
     * 抢购聚合页
     */
    public function index()
    {
        $groupbuy_model = model('groupbuy');

        // 线上抢购
        $groupbuy = $groupbuy_model->getGroupbuyOnlineList(array(array('groupbuy_recommended' ,'=', 1), array('groupbuy_is_vr' ,'=', 0)), 9);

        View::assign('groupbuy', $groupbuy);

        // 虚拟抢购
        $vr_groupbuy = $groupbuy_model->getGroupbuyOnlineList(array(array('groupbuy_recommended' ,'=', 1), array('groupbuy_is_vr' ,'=', 1)), 9);

        View::assign('vr_groupbuy', $vr_groupbuy);

        // 轮播图片
        $picArr = array();

        foreach (range(1, 4) as $i) {
            $a = config('ds_config.live_pic' . $i);
            if ($a) {
                $picArr[] = array($a, config('ds_config.live_link'. $i));
            }
        }

        View::assign('picArr', $picArr);

        View::assign('current', 'online');
        return View::fetch($this->template_dir.'index');
    }

    /**
     * 进行中的虚拟抢购
     */
    public function vr_groupbuy_list()
    {
        View::assign('current', 'online');
        View::assign('buy_button', lang('groupbuy_buy'));
        $this->_show_vr_groupbuy_list('getGroupbuyOnlineList');
        return View::fetch($this->template_dir.'groupbuy_vr_list');
    }

    /**
     * 即将开始的虚拟抢购
     */
    public function vr_groupbuy_soon()
    {
        View::assign('current', 'soon');
        View::assign('buy_button', lang('not_at_the'));
        $this->_show_vr_groupbuy_list('getGroupbuySoonList');
        return View::fetch($this->template_dir.'groupbuy_vr_list');
    }

    /**
     * 往期虚拟抢购
     */
    public function vr_groupbuy_history()
    {
        View::assign('current', 'history');
        View::assign('buy_button', lang('has_ended'));
        $this->_show_vr_groupbuy_list('getGroupbuyHistoryList');
        return View::fetch($this->template_dir.'groupbuy_vr_list');
    }

    /**
     * 获取抢购列表
     */
    private function _show_vr_groupbuy_list($function_name)
    {
        $groupbuy_model = model('groupbuy');
        $condition = array();
        $condition[] = array('groupbuy_is_vr','=', 1);

        $order = '';

        // 分类筛选条件
        if (($vr_class_id = (int) input('vr_class')) > 0) {
            $condition[] = array('vr_class_id','=',$vr_class_id);

            if (($vr_s_class_id = (int) input('vr_s_class')) > 0)
                $condition[] = array('vr_s_class_id','=',$vr_s_class_id);
        }


        // 价格区间筛选条件
        if (($price_id = intval(input('groupbuy_price'))) > 0
            && isset($this->groupbuy_price[$price_id])) {
            $p = $this->groupbuy_price[$price_id];
            $condition[] = array('groupbuy_price','between',array($p['gprange_start'], $p['gprange_end']));
        }

        // 排序
        $groupbuy_order_key = trim(input('groupbuy_order_key'));
        $groupbuy_order = input('groupbuy_order') == '2' ? 'desc' : 'asc';
        if (!empty($groupbuy_order_key)) {
            switch ($groupbuy_order_key) {
                case '1':
                    $order = 'groupbuy_price ' . $groupbuy_order;
                    break;
                case '2':
                    $order = 'groupbuy_rebate ' . $groupbuy_order;
                    break;
                case '3':
                    $order = 'groupbuy_buyer_count ' . $groupbuy_order;
                    break;
            }
        }

        $groupbuy_list = $groupbuy_model->$function_name($condition, 20, $order);
        View::assign('groupbuy_list', $groupbuy_list);
        View::assign('show_page', $groupbuy_model->page_info->render());

        View::assign('html_title', lang('text_groupbuy_list'));

        $this->_assign_seo(model('seo')->type('group')->show());

        /* 引用搜索相关函数 */
        require_once(base_path() . '/home/common_search.php');
        View::assign('groupbuyMenuIsVr', 1);
    }

    /**
     * 进行中的抢购抢购
     **/
    public function groupbuy_list() {
        View::assign('current', 'online');
        View::assign('buy_button', lang('groupbuy_buy'));
        $this->_show_groupbuy_list('getGroupbuyOnlineList');
        return View::fetch($this->template_dir.'groupbuy_list');
    }

    /**
     * 即将开始的抢购
     **/
    public function groupbuy_soon() {
        View::assign('current', 'soon');
        View::assign('buy_button', lang('not_at_the'));
        $this->_show_groupbuy_list('getGroupbuySoonList');
        return View::fetch($this->template_dir.'groupbuy_list');
    }

    /**
     * 往期抢购
     **/
    public function groupbuy_history() {
        View::assign('current', 'history');
        View::assign('buy_button', lang('has_ended'));
        $this->_show_groupbuy_list('getGroupbuyHistoryList');
        return View::fetch($this->template_dir.'groupbuy_list');
    }

    /**
     * 获取抢购列表
     **/
    private function _show_groupbuy_list($function_name) {
        $groupbuy_model = model('groupbuy');
        $condition = array();
        $condition[] = array('groupbuy_is_vr','=', 0);
        $order = '';

        // 分类筛选条件
        if (($gclass_id = (int) input('class')) > 0) {
            $condition[] = array('gclass_id','=',$gclass_id);

            if (($s_gclass_id = (int) input('s_class')) > 0)
                $condition[] = array('s_gclass_id','=',$s_gclass_id);
        }

        // 价格区间筛选条件
        if (($price_id = intval(input('groupbuy_price'))) > 0
            && isset($this->groupbuy_price[$price_id])) {
            $p = $this->groupbuy_price[$price_id];
            $condition[] = array('groupbuy_price','between',array($p['gprange_start'], $p['gprange_end']));
        }

        // 排序
        $groupbuy_order_key = trim(input('groupbuy_order_key'));
        $groupbuy_order = input('groupbuy_order') == '2'?'desc':'asc';
        if(!empty($groupbuy_order_key)) {
            switch ($groupbuy_order_key) {
                case '1':
                    $order = 'groupbuy_price '.$groupbuy_order;
                    break;
                case '2':
                    $order = 'groupbuy_rebate '.$groupbuy_order;
                    break;
                case '3':
                    $order = 'groupbuy_buyer_count '.$groupbuy_order;
                    break;
            }
        }

        $groupbuy_list = $groupbuy_model->$function_name($condition, 20, $order);
        View::assign('groupbuy_list', $groupbuy_list);
        View::assign('show_page', $groupbuy_model->page_info->render());

        View::assign('html_title', lang('text_groupbuy_list'));

        $this->_assign_seo(model('seo')->type('group')->show());

        /* 引用搜索相关函数 */
        require_once(base_path() . '/home/common_search.php');
        View::assign('groupbuyMenuIsVr', 0);

    }

    /**
     * 抢购详细信息
     **/
    public function groupbuy_detail() {
        $group_id = intval(input('param.group_id'));

        $groupbuy_model = model('groupbuy');

        //获取抢购详细信息
        $groupbuy_info = $groupbuy_model->getGroupbuyInfoByID($group_id);
        if(empty($groupbuy_info)) {
            $this->error(lang('param_error'),'showgroupbuy/index');
        }
        View::assign('groupbuy_info',$groupbuy_info);

        View::assign('groupbuyMenuIsVr', (bool) $groupbuy_info['groupbuy_is_vr']);

        if ($groupbuy_info['groupbuy_is_vr']) {
            $goods_info = model('goods')->getGoodsInfoByID($groupbuy_info['goods_id']);
            $buy_limit = max(0, (int) $goods_info['virtual_limit']);
            $upper_limit = max(0, (int) $groupbuy_info['groupbuy_upper_limit']);
            if ($buy_limit < 1 || ($buy_limit > 0 && $upper_limit > 0 && $buy_limit > $upper_limit)) {
                $buy_limit = $upper_limit;
            }

            View::assign('goods_info', $goods_info);
            View::assign('buy_limit', $buy_limit);
        } else {
            View::assign('buy_limit', $groupbuy_info['groupbuy_upper_limit']);
        }


        // 浏览数加1
        $update_array = array();
        $update_array['groupbuy_views'] = Db::raw('groupbuy_views+1');

        $groupbuy_model->editGroupbuy($update_array, array('groupbuy_id'=>$group_id));


        //获取店铺推荐商品
        $commended_groupbuy_list = $groupbuy_model->getGroupbuyCommendedList(8);
        View::assign('commended_groupbuy_list', $commended_groupbuy_list);

        // 好评率
        $evaluategoods_model = model('evaluategoods');
        $evaluate_info = $evaluategoods_model->getEvaluategoodsInfoByCommonidID($groupbuy_info['goods_commonid']);
        View::assign('evaluate_info', $evaluate_info);

        $this->_assign_seo(model('seo')->type('group_content')->param(array('name'=>$groupbuy_info['groupbuy_name']))->show());
        /* 引用搜索相关函数 */
        require_once(base_path() . '/home/common_search.php');
        return View::fetch($this->template_dir.'groupbuy_detail');
    }

    /**
     * 购买记录
     */
    public function groupbuy_order() {
        $group_id = intval(input('group_id'));
        if ($group_id > 0) {
            if (!input('is_vr')) {
                //获取购买记录
                $order_model = model('order');
                $condition = array();
                $condition[] = array('goods_type','=',2);
                $condition[] = array('promotions_id','=',$group_id);
                $order_goods_list = $order_model->getOrdergoodsList($condition, '*', 0 , 10);
                View::assign('order_goods_list', $order_goods_list);
                View::assign('show_page', $order_model->page_info->render());
                if (!empty($order_goods_list)) {
                    $orderid_array = array();
                    foreach ($order_goods_list as $value) {
                        $orderid_array[] = $value['order_id'];
                    }
                    $order_list = $order_model->getNormalOrderList(array(array('order_id','in', $orderid_array)), '', 'order_id,buyer_name,add_time');
                    $order_list = array_under_reset($order_list, 'order_id');
                    View::assign('order_list', $order_list);
                }
            } else {
                $vrorder_model = model('vrorder');
                $condition = array();
                $condition[] = array('order_promotion_type','=',1);
                $condition[] = array('promotions_id','=',$group_id);
                $order_goods_list = $vrorder_model->getVrorderAndOrderGoodsSalesRecordList($condition, '*', 10);
                View::assign('order_goods_list', $order_goods_list);
                View::assign('show_page', $vrorder_model->page_info->render());
            }
        }
        echo View::fetch($this->template_dir.'groupbuy_order');
    }

    /**
     * 商品评价
     */
    public function groupbuy_evaluate() {
        $goods_commonid = intval(input('commonid'));
        if ($goods_commonid > 0) {
            $condition = array();
            $condition[] = array('goods_commonid','=',$goods_commonid);
            $goods_list = model('goods')->getGoodsList($condition, 'goods_id');
            if (!empty($goods_list)) {
                $goodsid_array = array();
                foreach ($goods_list as $value) {
                    $goodsid_array[] = $value['goods_id'];
                }
                $evaluategoods_model = model('evaluategoods');
                $where = array();
                $where[] = array('geval_goodsid', 'in', $goodsid_array);
                $evaluate_list = $evaluategoods_model->getEvaluategoodsList($where, 10);
                View::assign('goodsevallist',$evaluate_list);
                View::assign('show_page',$evaluategoods_model->page_info->render());
            }
        }
        echo View::fetch($this->template_dir.'groupbuy_evaluate');
    }

}