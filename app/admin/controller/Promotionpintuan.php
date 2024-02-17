<?php
/**
 * 拼团管理
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
class Promotionpintuan extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/promotionpintuan.lang.php');
    }
    /**
     * 拼团列表
     */
    public function index()
    {
        $pintuan_model = model('ppintuan');
        $condition = array();
        if (!empty(input('param.pintuan_name'))) {
            $condition[] = array('pintuan_name','like', '%' . input('param.pintuan_name') . '%');
        }
        if (input('param.state') != '') {
            $condition[] = array('pintuan_state','=',intval(input('param.state')));
        }
        $pintuan_list = $pintuan_model->getPintuanList($condition, 10, 'pintuan_state desc, pintuan_end_time desc');
        View::assign('pintuan_list', $pintuan_list);
        View::assign('show_page', $pintuan_model->page_info->render());
        View::assign('pintuan_state_array', $pintuan_model->getPintuanStateArray());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('pintuan_list');
        return View::fetch();
    }
    
    
    
    /**
     * 添加拼团活动
     * */
    public function pintuan_add() {
        if (!request()->isPost()) {
            //输出导航
            $this->setAdminCurItem('pintuan_add');
            return View::fetch();
        } else {
            //验证输入
            $pintuan_name = trim(input('post.pintuan_name'));
            $start_time = strtotime(input('post.start_time'));
            $end_time = strtotime(input('post.end_time'));
            $pintuan_limit_number = intval(input('post.pintuan_limit_number'));
            if ($pintuan_limit_number <= 1) {
                $pintuan_limit_number = 2;
            }
            //成团时限
            $pintuan_limit_hour = intval(input('post.pintuan_limit_hour'));
            if ($pintuan_limit_hour <= 0) {
                $pintuan_limit_hour = 1;
            }
            //购买限制
            $pintuan_limit_quantity = intval(input('post.pintuan_limit_quantity'));
            if ($pintuan_limit_quantity <= 0) {
                $pintuan_limit_quantity = 1;
            }
            //购买折扣
            $pintuan_zhe = intval(input('post.pintuan_zhe'));
            if ($pintuan_zhe <= 0 || $pintuan_zhe >= 10) {
                $pintuan_zhe = 1;
            }

            if (empty($pintuan_name)) {
                ds_json_encode(10001,lang('pintuan_name_error'));
            }
            if ($start_time >= $end_time) {
                ds_json_encode(10001,lang('greater_than_start_time'));
            }


            //获取提交的数据
            $goods_id = intval(input('post.pintuan_goods_id'));
            if (empty($goods_id)) {
                ds_json_encode(10001,lang('param_error'));
            }
            $goods_model = model('goods');
            $goods_info = $goods_model->getGoodsInfoByID($goods_id);
            if (empty($goods_info) ) {
                ds_json_encode(10001,lang('param_error'));
            }

            //判断此商品是否在拼团中，拼团中的商品是不可以参加活动
            $result = $this->_check_allow_pintuan($goods_info['goods_commonid']);
            if($result!=TRUE){
                ds_json_encode(10001,$result['message']);
            }
            
            
            //生成活动
            $ppintuan_model = model('ppintuan');
            $param = array();
            $param['pintuan_name'] = $pintuan_name;
            $param['pintuan_starttime'] = $start_time;
            $param['pintuan_end_time'] = $end_time;
            $param['pintuan_zhe'] = $pintuan_zhe;
            $param['pintuan_limit_number'] = $pintuan_limit_number;
            $param['pintuan_limit_hour'] = $pintuan_limit_hour;
            $param['pintuan_limit_quantity'] = $pintuan_limit_quantity;
            $param['pintuan_goods_id'] = $goods_info['goods_id'];
            $param['pintuan_is_virtual'] = $goods_info['is_virtual'];
            $param['pintuan_goods_price'] = $goods_info['goods_price'];
            $param['pintuan_goods_name'] = $goods_info['goods_name'];
            $param['pintuan_goods_commonid'] = $goods_info['goods_commonid'];
            $param['pintuan_image'] = $goods_info['goods_image'];
            $result = $ppintuan_model->addPintuan($param);
            if ($result) {
                $this->log(lang('add_group_activities') . $pintuan_name . lang('activity_number') . $result);
                $ppintuan_model->_dGoodsPintuanCache($goods_info['goods_commonid']);#清除缓存
                ds_json_encode(10000,lang('pintuan_add_success'));
            } else {
                ds_json_encode(10001,lang('pintuan_add_fail'));
            }
        }
    }

    /**
     * 编辑拼团活动
     * */
    public function pintuan_edit() {
        if (!request()->isPost()) {
            $ppintuan_model = model('ppintuan');
            $pintuan_info = $ppintuan_model->getPintuanInfoByID(input('param.pintuan_id'));
            if (empty($pintuan_info) || !$pintuan_info['editable']) {
                $this->error(lang('param_error'));
            }
            View::assign('pintuan_info', $pintuan_info);
            $this->setAdminCurItem('pintuan_edit');
            return View::fetch('pintuan_add');
        } else {
            $pintuan_id = input('param.pintuan_id');

            $ppintuan_model = model('ppintuan');

            $pintuan_info = $ppintuan_model->getPintuanInfoByID($pintuan_id);
            if (empty($pintuan_info) || !$pintuan_info['editable']) {
                $this->error(lang('param_error'));
            }

            //验证输入
            $pintuan_name = trim(input('post.pintuan_name'));
            if (empty($pintuan_name)) {
                $this->error(lang('pintuan_name_error'));
            }

            $pintuan_limit_number = intval(input('post.pintuan_limit_number'));
            if ($pintuan_limit_number <= 1) {
                $pintuan_limit_number = 2;
            }
            //成团时限
            $pintuan_limit_hour = intval(input('post.pintuan_limit_hour'));
            if ($pintuan_limit_hour <= 0) {
                $pintuan_limit_hour = 1;
            }
            //购买限制
            $pintuan_limit_quantity = intval(input('post.pintuan_limit_quantity'));
            if ($pintuan_limit_quantity <= 0) {
                $pintuan_limit_quantity = 1;
            }
            //购买折扣
            $pintuan_zhe = intval(input('post.pintuan_zhe'));
            if ($pintuan_zhe <= 0 || $pintuan_zhe >= 10) {
                $pintuan_zhe = 1;
            }

            //生成活动
            $param = array();
            $param['pintuan_name'] = $pintuan_name;
            $param['pintuan_zhe'] = $pintuan_zhe;
            $param['pintuan_limit_number'] = $pintuan_limit_number;
            $param['pintuan_limit_hour'] = $pintuan_limit_hour;
            $param['pintuan_limit_quantity'] = $pintuan_limit_quantity;


            $result = $ppintuan_model->editPintuan($param, array('pintuan_id' => $pintuan_id));
            if ($result) {
                $this->log(lang('edit_group_activities') . $pintuan_name . lang('activity_number') . $pintuan_id);
                $ppintuan_model->_dGoodsPintuanCache($pintuan_info['pintuan_goods_commonid']);#清除缓存
                $this->success(lang('ds_common_op_succ'), url('Promotionpintuan/index'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }




    /**
     * 选择活动商品
     * */
    public function search_goods() {
        $goods_model = model('goods');
        $condition = array();
        $condition[] = array('goods_lock','=',0);
        $condition[] = array('goods_state','=',1);
        $condition[] = array('goods_name','like', '%' . input('param.goods_name') . '%');
        $goods_list = $goods_model->getGoodsCommonListForPromotion($condition, '*', 8, '');
        View::assign('goods_list', $goods_list);
        View::assign('show_page', $goods_model->page_info->render());
        echo View::fetch();
        exit;
    }

    public function pintuan_goods_info() {
        $goods_commonid = intval(input('param.goods_commonid'));

        $data = array();
        $data['result'] = true;
        //判断此商品是否已经参加拼团，
        $result = $this->_check_allow_pintuan($goods_commonid);
        if($result['result'] != TRUE){
            echo json_encode($result);
            die;
        }
        
        //获取商品具体信息用于显示
        $goods_model = model('goods');
        $condition = array();
        $condition[] = array('goods_commonid','=',$goods_commonid);
        $goods_list = $goods_model->getGoodsOnlineList($condition);
        if (empty($goods_list)) {
            $data['result'] = false;
            $data['message'] = lang('param_error');
            echo json_encode($data);
            die;
        }
        $goods_info = $goods_list[0];
        $data['goods_id'] = $goods_info['goods_id'];
        $data['goods_name'] = $goods_info['goods_name'];
        $data['goods_price'] = $goods_info['goods_price'];
        $data['goods_image'] = goods_thumb($goods_info, 240);
        $data['goods_href'] = url('Goods/index', array('goods_id' => $goods_info['goods_id']));

        echo json_encode($data);
        die;
    }

    /*
     * 判断此商品是否已经参加拼团
     */
    private function _check_allow_pintuan($goods_commonid) {
        $condition = array();
        $condition[] = array('pintuan_goods_commonid','=',$goods_commonid);
        $condition[] = array('pintuan_state','=',1);
        $pintuan = model('ppintuan')->getPintuanInfo($condition);
        $result['result'] = TRUE;
        if (!empty($pintuan)) {
            $result['result'] = FALSE;
            $result['message'] = lang('goods_are_syndicate');
        }
        return $result;
    }
    
    
    
    /**
     * 拼团详情
     */
    public function pintuan_manage()
    {
        $ppintuangroup_model = model('ppintuangroup');
        $ppintuanorder_model = model('ppintuanorder');
        $pintuan_id = intval(input('param.pintuan_id'));
        $condition = array();
        $condition[] = array('pintuan_id','=',$pintuan_id);
        if (input('param.pintuangroup_state') != null){
            $condition[] = array('pintuangroup_state','=',intval(input('param.pintuangroup_state')));
        }

        $ppintuangroup_list = $ppintuangroup_model->getPpintuangroupList($condition, 10); #获取开团信息
        foreach ($ppintuangroup_list as $key => $ppintuangroup) {
            //获取开团订单下的参团订单
            $condition = array();
            $condition[] = array('pintuangroup_id','=',$ppintuangroup['pintuangroup_id']);
            if($ppintuangroup['pintuangroup_is_virtual']){
                $ppintuangroup_list[$key]['order_list'] = $ppintuanorder_model->getPpintuanvrorderList($condition);
            }else{
                $ppintuangroup_list[$key]['order_list'] = $ppintuanorder_model->getPpintuanorderList($condition);
            }
        }
        $ppintuan_info = model('ppintuan')->getPintuanInfo(['pintuan_id'=>$pintuan_id]);
        View::assign('pintuan_info', $ppintuan_info);
        View::assign('show_page', $ppintuangroup_model->page_info->render());
        View::assign('pintuangroup_list', $ppintuangroup_list);
        View::assign('pintuangroup_state_array', $ppintuangroup_model->getPintuangroupStateArray());
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        $this->setAdminCurItem('pintuan_manage');
        return View::fetch();
    }
    
    /**
     * 拼团活动 提前结束
     */
    public function pintuan_end() {
        $pintuan_id = intval(input('param.pintuan_id'));
        $ppintuan_model = model('ppintuan');

        $pintuan_info = $ppintuan_model->getPintuanInfoByID($pintuan_id);
        if (!$pintuan_info) {
            ds_json_encode(10001, lang('param_error'));
        }

        /**
         * 指定拼团活动结束
         */
        $result = $ppintuan_model->endPintuan(array('pintuan_id' => $pintuan_id));

        if ($result) {
            $this->log('拼团活动提前结束，活动名称：' . $pintuan_info['pintuan_name'] . '活动编号：' . $pintuan_id, 1);
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }
    /**
     * 拼团活动 删除
     */
    public function pintuan_del() {
        $pintuan_id = intval(input('param.pintuan_id'));
        $ppintuan_model = model('ppintuan');
        $pintuan_info = $ppintuan_model->getPintuanInfoByID($pintuan_id);
        if (!$pintuan_info) {
            ds_json_encode(10001, lang('param_error'));
        }
        /**
         * 指定拼团活动删除
         */
        $result = $ppintuan_model->delPintuan(array('pintuan_id' => $pintuan_id));

        if ($result) {
            $this->log('拼团活动删除，活动名称：' . $pintuan_info['pintuan_name'] . '活动编号：' . $pintuan_id, 1);
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }

    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'pintuan_list', 'text' => lang('pintuan_list'), 'url' => url('Promotionpintuan/index')
            ),
            array(
                'name' => 'pintuan_add', 'text' => lang('pintuan_add'), 'url' => url('Promotionpintuan/pintuan_add')
            ),
        );
        if (request()->action() == 'pintuan_detail'){
            $menu_array[] = array(
                'name' => 'pintuan_detail', 'text' => lang('pintuan_detail'),
                'url' => url('Promotionpintuan/pintuan_detail')
            );
        }
            
        return $menu_array;
    }
}

