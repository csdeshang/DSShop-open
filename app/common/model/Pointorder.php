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
class Pointorder extends BaseModel {

    public $page_info;

    /**
     * 通过状态标识获取兑换订单状态
     * @access public
     * @author csdeshang
     * @return int
     */
    public function getPointorderStateBySign() {
        $pointorderstate_arr = array();
        $pointorderstate_arr['canceled'] = array(
            2,
            '已取消'
        );
        $pointorderstate_arr['waitship'] = array(
            20,
            '待发货'
        );
        $pointorderstate_arr['waitreceiving'] = array(
            30,
            '待收货'
        );
        $pointorderstate_arr['finished'] = array(
            40,
            '已完成'
        );
        return $pointorderstate_arr;
    }

    /**
     * 通过状态值获取兑换订单状态
     * @access public
     * @author csdeshang
     * @param type $order_state 订单状态
     * @return string
     */
    public function getPointorderState($order_state) {
        $pointorderstate_arr = array();
        $pointorderstate_arr[2] = array(
            'canceled',
            '已取消'
        );
        $pointorderstate_arr[20] = array(
            'waitship',
            '待发货'
        );
        $pointorderstate_arr[30] = array(
            'waitreceiving',
            '待收货'
        );
        $pointorderstate_arr[40] = array(
            'finished',
            '已完成'
        );
        if ($pointorderstate_arr[$order_state]) {
            return $pointorderstate_arr[$order_state];
        } else {
            return array(
                'unknown',
                '未知'
            );
        }
    }

    /**
     * 新增兑换礼品订单
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addPointorder($data) {
        if (!$data) {
            return false;
        }
        $result = Db::name('pointsorder')->insertGetId($data);
        //清除相关缓存
        if ($result && $data['point_buyerid'] > 0) {
            wcache($data['point_buyerid'], array('pointordercount' => null), 'm_pointorder');
        }
        return $result;
    }

    /**
     * 批量添加订单积分礼品
     * @access public
     * @author csdeshang
     * @param array $data 订单礼品信息
     * @return bool
     */
    public function addPointorderProdAll($data) {
        if (!$data) {
            return false;
        }
        $result = Db::name('pointsordergoods')->insertAll($data);
        return $result;
    }

    /**
     * 查询兑换订单礼品列表
     * @access public
     * @author csdeshang
     * @param type $where 查询条件
     * @param type $field 查询字段
     * @return type
     */
    public function getPointordergoodsList($where, $field = '*') {
        $ordergoods_list = Db::name('pointsordergoods')->field($field)->where($where)->select()->toArray();
        if ($ordergoods_list) {
            foreach ($ordergoods_list as $k => $v) {
                $v['point_goodsimage_old'] = $v['pointog_goodsimage'];
                $v['point_goodsimage_small'] = pointprod_thumb($v['pointog_goodsimage'], 'small');
                $v['pointog_goodsimage'] = pointprod_thumb($v['pointog_goodsimage'], 'mid');
                $ordergoods_list[$k] = $v;
            }
        }
        return $ordergoods_list;
    }

    /**
     * 删除兑换订单信息
     * @access public
     * @author csdeshang
     * @param array $where 查询条件
     * @return bool
     */
    public function delPointorder($where) {
        return Db::name('pointsorder')->where($where)->delete();
    }

    /**
     * 删除兑换订单礼品信息
     * @access public
     * @author csdeshang
     * @param array $where 查询条件
     * @return bool
     */
    public function delPointordergoods($where) {
        return Db::name('pointsordergoods')->where($where)->delete();
    }

    /**
     * 删除兑换订单地址信息
     * @access public
     * @author csdeshang
     * @param array $where 查询条件
     * @return bool
     */
    public function delPointorderAddress($where) {
        return Db::name('pointsorderaddress')->where($where)->delete();
    }

    /**
     * 添加积分兑换订单地址
     * @access public
     * @author csdeshang
     * @param array $data 订单收货地址信息
     * @return bool
     */
    public function addPointorderAddress($data) {
        if (!$data) {
            return false;
        }
        return Db::name('pointsorderaddress')->insertGetId($data);
    }

    /**
     * 查询兑换订单详情
     * @access public
     * @author csdeshang
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $order 排序
     * @param string $group 分组
     * @return array
     */
    public function getPointorderInfo($where, $field = '*', $order = '', $group = '') {
        $order_info = Db::name('pointsorder')->field($field)->where($where)->group($group)->order($order)->find();
        if(!$order_info){
            return $order_info;
        }
        $point_orderstate_arr = $this->getPointorderState($order_info['point_orderstate']);
        $order_info['point_orderstatetext'] = $point_orderstate_arr[1];
        $order_info['point_orderstatesign'] = $point_orderstate_arr[0];
        //是否可以发货
        $tmp = $this->allowPointorderShip($order_info);
        $order_info['point_orderallowship'] = $tmp['state'];
        unset($tmp);
        //是否可以修改发货信息
        $tmp = $this->allowEditPointorderShip($order_info);
        $order_info['point_orderalloweditship'] = $tmp['state'];
        unset($tmp);
        //是否允许取消
        $tmp = $this->allowPointorderCancel($order_info);
        $order_info['point_orderallowcancel'] = $tmp['state'];
        unset($tmp);
        //是否允许删除
        $tmp = $this->allowPointorderDelete($order_info);
        $order_info['point_orderallowdelete'] = $tmp['state'];
        unset($tmp);
        //允许确认收货
        $tmp = $this->allowPointorderReceiving($order_info);
        $order_info['point_orderallowreceiving'] = $tmp['state'];
        unset($tmp);
        return $order_info;
    }

    /**
     * 查询兑换订单收货人地址信息
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @param type $group 分组
     * @return type
     */
    public function getPointorderAddressInfo($where, $field = '*', $order = '', $group = '') {
        return Db::name('pointsorderaddress')->field($field)->where($where)->group($group)->order($order)->find();
    }

    /**
     * 查询兑换订单列表
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @param str $field 字段
     * @param int $pagesize  分页
     * @param int $limit 限制
     * @param str $order 排序
     * @return array
     */
    public function getPointorderList($where, $field = '*', $pagesize = 0, $limit = 0, $order = '') {
        
        if($pagesize){
            $order_list = Db::name('pointsorder')->field($field)->where($where)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $order_list;
            $order_list = $order_list->items();
        }else{
            $order_list = Db::name('pointsorder')->field($field)->where($where)->limit($limit)->order($order)->select()->toArray();
        }
        
        
        foreach ($order_list as $k => $v) {
            //订单状态
            $point_orderstate_arr = $this->getPointorderState($v['point_orderstate']);
            $v['point_orderstatetext'] = $point_orderstate_arr[1];
            $v['point_orderstatesign'] = $point_orderstate_arr[0];
            //是否可以发货
            $tmp = $this->allowPointorderShip($v);
            $v['point_orderallowship'] = $tmp['state'];
            unset($tmp);
            //是否可以修改发货信息
            $tmp = $this->allowEditPointorderShip($v);
            $v['point_orderalloweditship'] = $tmp['state'];
            unset($tmp);
            //是否允许取消
            $tmp = $this->allowPointorderCancel($v);
            $v['point_orderallowcancel'] = $tmp['state'];
            unset($tmp);
            //是否允许删除
            $tmp = $this->allowPointorderDelete($v);
            $v['point_orderallowdelete'] = $tmp['state'];
            unset($tmp);
            //允许确认收货
            $tmp = $this->allowPointorderReceiving($v);
            $v['point_orderallowreceiving'] = $tmp['state'];
            unset($tmp);
            $order_list[$k] = $v;
        }
        return $order_list;
    }

    /**
     * 是否可以发货
     * @access public
     * @author csdeshang
     * @param array $pointorder_info 兑换订单详情
     * @return array
     */
    public function allowPointorderShip($pointorder_info) {
        if (!$pointorder_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        $pointorderstate_arr = $this->getPointorderStateBySign();
        if ($pointorder_info['point_orderstate'] == $pointorderstate_arr['waitship'][0]) {
            return array('state' => true);
        } else {
            return array('state' => false);
        }
    }

    /**
     * 是否可以修改发货信息
     * @access public
     * @author csdeshang
     * @param array $pointorder_info 兑换订单详情
     * @return bool
     */
    public function allowEditPointorderShip($pointorder_info) {
        if (!$pointorder_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        $pointorderstate_arr = $this->getPointorderStateBySign();
        if ($pointorder_info['point_orderstate'] == $pointorderstate_arr['waitreceiving'][0]) {
            return array('state' => true);
        } else {
            return array('state' => false);
        }
    }

    /**
     * 是否可以确认收货
     * @access public
     * @author csdeshang
     * @param array $pointorder_info 兑换订单详情
     * @return bool
     */
    public function allowPointorderReceiving($pointorder_info) {
        if (!$pointorder_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        $pointorderstate_arr = $this->getPointorderStateBySign();
        if ($pointorder_info['point_orderstate'] == $pointorderstate_arr['waitreceiving'][0]) {
            return array('state' => true);
        } else {
            return array('state' => false);
        }
    }

    /**
     * 是否可以发货
     * @access public
     * @author csdeshang
     * @param array $pointorder_info 兑换订单详情
     * @return bool
     */
    public function allowPointorderCancel($pointorder_info) {
        if (!$pointorder_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        $pointorderstate_arr = $this->getPointorderStateBySign();
        if ($pointorder_info['point_orderstate'] == $pointorderstate_arr['waitship'][0]) {
            return array('state' => true);
        } else {
            return array('state' => false);
        }
    }

    /**
     * 是否可以发货
     * @access public
     * @author csdeshang
     * @param array $pointorder_info 兑换订单详情
     * @return array
     */
    public function allowPointorderDelete($pointorder_info) {
        if (!$pointorder_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        $pointorderstate_arr = $this->getPointorderStateBySign();
        if ($pointorder_info['point_orderstate'] == $pointorderstate_arr['canceled'][0]) {
            return array('state' => true);
        } else {
            return array('state' => false);
        }
    }

    /**
     * 积分礼品兑换订单信息修改
     * @access public
     * @author csdeshang
     * @param  array $data 修改信息数组
     * @param  array $condition 条件
     * @return bool 
     */
    public function editPointorder($condition, $data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('pointsorder')->where($condition)->update($data);
    }

    /**
     * 取消兑换商品订单
     * @access public
     * @author csdeshang
     * @param type $point_orderid 积分订单ID
     * @param type $member_id 会员ID
     * @return type
     */
    public function cancelPointorder($point_orderid, $member_id = 0) {
        $point_orderid = intval($point_orderid);
        if ($point_orderid <= 0) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }

        //获取订单状态
        $pointorderstate_arr = $this->getPointorderStateBySign();

        $where = array();
        $where[] = array('point_orderid','=',$point_orderid);
        if ($member_id > 0) {
            $where[] = array('point_buyerid','=',$member_id);
        }
        $where[] = array('point_orderstate','=',$pointorderstate_arr['waitship'][0]); //未发货时可取消
        //查询兑换信息
        $order_info = $this->getPointorderInfo($where, 'point_orderid,point_ordersn,point_buyerid,point_buyername,point_allpoint,point_orderstate');
        if (!$order_info) {
            return array(
                'state' => false,
                'msg' => '兑换订单信息错误'
            );
        }
        $result = $this->editPointorder(array(
            'point_orderid' => $order_info['point_orderid'],
            'point_buyerid' => $order_info['point_buyerid']
                ), array('point_orderstate' => $pointorderstate_arr['canceled'][0]));
        if ($result) {

            //更新会员缓存
            wcache($order_info['point_buyerid'], array('pointordercount' => null), 'm_pointorder');

            //退还会员积分
            $insert_arr = array();
            $insert_arr['pl_memberid'] = $order_info['point_buyerid'];
            $insert_arr['pl_membername'] = $order_info['point_buyername'];
            $insert_arr['pl_points'] = $order_info['point_allpoint'];
            $insert_arr['point_ordersn'] = $order_info['point_ordersn'];
            $insert_arr['pl_desc'] = '取消兑换礼品信息' . $order_info['point_ordersn'] . '增加积分';
            model('points')->savePointslog('pointorder', $insert_arr, true);

            //更改兑换礼品库存
            $prod_list = $this->getPointorderAndGoodsList(array('pointsordergoods.pointog_orderid' => $order_info['point_orderid']), 'pointog_goodsid,pointog_goodsnum');
            if ($prod_list) {
                $pointprod_model = model('pointprod');
                foreach ($prod_list as $v) {
                    $update_arr = array();
                    $update_arr['pgoods_storage'] = Db::raw('pgoods_storage+'.$v['pointog_goodsnum']);
                    $update_arr['pgoods_salenum'] = Db::raw('pgoods_salenum-'.$v['pointog_goodsnum']);

                    $pointprod_model->editPointProd($update_arr, array('pgoods_id' => $v['pointog_goodsid']));
                    unset($update_arr);
                }
            }
            return array(
                'state' => true,
                'data' => array('order_info' => $order_info)
            );
        } else {
            return array(
                'state' => true,
                'msg' => '取消失败'
            );
        }
    }

    /**
     * 兑换订单确认收货
     * @access public
     * @author csdeshang
     * @param type $point_orderid 积分订单ID
     * @param type $member_id 会员ID
     * @return type
     */
    public function receivingPointorder($point_orderid, $member_id = 0) {
        $point_orderid = intval($point_orderid);
        if ($point_orderid <= 0) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }

        //获取订单状态
        $pointorderstate_arr = $this->getPointorderStateBySign();

        $where = array();
        $where[] = array('point_orderid','=',$point_orderid);
        if ($member_id > 0) {
            $where[] = array('point_buyerid','=',$member_id);
        }
        $where[] = array('point_orderstate','=',$pointorderstate_arr['waitreceiving'][0]); //已发货待收货
        //更新运费
        $result = $this->editPointorder($where, array(
            'point_orderstate' => $pointorderstate_arr['finished'][0],
            'point_finnshedtime' => TIMESTAMP
        ));
        if ($result) {
            return array('state' => true);
        } else {
            return array(
                'state' => true,
                'msg' => '确认收货失败'
            );
        }
    }

    /**
     * 查询兑换订单总数
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getPointorderCount($condition) {
        return Db::name('pointsorder')->where($condition)->count();
    }

    /**
     * 查询积分兑换商品订单及商品列表
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @param string $field 字段
     * @param string $order 排序
     * @return array
     */
    public function getPointorderAndGoodsList($where, $field = '*',  $order = '',$pagesize='') {
        if($pagesize){
            $order_list = Db::name('pointsordergoods')->alias('pointsordergoods')->field($field)->join('pointsorder pointsorder','pointsordergoods.pointog_orderid=pointsorder.point_orderid')->where($where)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $order_list;
            $order_list = $order_list->items();
            return $order_list;
        }else{
        return Db::name('pointsordergoods')->alias('pointsordergoods')->field($field)->join('pointsorder pointsorder','pointsordergoods.pointog_orderid=pointsorder.point_orderid')->where($where)->order($order)->select()->toArray();
        }
    }

    /**
     * 查询积分兑换商品订单及商品详细
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @param str $field 字段
     * @param str $order 排序
     * @param str $group 分组
     * @return array
     */
    public function getPointorderAndGoodsInfo($where, $field = '*', $order = '', $group = '') {
        return Db::name('pointsordergoods')->alias('pointsordergoods')->field($field)->join('pointsorder pointsorder','pointsordergoods.pointog_orderid=pointsorder.point_orderid')->where($where)->group($group)->order($order)->find();
    }

    /**
     * 查询会员已经兑换商品数
     * @access public
     * @author csdeshang
     * @param int $member_id 会员编号
     * @return int
     */
    public function getMemberPointsOrderGoodsCount($member_id) {
        $info = rcache($member_id, 'm_pointorder');
        if (!isset($info['pointordercount']) || $info['pointordercount'] !== 0) {
            //获取兑换订单状态
            $pointorderstate_arr = $this->getPointorderStateBySign();

            $where = array();
            $where[] = array('point_buyerid','=',$member_id);
            $where[] = array('point_orderstate','<>',$pointorderstate_arr['canceled'][0]);
            $list = $this->getPointorderAndGoodsList($where, 'SUM(pointog_goodsnum) as goodsnum');
            $pointordercount = 0;
            if ($list) {
                $pointordercount = intval($list[0]['goodsnum']);
            }
            wcache($member_id, array('pointordercount' => $pointordercount), 'm_pointorder');
        } else {
            $pointordercount = intval($info['pointordercount']);
        }
        return $pointordercount;
    }

    /**
     * 创建积分礼品兑换订单
     * @access public
     * @author csdeshang
     * @param type $post_arr 数据数组
     * @param type $pointprod_arr 积分数组
     * @param type $member_info 会员信息
     * @return type
     */
    public function createOrder($post_arr, $pointprod_arr, $member_info) {
        //验证是否存在收货地址
        $address_options = intval($post_arr['address_options']);
        if ($address_options <= 0) {
            return array(
                'state' => false,
                'msg' => '请选择收货人地址'
            );
        }
        $address_info = model('address')->getOneAddress($address_options);
        if (empty($address_info)) {
            return array(
                'state' => false,
                'msg' => '收货人地址信息错误'
            );
        }

        //获取兑换订单状态
        $pointorderstate_arr = $this->getPointorderStateBySign();
        //新增兑换订单
        $order_array = array();
        $order_array['point_ordersn'] = makePaySn($member_info['member_id']);
        $order_array['point_buyerid'] = $member_info['member_id'];
        $order_array['point_buyername'] = $member_info['member_name'];
        $order_array['point_buyeremail'] = $member_info['member_email'];
        $order_array['point_addtime'] = TIMESTAMP;
        $order_array['point_allpoint'] = $pointprod_arr['pgoods_pointall'];
        $order_array['point_ordermessage'] = isset($post_arr['pcart_message'])?trim($post_arr['pcart_message']):'';
        $order_array['point_orderstate'] = $pointorderstate_arr['waitship'][0];
        $order_id = $this->addPointorder($order_array);
        if (!$order_id) {
            return array(
                'state' => false,
                'msg' => '兑换操作失败'
            );
        }

        //扣除会员积分
        $insert_arr = array();
        $insert_arr['pl_memberid'] = $member_info['member_id'];
        $insert_arr['pl_membername'] = $member_info['member_name'];
        $insert_arr['pl_points'] = -$pointprod_arr['pgoods_pointall'];
        $insert_arr['point_ordersn'] = $order_array['point_ordersn'];
        model('points')->savePointslog('pointorder', $insert_arr, true);

        //添加订单中的礼品信息
        $pointprod_model = model('pointprod');

        if ($pointprod_arr['pointprod_list']) {
            $output_goods_name = array(); //输出显示的对话礼品名称数组
            foreach ($pointprod_arr['pointprod_list'] as $v) {
                $tmp = array();
                $tmp['pointog_orderid'] = $order_id;
                $tmp['pointog_goodsid'] = $v['pgoods_id'];
                $tmp['pointog_goodsname'] = $v['pgoods_name'];
                $tmp['pointog_goodspoints'] = $v['pgoods_points'];
                $tmp['pointog_goodsnum'] = $v['quantity'];
                $tmp['pointog_goodsimage'] = $v['pgoods_image_old'];
                $order_goods_array[] = $tmp;

                //输出显示前3个兑换礼品名称
                if (count($output_goods_name) < 3) {
                    $output_goods_name[] = $v['pgoods_name'];
                }
                unset($tmp);

                //更新积分礼品库存
                $tmp = array();
                $tmp['pgoods_id'] = $v['pgoods_id'];
                $tmp['pgoods_storage'] = Db::raw('pgoods_storage-'.$v['quantity']);
                $tmp['pgoods_salenum'] = Db::raw('pgoods_salenum+'.$v['quantity']);

                $pointprod_uparr[] = $tmp;
                unset($tmp);
            }

            //批量新增兑换订单礼品
            $this->addPointorderProdAll($order_goods_array);
            //更新兑换礼品库存
            foreach ($pointprod_uparr as $v) {
                $pointprod_model->editPointProd($v, array('pgoods_id' => $v['pgoods_id']));
            }
        }

        //清除购物车信息
        model('pointcart')->delPointcart(array('pmember_id' => $member_info['member_id']), $member_info['member_id']);

        //保存买家收货地址,添加订单收货地址
        if ($address_info) {
            $address_array = array();
            $address_array['pointoa_orderid'] = $order_id;
            $address_array['pointoa_truename'] = $address_info['address_realname'];
            $address_array['pointoa_areaid'] = $address_info['area_id'];
            $address_array['pointoa_areainfo'] = $address_info['area_info'];
            $address_array['pointoa_address'] = $address_info['address_detail'];
            $address_array['pointoa_zipcode'] = (isset($address_info['zip_code'])) ? $address_info['zip_code'] : '';
            $address_array['pointoa_telphone'] = $address_info['address_tel_phone'];
            $address_array['pointoa_mobphone'] = $address_info['address_mob_phone'];
            $this->addPointorderAddress($address_array);
        }

        return array(
            'state' => true,
            'data' => array('order_id' => $order_id)
        );
    }

    /**
     * 删除兑换订单
     * @access public
     * @author csdeshang
     * @param type $order_id 订单ID
     * @return type
     */
    public function delPointorderByOrderID($order_id) {
        $order_id = intval($order_id);
        if ($order_id <= 0) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        //获取订单状态
        $pointorderstate_arr = $this->getPointorderStateBySign();

        //删除操作
        $where = array();
        $where[] = array('point_orderid','=',$order_id);
        $where[] = array('point_orderstate','=',$pointorderstate_arr['canceled'][0]); //只有取消的订单才能删除
        $result = $this->delPointorder($where);
        if ($result) {
            //删除兑换礼品信息
            $this->delPointordergoods(array('pointog_orderid' => $order_id));
            //删除兑换地址信息
            $this->delPointorderAddress(array('pointoa_orderid' => $order_id));
            return array('state' => true);
        } else {
            return array(
                'state' => false,
                'msg' => '删除失败'
            );
        }
    }

    /**
     * 兑换订单发货
     * @access public
     * @author csdeshang
     * @param type $order_id 订单id
     * @param type $postarr 数组
     * @param type $order_info 会员
     * @return type
     */ 
    public function shippingPointorder($order_id, $postarr, $order_info = array()) {
        $order_id = intval($order_id);
        if ($order_id <= 0) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        //获取订单状态
        $pointorderstate_arr = $this->getPointorderStateBySign();

        //查询订单信息
        $condition = array();
        $condition[] = array('point_orderid','=',$order_id);
        $condition[] = array('point_orderstate','in',array($pointorderstate_arr['waitship'][0],$pointorderstate_arr['waitreceiving'][0])); 
        //待发货和已经发货状态
        //如果订单详情不存在，则查询
        if (!$order_info) {
            $order_info = $this->getPointorderInfo($condition, 'point_orderstate');
        }
        if (!$order_info) {
            return array(
                'state' => false,
                'msg' => '参数错误'
            );
        }
        //更新发货信息
        $update_arr = array();
        if ($order_info['point_orderstate'] == $pointorderstate_arr['waitship'][0]) {
            $update_arr['point_shippingtime'] = TIMESTAMP;
            $update_arr['point_orderstate'] = $pointorderstate_arr['waitreceiving'][0]; //已经发货,待收货
        }
        $update_arr['point_shippingcode'] = $postarr['shippingcode'];
        $update_arr['point_shipping_ecode'] = $postarr['express_code'];
        $result = $this->editPointorder($condition, $update_arr);
        if ($result) {
            return array('state' => true);
        } else {
            return array(
                'state' => false,
                'msg' => '发货修改失败'
            );
        }
    }

}