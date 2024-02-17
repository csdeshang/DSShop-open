<?php

/**
 * 秒杀
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
class Promotionxianshi extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/promotionxianshi.lang.php');
    }

    /**
     * 活动列表
     * */
    public function index() {
        //自动开启秒杀
        if (intval(input('param.promotion_allow')) === 1) {
            $config_model = model('config');
            $update_array = array();
            $update_array['promotion_allow'] = 1;
            $config_model->editConfig($update_array);
        }

        $xianshi_model = model('pxianshi');
        $condition = array();
        if (!empty(input('param.xianshi_name'))) {
            $condition[] = array('xianshi_name','like', '%' . input('param.xianshi_name') . '%');
        }
        if (!empty(input('param.state'))) {
            $condition[] = array('xianshi_state','=',intval(input('param.state')));
        }
        $xianshi_list = $xianshi_model->getXianshiList($condition, 10, 'xianshi_state desc, xianshi_end_time desc');
        View::assign('xianshi_list', $xianshi_list);
        View::assign('show_page', $xianshi_model->page_info->render());
        View::assign('xianshi_state_array', $xianshi_model->getXianshiStateArray());

        $this->setAdminCurItem('xianshi_list');
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        return View::fetch();
    }

    /**
     * 添加秒杀活动
     * */
    public function xianshi_add() {
        if (!request()->isPost()) {
            $this->setAdminCurItem('xianshi_add');
            return View::fetch();
        } else {
            //验证输入
            $xianshi_name = trim(input('post.xianshi_name'));
            $start_time = strtotime(input('post.start_time'));
            $end_time = strtotime(input('post.end_time'));
            $lower_limit = intval(input('post.lower_limit'));
            if ($lower_limit <= 0) {
                $lower_limit = 1;
            }
            if (empty($xianshi_name)) {
                ds_json_encode(10001,lang('xianshi_name_error'));
            }
            if ($start_time >= $end_time) {
                ds_json_encode(10001,lang('greater_than_start_time'));
            }


            //生成活动
            $pxianshi_model = model('pxianshi');
            $param = array();
            $param['xianshi_name'] = $xianshi_name;
            $param['xianshi_title'] = input('post.xianshi_title');
            $param['xianshi_explain'] = input('post.xianshi_explain');
            $param['xianshi_starttime'] = $start_time;
            $param['xianshi_end_time'] = $end_time;
            $param['xianshi_lower_limit'] = $lower_limit;
            $result = $pxianshi_model->addXianshi($param);
            if ($result) {
                $this->log(lang('add_limited_time_discount_activity') . $xianshi_name . lang('activity_number') . $result);
                // 添加计划任务
                $this->addcron(array('cron_exetime' => $param['xianshi_end_time'], 'cron_value' => serialize(intval($result)), 'cron_type' => 'editExpireXianshi'), true);
                ds_json_encode(10000,lang('xianshi_add_success'));

            } else {
                ds_json_encode(10001,lang('xianshi_add_fail'));
            }
        }
    }

    /**
     * 编辑秒杀活动
     * */
    public function xianshi_edit() {
        if (!request()->isPost()) {
            $pxianshi_model = model('pxianshi');

            $xianshi_info = $pxianshi_model->getXianshiInfoByID(input('param.xianshi_id'));
            if (empty($xianshi_info) || !$xianshi_info['editable']) {
                $this->error(lang('param_error'));
            }

            View::assign('xianshi_info', $xianshi_info);

            $this->setAdminCurItem('xianshi_edit');
            return View::fetch('xianshi_add');
        } else {
            $xianshi_id = input('post.xianshi_id');

            $pxianshi_model = model('pxianshi');
            $xianshigoods_model = model('pxianshigoods');

            $xianshi_info = $pxianshi_model->getXianshiInfoByID($xianshi_id);
            if (empty($xianshi_info) || !$xianshi_info['editable']) {
                ds_json_encode(10001,lang('param_error'));
            }

            //验证输入
            $xianshi_name = trim(input('post.xianshi_name'));
            $lower_limit = intval(input('post.lower_limit'));
            if ($lower_limit <= 0) {
                $lower_limit = 1;
            }
            if (empty($xianshi_name)) {
                ds_json_encode(10001,lang('xianshi_name_error'));
            }

            //生成活动
            $param = array();
            $param['xianshi_name'] = $xianshi_name;
            $param['xianshi_title'] = input('post.xianshi_title');
            $param['xianshi_explain'] = input('post.xianshi_explain');
            $param['xianshi_lower_limit'] = $lower_limit;
            $param_goods = array();
            $param_goods['xianshi_name'] = $xianshi_name;
            $param_goods['xianshi_title'] = input('post.xianshi_title');
            $param_goods['xianshi_explain'] = input('post.xianshi_explain');
            $param_goods['xianshigoods_lower_limit'] = $lower_limit;
            $result = $pxianshi_model->editXianshi($param, array('xianshi_id' => $xianshi_id));
            $xianshigoods_model->editXianshigoods($param_goods, array('xianshi_id' => $xianshi_id));
            if ($result && $result) {
                $this->log(lang('edit_limited_time_discount_activity') . $xianshi_name . lang('activity_number') . $xianshi_id);
                ds_json_encode(10000,lang('ds_common_op_succ'));
            } else {
                ds_json_encode(10001,lang('ds_common_op_fail'));
            }
        }
    }

    /**
     * 秒杀活动取消
     * */
    public function xianshi_cancel() {
        $xianshi_id = intval(input('param.xianshi_id'));
        $xianshi_model = model('pxianshi');
        $result = $xianshi_model->cancelXianshi(array('xianshi_id' => $xianshi_id));
        if ($result) {
            $this->log('取消秒杀活动，活动编号' . $xianshi_id);
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    /**
     * 秒杀活动删除
     * */
    public function xianshi_del() {
        $xianshi_model = model('pxianshi');
        $xianshi_id = input('param.xianshi_id');
        $xianshi_id_array = ds_delete_param($xianshi_id);
        if ($xianshi_id_array === FALSE) {
            ds_json_encode(10001, lang('param_error'));
        }
        $condition = array();
        $condition[] = array('xianshi_id','in', $xianshi_id_array);
        $result = $xianshi_model->delXianshi($condition);
        if ($result) {
            $this->log('删除秒杀活动，活动编号' . $xianshi_id);
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    /**
     * 活动详细信息
     * */
    public function xianshi_detail() {
        $xianshi_id = intval(input('param.xianshi_id'));

        $xianshi_model = model('pxianshi');
        $xianshigoods_model = model('pxianshigoods');

        $xianshi_info = $xianshi_model->getXianshiInfoByID($xianshi_id);
        if (empty($xianshi_info)) {
            $this->error(lang('param_error'));
        }
        View::assign('xianshi_info', $xianshi_info);

        //获取秒杀商品列表
        $condition = array();
        $condition[] = array('xianshi_id','=',$xianshi_id);
        $xianshigoods_list = $xianshigoods_model->getXianshigoodsExtendList($condition, 5);
        View::assign('xianshi_goods_list', $xianshigoods_list);
        View::assign('show_page', $xianshigoods_model->page_info->render());
        $this->setAdminCurItem('xianshi_detail');
        return View::fetch();
    }

    /**
     * 选择活动商品
     * */
    public function goods_select() {
        $goods_model = model('goods');
        $condition = array();
        $condition[] = array('goods.goods_name','like', '%' . input('param.goods_name') . '%');
        $goods_list = $goods_model->getGoodsListForPromotion($condition, 'goods.goods_id,goods.goods_commonid,goods.goods_name,goods.goods_image,goods.goods_price', 10, 'xianshi');

        View::assign('goods_list', $goods_list);
        View::assign('show_page', $goods_model->page_info->render());
        echo View::fetch();
    }

    /**
     * ajax修改抢购信息
     */
    public function ajax() {
        $result = true;
        $update_array = array();
        $condition = array();

        switch (input('param.branch')) {
            case 'recommend':
                $pxianshigoods_model = model('pxianshigoods');
                $update_array['xianshigoods_recommend'] = input('param.value');
                $condition[] = array('xianshigoods_id','=',input('param.id'));
                $result = $pxianshigoods_model->editXianshigoods($update_array, $condition);
                break;
        }

        if ($result) {
            echo 'true';
            exit;
        } else {
            echo 'false';
            exit;
        }
    }

    /**
     * 秒杀商品添加
     * */
    public function xianshi_goods_add() {
        $goods_id = intval(input('post.goods_id'));
        $xianshi_id = intval(input('post.xianshi_id'));
        $xianshi_price = floatval(input('post.xianshi_price'));

        $goods_model = model('goods');
        $pxianshi_model = model('pxianshi');
        $xianshigoods_model = model('pxianshigoods');

        $data = array();
        $data['result'] = true;

        $goods_info = $goods_model->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            $data['result'] = false;
            $data['message'] = lang('param_error');
            echo json_encode($data);
            die;
        }

        $xianshi_info = $pxianshi_model->getXianshiInfoByID($xianshi_id);
        if (!$xianshi_info) {
            $data['result'] = false;
            $data['message'] = lang('param_error');
            echo json_encode($data);
            die;
        }

        //检查商品是否已经参加同时段活动
        $condition = array();
        $condition[] = array('xianshigoods_end_time','>', $xianshi_info['xianshi_starttime']);
        $condition[] = array('goods_id','=',$goods_id);
        $xianshigoods = $xianshigoods_model->getXianshigoodsExtendList($condition);
        if (!empty($xianshigoods)) {
            $data['result'] = false;
            $data['message'] = lang('product_participated_simultaneous_activities');
            echo json_encode($data);
            die;
        }

        //添加到活动商品表
        $param = array();
        $param['xianshi_id'] = $xianshi_info['xianshi_id'];
        $param['xianshi_name'] = $xianshi_info['xianshi_name'];
        $param['xianshi_title'] = $xianshi_info['xianshi_title'];
        $param['xianshi_explain'] = $xianshi_info['xianshi_explain'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['goods_commonid'] = $goods_info['goods_commonid'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['xianshigoods_price'] = $xianshi_price;
        $param['goods_image'] = $goods_info['goods_image'];
        $param['xianshigoods_starttime'] = $xianshi_info['xianshi_starttime'];
        $param['xianshigoods_end_time'] = $xianshi_info['xianshi_end_time'];
        $param['xianshigoods_lower_limit'] = $xianshi_info['xianshi_lower_limit'];

        $result = array();
        $xianshigoods_info = $xianshigoods_model->addXianshigoods($param);
        if ($xianshigoods_info) {
            $result['result'] = true;
            $data['message'] = lang('add_success');
            $data['xianshi_goods'] = $xianshigoods_info;
            $this->log(lang('add_limited_time_discount_items') . $xianshi_info['xianshi_name'] . '，' . lang('ds_goods_name') . '：' . $goods_info['goods_name']);

            // 添加任务计划
            $this->addcron(array('cron_type' => 'updateGoodsPromotionPriceByGoodsId', 'cron_value' => serialize($goods_info['goods_id']), 'cron_exetime' => $param['xianshigoods_starttime']));
        } else {
            $data['result'] = false;
            $data['message'] = lang('param_error');
        }
        echo json_encode($data);
        die;
    }

    /**
     * 秒杀商品价格修改
     * */
    public function xianshi_goods_price_edit() {
        $xianshigoods_id = intval(input('param.xianshigoods_id'));
        $xianshi_price = floatval(input('param.xianshi_price'));

        if (!request()->isPost()){
            View::assign('xianshi_price',$xianshi_price);
            $this->setAdminCurItem('edit');
            return View::fetch('edit');
        }else{
            $data = array();
            $data['result'] = true;

            $xianshigoods_model = model('pxianshigoods');

            $xianshigoods_info = $xianshigoods_model->getXianshigoodsInfoByID($xianshigoods_id);
            if (!$xianshigoods_info) {
                $this->error(lang('ds_common_op_fail'));
            }

            $update = array();
            $update['xianshigoods_price'] = $xianshi_price;
            $condition = array();
            $condition[] = array('xianshigoods_id','=',$xianshigoods_id);
            $result = $xianshigoods_model->editXianshigoods($update, $condition);

            if ($result) {
                $xianshigoods_info['xianshigoods_price'] = $xianshi_price;
                $xianshigoods_info = $xianshigoods_model->getXianshigoodsExtendInfo($xianshigoods_info);
                $data['xianshi_price'] = $xianshigoods_info['xianshigoods_price'];
                $data['xianshi_discount'] = $xianshigoods_info['xianshi_discount'];

                // 添加对列修改商品促销价格
                model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsId','cron_value'=>serialize($xianshigoods_info['goods_id'])));

                $this->log(lang('limited_time_discount_price_modified') . $xianshigoods_info['xianshigoods_price'] . '，' . lang('ds_goods_name') . '：' . $xianshigoods_info['goods_name']);
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    /**
     * 秒杀商品删除
     * */
    public function xianshi_goods_delete() {
        $xianshigoods_model = model('pxianshigoods');
        $pxianshi_model = model('pxianshi');

        $data = array();
        $data['result'] = true;

        $xianshigoods_id = intval(input('param.xianshigoods_id'));
        $xianshigoods_info = $xianshigoods_model->getXianshigoodsInfoByID($xianshigoods_id);
        if (!$xianshigoods_info) {
            ds_json_encode(10001, lang('param_error'));

        }

        $xianshi_info = $pxianshi_model->getXianshiInfoByID($xianshigoods_info['xianshi_id']);
        if (!$xianshi_info) {
            ds_json_encode(10001, lang('param_error'));
        }

        if (!$xianshigoods_model->delXianshigoods(array('xianshigoods_id' => $xianshigoods_id))) {
            ds_json_encode(10001, lang('xianshi_goods_delete_fail'));
        }

        // 添加对列修改商品促销价格
        model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsId','cron_value'=>serialize($xianshigoods_info['goods_id'])));

        $this->log(lang('delete_time_limited_discount_items') . $xianshi_info['xianshi_name'] . '，' . lang('ds_goods_name') . '：' . $xianshigoods_info['goods_name']);
        ds_json_encode(10000, '删除成功');

    }

    /**
     * 页面内导航菜单
     *
     * @param string $menu_key 当前导航的menu_key
     * @param array $array 附加菜单
     * @return
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'xianshi_list', 'text' => lang('xianshi_list'), 'url' => url('Promotionxianshi/index')
            ),
            array(
                'name' => 'xianshi_add', 'text' => lang('xianshi_add'), 'url' => url('Promotionxianshi/xianshi_add')
            ),
        );
        if (request()->action() == 'xianshi_detail')
            $menu_array[] = array(
                'name' => 'xianshi_detail', 'text' => lang('xianshi_detail'),
                'url' => 'javascript:void(0)'
            );
        return $menu_array;
    }

}
