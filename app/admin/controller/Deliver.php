<?php

/**
 * 发货
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
class Deliver extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/deliver.lang.php');
    }
    /**
     * 发货列表
     *
     */
    public function index() {
        $order_model = model('order');
        $state = input('state');
        if (!in_array($state, array('deliverno', 'delivering', 'delivered'))) {
            $state = 'deliverno';
        }

        $order_state = str_replace(array('deliverno', 'delivering', 'delivered'), array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS), $state);
        $condition = array();
        $condition[] = array('order_state','=',$order_state);
        $condition[] = array('refund_state','=',0);


        $buyer_name = input('buyer_name');
        if ($buyer_name != '') {
            $condition[] = array('buyer_name','=',$buyer_name);
        }
        $order_sn = input('order_sn');
        if ($order_sn != '') {
            $condition[] = array('order_sn','=',$order_sn);
        }
        $query_start_date = input('query_start_date');
        $query_end_date = input('query_end_date');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_start_date);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $query_end_date);
        $start_unixtime = $if_start_date ? strtotime($query_start_date) : null;
        $end_unixtime = $if_end_date ? strtotime($query_end_date) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition[] = array('add_time','between', array($start_unixtime, $end_unixtime));
        }
        $order_list = $order_model->getOrderList($condition, 10, '*', 'order_id desc', 0, array('order_goods', 'order_common','ppintuanorder', 'member'));

        foreach ($order_list as $key => $order_info) {
            if(isset($order_info['extend_order_goods'])){
                foreach ($order_info['extend_order_goods'] as $value) {
                    $value['image_240_url'] = goods_cthumb($value['goods_image'], 240);
                    $value['goods_type_cn'] = get_order_goodstype($value['goods_type']);
                    $value['goods_url'] = url('Goods/index', ['goods_id' => $value['goods_id']]);
                    if ($value['goods_type'] == 5) {
                        $order_info['zengpin_list'][] = $value;
                    } else {
                        $order_info['goods_list'][] = $value;
                    }
                }

                if (empty($order_info['zengpin_list'])) {
                $order_info['goods_count'] = count($order_info['goods_list']);
            } else {
                $order_info['goods_count'] = count($order_info['goods_list']) + 1;
            }
            }
            $order_list[$key] = $order_info;
        }
        View::assign('order_list', $order_list);
        View::assign('show_page', $order_model->page_info->render());
        $this->setAdminCurItem($state);
        return View::fetch();
    }

    /**
     * 发货
     */
    public function send() {
        $order_id = input('param.order_id');
        if ($order_id <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }

        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        $if_allow_send = intval($order_info['lock_state']) || !in_array($order_info['order_state'], array(ORDER_STATE_PAY, ORDER_STATE_SEND));
        if ($if_allow_send) {
            ds_json_encode(10001,lang('param_error'));
        }

        if (!request()->isPost()) {
            View::assign('order_info', $order_info);
            //取发货地址
            $daddress_model = model('daddress');
            $daddress_info = array();
            if ($order_info['extend_order_common']['daddress_id'] > 0) {
                $daddress_info = $daddress_model->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
            }
            if(empty($daddress_info)){
                //取默认地址
                $daddress_info = $daddress_model->getAddressList(array(), '*', 'daddress_isdefault desc', 1);
                if (!empty($daddress_info)) {
                    $daddress_info = $daddress_info[0];
                    //写入发货地址编号
                    $this->_edit_order_daddress($daddress_info['daddress_id'], $order_id);
                } else {
                    //写入发货地址编号
                    $this->_edit_order_daddress(0, $order_id);
                }
            }
            View::assign('daddress_info', $daddress_info);

            $express_list = rkcache('express', true);

            View::assign('express_list', $express_list);
            
            $this->setAdminCurItem('send');
            return View::fetch();
        } else {
            $logic_order = model('order','logic');
            $post = input('post.');
            $post['reciver_info'] = $this->_get_reciver_info();
            if (empty($post['daddress_id'])){
                ds_json_encode(10001,'请选择发货地址');
            }
            $result = $logic_order->changeOrderSend($order_info, 'admin', session('admin_name'), $post);
            if (!$result['code']) {
                ds_json_encode(10001,$result['msg']);
            } else {
                ds_json_encode(10000,'操作成功');
            }
        }
    }
    /**
     * 批量发货
     */
    public function batch_send() {
        $order_id = ds_delete_param(input('param.order_id'));
        $order_model = model('order');
        $daddress_model = model('daddress');
        $condition = array();
        $condition[] = array('order_id','in',$order_id);
        $condition[] = array('lock_state','=',0);
        $condition[] = array('order_state','in',array(ORDER_STATE_PAY));
        $order_list = $order_model->getOrderList($condition, '', '*', 'order_id desc', 0, array('order_common'));
        if (request()->isPost()) {
            if(empty($order_list)){
                $this->error(lang('param_error'));
            }
            $send=input('param.send/a');
            $logic_order = model('order','logic');
                foreach($order_list as $order_info){
                    if(empty($send[$order_info['order_id']])){
                        $this->error(lang('param_error'));
                    }
                    if(!$send[$order_info['order_id']]['daddress_id']){
                        $this->error(lang('store_order_order_sn').$order_info['order_sn'].':'.lang('store_deliver_confirm_daddress'));
                    }
                    if(!$send[$order_info['order_id']]['express_id']){
                        $this->error(lang('store_order_order_sn').$order_info['order_sn'].':'.lang('store_deliver_express_select'));
                    }
                    if(!$send[$order_info['order_id']]['shipping_code']){
                        $this->error(lang('store_order_order_sn').$order_info['order_sn'].':'.lang('store_deliver_shipping_code_pl'));
                    }
                    $result = $logic_order->changeOrderSend($order_info, 'admin', session('admin_name'), array_merge($send[$order_info['order_id']],array(
                        'reciver_info'=>serialize($order_info['extend_order_common']['reciver_info']),
                        'shipping_express_id'=>$send[$order_info['order_id']]['express_id'],
                        'reciver_name'=>$order_info['extend_order_common']['reciver_name'],
                        'reciver_area'=>$order_info['extend_order_common']['reciver_info']['area'],
                        'reciver_street'=>$order_info['extend_order_common']['reciver_info']['street'],
                        'reciver_mob_phone'=>$order_info['extend_order_common']['reciver_info']['mob_phone'],
                        'reciver_tel_phone'=>$order_info['extend_order_common']['reciver_info']['tel_phone'],
                        'deliver_explain'=>$order_info['extend_order_common']['deliver_explain'],
                    )));
                    if(!$result['code']){
                        ds_json_encode(10001,$result['msg']);
                    }
                }
                
            dsLayerOpenSuccess($result['msg']);
         
        }else{
            if(empty($order_list)){
                $this->error(lang('param_error'));
            }
            
            $daddress_list = $daddress_model->getAddressList(array(), '*', 'daddress_isdefault desc');
            View::assign('daddress_list', $daddress_list);

            foreach($order_list as $key => $order_info){
                //如果是自提订单，只保留自提快递公司
                $express_list = rkcache('express', true);
                if (!empty($order_info['extend_order_common']['reciver_info']['dlyp'])) {
                    foreach ($express_list as $k => $v) {
                        if ($v['express_zt_state'] == '0')
                            unset($express_list[$k]);
                    }
                }
                $order_list[$key]['my_express_list']=array_values($express_list);
            }
            View::assign('order_list', $order_list);
            return View::fetch('batch_send');
        }
        

        
    }

    /**
     * 编辑收货地址
     * @return boolean
     */
    public function buyer_address_edit() {
        $order_id = input('param.order_id');
        if ($order_id <= 0){
            return false;
        }
        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        $order_common_info = $order_model->getOrdercommonInfo($condition);
        if (!$order_common_info){
            return false;
        }
        $order_common_info['reciver_info'] = @unserialize($order_common_info['reciver_info']);
        View::assign('address_info', $order_common_info);
        return View::fetch();
    }

    /**
     * 收货地址保存
     */
    public function buyer_address_save() {
        $order_model = model('order');
        $data = array();
        $data['reciver_name'] = input('post.new_reciver_name');
        $data['reciver_info'] = $this->_get_reciver_info();
        $condition = array();
        $condition[] = array('order_id','=',intval(input('param.order_id')));
        $result = $order_model->editOrdercommon($data, $condition);
        if ($result>=0) {
            dsLayerOpenSuccess('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 组合reciver_info
     */
    private function _get_reciver_info() {
        $reciver_info = array(
            'address' => input('post.reciver_area') . ' ' . input('post.reciver_street'),
            'phone' => trim(input('post.reciver_mob_phone') . ',' . input('post.reciver_tel_phone'), ','),
            'area' => input('post.reciver_area'),
            'street' => input('post.reciver_street'),
            'mob_phone' => input('post.reciver_mob_phone'),
            'tel_phone' => input('post.reciver_tel_phone'),
            'dlyp' => input('post.reciver_dlyp'),
        );
        return serialize($reciver_info);
    }


    /**
     * 选择发货地址
     * @return boolean
     */
    public function send_address_select() {
        $address_list = model('daddress')->getAddressList(array());
        View::assign('address_list', $address_list);
        View::assign('order_id', input('param.order_id'));
        return View::fetch();
    }

    /**
     * 保存发货地址修改
     */
    public function send_address_save() {
        $result = $this->_edit_order_daddress(input('param.daddress_id'), input('param.order_id'));
        if ($result>=0) {
            dsLayerOpenSuccess('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /**
     * 修改发货地址
     */
    private function _edit_order_daddress($daddress_id, $order_id) {
        $order_model = model('order');
        $data = array();
        $data['daddress_id'] = intval($daddress_id);
        $condition = array();
        $condition[] = array('order_id','=',$order_id);
        return $order_model->editOrdercommon($data, $condition);
    }

    /**
     * 物流跟踪
     */
    public function search_deliver() {
        $order_sn = input('param.order_sn');
        if (!is_numeric($order_sn)) {
            $this->error(lang('param_error'));
        }

        $order_model = model('order');
        $condition = array();
        $condition[] = array('order_sn','=',$order_sn);
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || $order_info['shipping_code'] == '') {
            $this->error(lang('no_information_found'));
        }
        $order_info['state_info'] = get_order_state($order_info);
        View::assign('order_info', $order_info);
        //卖家发货信息
        $daddress_info = model('daddress')->getAddressInfo(array('daddress_id' => $order_info['extend_order_common']['daddress_id']));
        View::assign('daddress_info', $daddress_info);

        //取得配送公司代码
        $express = rkcache('express', true);
        View::assign('express_code', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_code']:'');
        View::assign('express_name', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_name']:'');
        View::assign('express_url', isset($express[$order_info['extend_order_common']['shipping_express_id']])?$express[$order_info['extend_order_common']['shipping_express_id']]['express_url']:'');
        View::assign('shipping_code', $order_info['shipping_code']);
        
        $this->setAdminCurItem('search_deliver');
        return View::fetch('search_deliver');
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function get_express() {
        $result = model('express')->queryExpress(input('param.express_code'),input('param.shipping_code'),input('param.phone'));
        if ($result['Success'] != true)
            exit(json_encode(false));
        $content['Traces'] = array_reverse($result['Traces']);
        $output = '';
        if (is_array($content['Traces'])) {
            foreach ($content['Traces'] as $k => $v) {
                if ($v['AcceptTime'] == '')
                    continue;
                $output .= '<li>' . $v['AcceptTime'] . '&nbsp;&nbsp;' . $v['AcceptStation'] . '</li>';
            }
        }
        if ($output == '')
            exit(json_encode(false));
        echo json_encode($output);
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$name	当前导航的name
     * @return
     */
    protected function getAdminItemList()
    {
        $menu_array = array();
        $menu_type=request()->action();
        switch ($menu_type) {
            case 'index':
                $menu_array = array(
                    array('name' => 'deliverno', 'text' => lang('ds_member_path_deliverno'), 'url' => url('Deliver/index',['state'=>'deliverno'])),
                    array('name' => 'delivering', 'text' => lang('ds_member_path_delivering'),  'url' => url('Deliver/index',['state'=>'delivering'])),
                    array('name' => 'delivered', 'text' => lang('ds_member_path_delivered'), 'url' => url('Deliver/index',['state'=>'delivered'])),
                );
                break;
            case 'search':
                $menu_array = array(
                     array('name' => 'nodeliver', 'text' => lang('ds_member_path_deliverno'), 'url' => url('Deliver/index/state/nodeliver')),
                     array('name' => 'delivering', 'text' => lang('ds_member_path_delivering'), 'url' => url('Deliver/index/state/delivering')),
                     array('name' => 'delivered', 'text' => lang('ds_member_path_delivered'), 'url' => url('Deliver/index/state/delivered')),
                     array('name' => 'search', 'text' => lang('ds_member_path_deliver_info'), 'url' => '###'),
                );
                break;
        }
        return $menu_array;
    }

}
