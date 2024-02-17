<?php

namespace app\common\model;
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
 * 数据层模型
 */
class Order extends BaseModel {
    public $page_info;
    public $lock=false;//是否加锁
    

    /**
     * 取单条订单信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $extend 扩展
     * @param string $fields 字段
     * @param array $order 排序
     * @param array $group 分组
     * @return array
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '', $group = '') {
        $order_info = Db::name('order')->field($fields)->where($condition)->lock($this->lock)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = get_order_state($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = get_order_payment_name($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_info['extend_order_common'] = $this->getOrdercommonInfo(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }


        //返回买家信息
        if (in_array('member', $extend)) {
            $order_info['extend_member'] = model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = $this->getOrdergoodsList(array('order_id' => $order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }
        return $order_info;
    }
    
    /**
     * 获取订单信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @return array
     */
    public function getOrdercommonInfo($condition = array(), $field = '*') {
        return Db::name('ordercommon')->where($condition)->find();
    }
    
    /**
     * 获取订单信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return type
     */
    public function getOrderpayInfo($condition = array()) {
        return Db::name('orderpay')->where($condition)->find();
    }

    /**
     * 取得支付单列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return array
     */
    public function getOrderpayList($condition,  $field = '*', $order = '', $key = '') {
        $pay_list = Db::name('orderpay')->field($field)->where($condition)->order($order)->select()->toArray();
        if($key){
            $pay_list = ds_change_arraykey($pay_list, $key);
        }
        return $pay_list;
    }

    /**
     * 取得订单列表(未被删除)
     * @access public
     * @author csdeshang
     * @param unknown $condition 条件
     * @param string $pagesize 分页
     * @param string $field 字段
     * @param string $order 排序
     * @param string $limit 限制
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = 0, $extend = array()) {
        $condition[]=array('delete_state','=',0);
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }

    /**
     * 取得订单列表(所有)
     * @access public
     * @author csdeshang
     * @param unknown $condition 条件
     * @param string $pagesize 分页
     * @param string $field 字段
     * @param string $order 排序
     * @param string $limit 限制
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = 0, $extend = array()) {

        if ($pagesize) {
            $list_paginate = Db::name('order')->field($field)->where($condition)->order($order)->paginate(['list_rows' => $pagesize, 'query' => request()->param()], false);
            $this->page_info = $list_paginate;
            $list = $list_paginate->items();
        } else {
            $list = Db::name('order')->field($field)->where($condition)->order($order)->limit($limit)->select()->toArray();
        }


        if (empty($list))
            return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = get_order_state($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = get_order_payment_name($order['payment_code']);
            }
            if (!empty($extend))
                $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list))
            $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common', $extend)) {
            $order_common_list = $this->getOrdercommonList(array(array('order_id','in', array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }

        //追加返回买家信息
        if (in_array('member', $extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }


        //追加返回商品信息
        if (in_array('order_goods', $extend)) {
            //取商品列表
            $order_goods_list = Db::name('ordergoods')->where('order_id', 'in', array_keys($order_list))->select()->toArray();

            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {

                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods']= array();
            }
        }
        
        //追加返回拼团订单信息
        if (in_array('ppintuanorder', $extend)) {
            //取拼团订单附加列表
            $pintuanorder_list = model('ppintuanorder')->getPpintuanorderList(array(array('ppintuanorder.order_id','in', array_keys($order_list)),array('pintuanorder_type','=',0)));
            if (!empty($pintuanorder_list)) {
                foreach ($pintuanorder_list as $value) {
                    $order_list[$value['order_id']]['pintuan_id'] = $value['pintuan_id'];
                    $order_list[$value['order_id']]['pintuangroup_id'] = $value['pintuangroup_id'];
                    $order_list[$value['order_id']]['pintuanorder_state'] = $value['pintuanorder_state'];
                    $order_list[$value['order_id']]['pintuanorder_state_text'] = $value['pintuanorder_state_text'];
                }
            }
        }

        return $order_list;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @access public
     * @author csdeshang
     * @param int $id   买家ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($id, $key) {
        if (!config('ds_config.cache_open')) return ;
        $types = $id.'_ordercount' . $key;
        $count = rkcache($types);
        return $count;
    }

    /**
     * 设置(买/卖家)订单某个数量缓存
     * @access public
     * @author csdeshang
     * @param int $id 买家ID
     * @param int $key 允许传入  NewCount、PayCount、SendCount、EvalCount、TradeCount，分别取相应数量缓存，只许传入一个
     * @param array $count 数据
     * @return type
     */
    public function editOrderCountCache($id, $key, $count) {
        if (!config('ds_config.cache_open') || !intval($id))
            return;
        $types = $id.'_ordercount_' . $key;
        wkcache($types, $count);
    }

    /**
     * 取得买卖家订单数量某个缓存
     * @access public
     * @author csdeshang
     * @param int $id 买家ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($id, $key) {
        $cache_info = $this->getOrderCountCache($id, $key);
        if (config('ds_config.cache_open') && is_numeric($cache_info)) {
            //从缓存中取得
            $count = $cache_info;
        } else {
            //从数据库中取得
            $condition = array();
            $condition[] = array('buyer_id' , '=' ,$id);
            $func = 'getOrderState' . $key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($id, $key, $count);
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @access public
     * @author csdeshang
     * @param string $type 买/卖家标志，允许传入 buyer
     * @param int $id   买家ID
     * @return bool
     */
    public function delOrderCountCache($type) {
        $type_NewCount = $type.'_ordercount' .'_NewCount';
        $type_PayCount = $type.'_ordercount' .'_PayCount';
        $type_SendCount = $type.'_ordercount' .'_SendCount';
        $type_EvalCount = $type.'_ordercount' .'_EvalCount';
        $type_TradeCount = $type.'_ordercount' .'_TradeCount';
        dcache($type_NewCount);
        dcache($type_PayCount);
        dcache($type_SendCount);
        dcache($type_EvalCount);
        dcache($type_TradeCount);
    }

    /**
     * 待付款订单数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition[]=array('order_state','=',ORDER_STATE_NEW);
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition[]=array('order_state','=',ORDER_STATE_PAY);
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition[]=array('order_state','=',ORDER_STATE_SEND);
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @access public
     * @author csdeshang
     * @param type $condition 检索条件
     * @return type
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition[]=array('order_state','=',ORDER_STATE_SUCCESS);
        $condition[]=array('evaluation_state','=',0);
		$condition[]=array('refund_state','=',0);
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getOrderStateTradeCount($condition = array()) {
        $condition[] = array(array('order_state','not in', array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)));
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getOrderCount($condition) {
        return Db::name('order')->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件  
     * @param string $fields 字段  
     * @param string $order 排序
     * @return array
     */
    public function getOrdergoodsInfo($condition = array(), $fields = '*', $order = '') {
        return Db::name('ordergoods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $fields 字段
     * @param type $limit 限制
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $group 分组
     * @param type $key 键
     * @return array
     */
    public function getOrdergoodsList($condition = array(), $fields = '*', $limit = 0, $pagesize = null, $order = 'rec_id desc', $group = null, $key = null) {
        if ($pagesize) {
            $res= Db::name('ordergoods')->field($fields)->where($condition)->order($order)->group($group)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            $ordergoods = $res->items();
            if(!empty($key)){
                $ordergoods = ds_change_arraykey($ordergoods, $key);
            }
            return $ordergoods;
        } else {
            $ordergoods = Db::name('ordergoods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->select()->toArray();
            if(!empty($key)){
                $ordergoods = ds_change_arraykey($ordergoods, $key);
            }
            return $ordergoods;
        }
    }

    /**
     * 取得订单扩展表列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $fields 字段
     * @param string $order 排序
     * @param int $limit 限制
     * @return array
     */
    public function getOrdercommonList($condition = array(), $fields = '*', $order = '', $limit = 0) {
        return Db::name('ordercommon')->field($fields)->where($condition)->order($order)->limit($limit)->select()->toArray();
    }

    /**
     * 插入订单支付表信息
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return int 返回 insert_id
     */
    public function addOrderpay($data) {
        return Db::name('orderpay')->insertGetId($data);
    }

    /**
     * 插入订单表信息
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $result = Db::name('order')->insertGetId($data);
        if ($result) {
            //更新缓存
            model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'delOrderCountCache','cron_value'=>serialize(array('buyer_id'=>$data['buyer_id']))));
        }
        return $result;
    }

    /**
     * 插入订单扩展表信息
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return int 返回 insert_id
     */
    public function addOrdercommon($data) {
        return Db::name('ordercommon')->insertGetId($data);
    }

    /**
     * 插入订单扩展表信息
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return int 返回 insert_id
     */
    public function addOrdergoods($data) {
        return Db::name('ordergoods')->insertAll($data);
    }

    /**
     * 添加订单日志
     * @access public
     * @author csdeshang
     * @param type $data 数据信息
     * @return type
     */
    public function addOrderlog($data) {
        $data['log_role'] = str_replace(array('buyer', 'system', 'admin'), array('买家', '系统', '管理员'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return Db::name('orderlog')->insertGetId($data);
    }

    /**
     * 更改订单信息
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @param array $condition 条件
     * @param int $limit 限制
     * @return bool
     */
    public function editOrder($data, $condition, $limit = 0) {
        $update = Db::name('order')->where($condition)->limit($limit)->update($data);
        if ($update) {
            //更新缓存
            $order_list = Db::name('order')->where($condition)->select()->toArray();
            foreach ($order_list as $key => $order) {
                model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'delOrderCountCache','cron_value'=>serialize(array('buyer_id'=>$order['buyer_id']))));
            }
        }
        return $update;
    }

    /**
     * 更改订单信息
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @param array $condition 条件
     * @return bool
     */
    public function editOrdercommon($data, $condition) {
        return Db::name('ordercommon')->where($condition)->update($data);
    }

    /**
     * 更改订单信息
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrdergoods($data, $condition) {
        return Db::name('ordergoods')->where($condition)->update($data);
    }

    /**
     * 更改订单支付信息
     * @access public
     * @author csdeshang
     * @param type $data 数据 
     * @param type $condition 条件
     * @return type
     */
    public function editOrderpay($data, $condition) {
        return Db::name('orderpay')->where($condition)->update($data);
    }

    /**
     * 订单操作历史列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return Ambigous <multitype:, unknown>
     */
    public function getOrderlogList($condition) {
        return Db::name('orderlog')->where($condition)->select()->toArray();
    }

    /**
     * 取得单条订单操作记录
     * @access public
     * @author csdeshang
     * @param array $condition 条件     
     * @param string $order 排序
     * @return array
     */
    public function getOrderlogInfo($condition = array(), $order = '') {
        return Db::name('orderlog')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @access public
     * @author csdeshang
     * @param type $operate 操作
     * @param type $order_info 订单信息
     * @return boolean
     */
    public function getOrderOperateState($operate, $order_info) {
        if (!is_array($order_info) || empty($order_info))
            return false;

        switch ($operate) {

            //买家取消订单
            case 'buyer_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                break;

            //申请退款
            case 'refund_cancel':
                if (isset($order_info['refund'])) {
                    $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
                } else {
                    $state = FALSE;
                }
                break;


            //平台取消订单
            case 'system_cancel':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
                        ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                break;

            //平台收款
            case 'system_receive_pay':
                $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
                break;

            case 'payment':
                $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
                break;

            //调整运费
            case 'modify_price':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) || ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                $state = floatval($order_info['shipping_fee']) > 0 && $state;
                break;
            //调整商品价格
            case 'spay_price':
                $state = ($order_info['order_state'] == ORDER_STATE_NEW) || ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
                $state = floatval($order_info['goods_amount']) > 0 && $state;
                break;

            //发货
            case 'send':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY;
                break;

            //收货
            case 'receive':
                $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
                break;

            //评价
            case 'evaluation':
                $state = !$order_info['refund_state'] && !$order_info['lock_state'] && !$order_info['evaluation_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
                break;

            //锁定
            case 'lock':
                $state = intval($order_info['lock_state']) ? true : false;
                break;

            //快递跟踪
            case 'deliver':
                $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
                break;

            //放入回收站
            case 'delete':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
                break;

            //永久删除、从回收站还原
            case 'drop':
            case 'restore':
                $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL, ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
                break;

            //分享
            case 'share':
                $state = true;
                break;
        }
        return $state;
    }

    /**
     * 联查订单表订单商品表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 站点
     * @param number $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $pagesize = 0, $order = 'rec_id desc') {
        if($pagesize){
            $list = Db::name('ordergoods')->alias('order_goods')->where($condition)->field($field)->join('order order','order_goods.order_id=order.order_id','LEFT')->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $list;
            return $list->items();
        }else{
            $list = Db::name('ordergoods')->alias('order_goods')->where($condition)->field($field)->join('order order','order_goods.order_id=order.order_id','LEFT')->select()->toArray();
            return $list;
        }
    }

    /**
     * 订单销售记录 订单状态为20、30、40时
     * @access public
     * @author csdeshang
     * @param unknown $condition  条件  
     * @param string $field 字段
     * @param number $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field = "*", $pagesize = 0, $order = 'rec_id desc') {
        $condition[] = array('order_state','in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        return $this->getOrderAndOrderGoodsList($condition, $field, $pagesize, $order);
    }

}