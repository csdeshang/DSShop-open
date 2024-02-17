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
class Pointcart extends BaseModel
{

    /**
     * 礼品购物车保存
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return boolean
     */
    public function addPointcart($data)
    {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('pointscart')->insertGetId($data);
        if ($result) {
            //清除相关缓存
            wcache($data['pmember_id'], array('pointcart_count' => null), 'm_pointcart');
            return $result;
        }
        else {
            return false;
        }
    }

    /**
     * 兑换礼品购物车列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @return type 
     */
    public function getPointcartList($where, $field = '*')
    {
        $cartgoods_list = Db::name('pointscart')->field($field)->where($where)->select()->toArray();
        if ($cartgoods_list) {
            foreach ($cartgoods_list as $k => $v) {
                $v['pgoods_image_old'] = $v['pgoods_image'];
                $v['pgoods_image_max'] = pointprod_thumb($v['pgoods_image']);
                $v['pgoods_image_small'] = pointprod_thumb($v['pgoods_image'], 'small');
                $v['pgoods_image'] = pointprod_thumb($v['pgoods_image'], 'normal');
                $cartgoods_list[$k] = $v;
            }
        }
        return $cartgoods_list;
    }
    
    /**
     * 查询兑换礼品购物车列表并计算总积分
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $pagesize  分页
     * @param type $limit 限制
     * @param type $order 排序
     * @param type $group 分组
     * @return type
     */
    public function getPCartListAndAmount($where, $field = '*', $pagesize = 0, $limit = 0, $order = '', $group = '')
    {
        $cartgoods_list = $this->getPointcartList($where);
        //兑换礼品总积分
        $cartgoods_pointall = 0;
        if (!empty($cartgoods_list) && is_array($cartgoods_list)) {
            foreach ($cartgoods_list as $k => $v) {
                $pointsgoods_info=Db::name('pointsgoods')->where('pgoods_id',$v['pgoods_id'])->find();
                $v['pgoods_storage'] = $pointsgoods_info['pgoods_storage'];
                $v['pgoods_pointone'] = intval($v['pgoods_points']) * intval($v['pgoods_choosenum']);
                $cartgoods_list[$k] = $v;
                $cartgoods_pointall = $cartgoods_pointall + $v['pgoods_pointone'];
            }
        }
        return array(
            'state' => true,
            'data' => array('cartgoods_list' => $cartgoods_list, 'cartgoods_pointall' => $cartgoods_pointall)
        );
    }

    /**
     * 礼品购物车信息单条
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @return type
     */
    public function getPointcartInfo($where, $field = '*', $order = '')
    {
        return Db::name('pointscart')->field($field)->where($where)->order($order)->find();
    }

    /**
     * 礼品购物车礼品数量
     * @access public
     * @author csdeshang
     * @param type $member_id 会员id
     * @return type
     */
    public function getPointcartCount($member_id)
    {
        $info = rcache($member_id, 'm_pointcart');
        if (!isset($info['pointcart_count']) || $info['pointcart_count'] == 0) {
            $pointcart_count = Db::name('pointscart')->where(array('pmember_id' => $member_id))->count();
            $pointcart_count = intval($pointcart_count);
            wcache($member_id, array('pointcart_count' => $pointcart_count), 'm_pointcart');
        }
        else {
            $pointcart_count = intval($info['pointcart_count']);
        }
        return $pointcart_count;
    }

    /**
     * 删除礼品购物车信息按照购物车编号
     * @access public
     * @author csdeshang
     * @param type $pc_id 礼品ID
     * @param type $member_id 会员ID
     * @return boolean
     */
    public function delPointcartById($pc_id, $member_id = 0)
    {
        if (empty($pc_id)) {
            return false;
        }
        $where = array();
        if (is_array($pc_id)) {
            $where[]=array('pcart_id','in', $pc_id);
        }
        else {
            $where[]=array('pcart_id','=',$pc_id);
        }
        $result = Db::name('pointscart')->where($where)->delete();
        //清除相关缓存
        if ($result && $member_id > 0) {
            wcache($member_id, array('pointcart_count' => null), 'm_pointcart');
        }
        return $result;
    }

    /**
     * 删除特定条件礼品购物车信息
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $member_id 会员id
     * @return type
     */
    public function delPointcart($where, $member_id = 0)
    {
        $result = Db::name('pointscart')->where($where)->delete();
        //清除相关缓存
        if ($result && $member_id > 0) {
            wcache($member_id, array('pointcart_count' => null), 'm_pointcart');
        }
        return $result;
    }

    /**
     * 积分礼品购物车信息修改
     * @access public
     * @author csdeshang
     * @param type $condition 条件数组
     * @param type $data 修改信息数组
     * @return boolean
     */
    public function editPointcart($condition, $data)
    {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('pointscart')->where($condition)->update($data);
        return $result;
    }

    /**
     * 验证是否能兑换
     * @param type $pgoods_id 礼品ID
     * @param type $quantity 数量
     * @param type $member_id 会员ID 
     * @return type
     */
    public function checkExchange($pgoods_id, $quantity, $member_id)
    {
        $pgoods_id = intval($pgoods_id);
        $quantity = intval($quantity);
        if ($pgoods_id <= 0 || $quantity <= 0) {
            return array('state' => false, 'error' => 'ParameterError', 'msg' => '参数错误');
        }
        $pointprod_model = model('pointprod');
        //获取兑换商品的展示状态
        $pgoodsshowstate_arr = $pointprod_model->getPgoodsShowState();
        //获取兑换商品的开启状态
        $pgoodsopenstate_arr = $pointprod_model->getPgoodsOpenState();
        //验证积分礼品是否存在
        $prod_info = $pointprod_model->getPointProdInfo(array(
                                                            'pgoods_id' => $pgoods_id,
                                                            'pgoods_show' => $pgoodsshowstate_arr['show'][0],
                                                            'pgoods_state' => $pgoodsopenstate_arr['open'][0]
                                                        ));
        if (!$prod_info) {
            return array('state' => false, 'error' => 'ParameterError', 'msg' => '兑换礼品信息不存在');
        }

        //验证积分礼品兑换状态
        $ex_state = $pointprod_model->getPointProdExstate($prod_info);
        switch ($ex_state) {
            case 'willbe':
                return array('state' => false, 'error' => 'StateError', 'msg' => '该兑换礼品的兑换活动即将开始');
                break;
            case 'end':
                return array('state' => false, 'error' => 'StateError', 'msg' => '该兑换礼品的兑换活动已经结束');
                break;
        }

        //查询会员信息
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID($member_id);

        //验证是否满足会员级别
        $member_info['member_grade'] = $member_model->getOneMemberGrade($member_info['member_exppoints']);
        if ($prod_info['pgoods_limitmgrade'] > 0 && $member_info['member_grade']['level'] < $prod_info['pgoods_limitmgrade']) {
            return array('state' => false, 'error' => 'MemberGradeError', 'msg' => '您还未达到兑换所需的会员级别，不能进行兑换');
        }

        //验证兑换数量是否合法
        $data = $this->checkPointProdExnum($prod_info, $quantity, $member_id);
        if (!$data['state']) {
            return array('state' => false, 'error' => 'StateError', 'msg' => $data['msg']);
        }
        $prod_info['quantity'] = $quantity;
        //计算消耗积分总数
        $prod_info['pointsamount'] = intval($prod_info['pgoods_points']) * intval($quantity);

        //验证积分数是否足够
        $data = $this->checkPointEnough($prod_info['pointsamount'], $member_id, $member_info);
        if (!$data['state']) {
            return array('state' => false, 'error' => 'PointsShortof', 'msg' => $data['msg']);
        }
        return array('state' => true, 'data' => array('prod_info' => $prod_info));
    }

    /**
     * 验证礼品兑换数量是否合法
     * @access public
     * @author csdeshang
     * @param array $prodinfo 礼品数组
     * @param int $quantity 兑换数量
     * @param array $member_id 会员编号
     * @return array 兑换数量是否合法及其错误数组
     */
    public function checkPointProdExnum($prodinfo, $quantity, $member_id)
    {
        $data = $this->getPointProdExnum($prodinfo, $quantity, $member_id);
        if (!$data['state']) {
            return array('state' => false, 'msg' => $data['msg']);
        }
        //如果兑换数量大于可兑换数量则提示错误
        if ($data['data']['quantity'] < $quantity) {
            return array('state' => false, 'msg' => "兑换礼品数量不能大于{$data['data']['quantity']}");
        }
        return array('state' => true);
    }

    /**
     * 获得礼品可兑换数量
     * @access public
     * @author csdeshang
     * @param array $prodinfo 礼品数组
     * @param int $quantity 兑换数量
     * @param array $member_id 会员编号
     * @return array 兑换数量及其错误数组
     */
    public function getPointProdExnum($prodinfo, $quantity, $member_id)
    {
        if ($quantity <= 0) {
            return array('state' => false, 'msg' => '兑换数量错误');
        }
        if ($prodinfo['pgoods_storage'] <= 0) {
            return array('state' => false, 'msg' => '该礼品已兑换完');
        }
        if ($prodinfo['pgoods_storage'] < $quantity) {
            //如果兑换数量大于库存，则兑换数量为库存数量
            $quantity = $prodinfo['pgoods_storage'];
        }
        if ($prodinfo['pgoods_islimit'] == 1 && $prodinfo['pgoods_limitnum'] < $quantity) {
            //如果兑换数量大于限兑数量，则兑换数量为限兑数量
            $quantity = $prodinfo['pgoods_limitnum'];
        }
        //查询已兑换数量，并获得剩余可兑换数量
        if ($prodinfo['pgoods_islimit'] == 1) {
            $pointorder_model = model('pointorder');
            //获取兑换订单状态
            $pointorderstate_arr = $pointorder_model->getPointorderStateBySign();
            $where = array();
            $where[]=array('pointog_goodsid','=',$prodinfo['pgoods_id']);
            $where[]=array('point_buyerid','=',$member_id);
            $where[]=array('point_orderstate','<>', $pointorderstate_arr['canceled'][0]);//未取消
            $pordergoodsinfo = $pointorder_model->getPointorderAndGoodsInfo($where, "SUM(pointog_goodsnum) as exnum", '', 'pointog_goodsid');
            if ($pordergoodsinfo) {
                $ablenum = $prodinfo['pgoods_limitnum'] - intval($pordergoodsinfo['exnum']);
                if ($ablenum <= 0) {
                    return array('state' => false, 'msg' => '您已达到该礼品的最大兑换数，不能再兑换，请兑换其他礼品');
                }
                if ($ablenum < $quantity) {
                    $quantity = $ablenum;
                }
            }
        }
        return array('state' => true, 'data' => array('quantity' => $quantity));
    }

    /**
     * 获得某会员购物车礼品总积分
     * @access public
     * @author csdeshang
     * @param type $member_id 会员ID
     * @return type
     */
    public function getPointcartAmount($member_id)
    {
        if ($member_id <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        $info = $this->getPointcartInfo(array('pmember_id' => $member_id), 'SUM(pgoods_points*pgoods_choosenum) as amount');
        $amount = intval($info['amount']);
        return $amount;
    }

    /**
     * 获得符合条件的购物车商品列表同时计算运费、总积分数等信息，用于下单过程
     * @access public
     * @author csdeshang
     * @param type $member_id 会员id
     * @return type
     */
    public function getCartGoodsList($member_id,$post=array())
    {
        $return_array = array();
        //获取礼品购物车内信息
        if(!empty($post) && isset($post['ifcart']) && !$post['ifcart'] && isset($post['cart_id'])){
            $temp= explode('|', $post['cart_id']);
            
            $data=$this->checkExchange($temp[0], $temp[1], $member_id);
            if (!$data['state']) {
                return array('state' => false, 'msg' => $data['msg']);
            }
            $prod_info = $data['data']['prod_info'];
            
            $temp = array();
            $temp['pcart_id']=0;
            $temp['pmember_id'] = $member_id;
            $temp['pgoods_id'] = $prod_info['pgoods_id'];
            $temp['pgoods_name'] = $prod_info['pgoods_name'];
            $temp['pgoods_points'] = $prod_info['pgoods_points'];
            $temp['pgoods_choosenum'] = $prod_info['quantity'];
            $temp['pgoods_image'] = $prod_info['pgoods_image_old'];
            $cartgoodslist_tmp=array($temp);
        }else{
        $cartgoodslist_tmp = $this->getPointcartList(array('pmember_id' => $member_id));
        }
        if (!$cartgoodslist_tmp) {
            return array('state' => false, 'msg' => '购物车信息错误');
        }
        $cartgoodslist = array();
        foreach ($cartgoodslist_tmp as $v) {
            $cartgoodslist[$v['pgoods_id']] = $v;
        }
        //购物车礼品ID数组
        $cartgoodsid_arr = array_keys($cartgoodslist);

        //查询积分礼品信息
        $pointprod_model = model('pointprod');
        $pointprod_list = $pointprod_model->getOnlinePointProdList(array(array('pgoods_id','in', $cartgoodsid_arr)));
        if (!$pointprod_list) {
            return array('state' => false, 'msg' => '购物车信息错误');
        }
        unset($cartgoodsid_arr);
        unset($cartgoodslist_tmp);

        $cart_delid_arr = array();//应删除的购物车信息
        $pgoods_pointall = 0;//积分总数

        //查询会员信息
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID(session('member_id'));
        $member_info['member_grade'] = $member_model->getOneMemberGrade($member_info['member_exppoints']);

        //处理购物车礼品信息
        foreach ($pointprod_list as $k => $v) {
            //验证是否满足会员级别
            if ($v['pgoods_limitmgrade'] > 0 && $member_info['member_grade']['level'] < $v['pgoods_limitmgrade']) {
                $cart_delid_arr[] = $cartgoodslist[$v['pgoods_id']]['pcart_id'];
                unset($pointprod_list[$k]);
                break;
            }

            //验证积分礼品兑换状态
            $ex_state = $pointprod_model->getPointProdExstate($v);
            switch ($ex_state) {
                case 'going':
                    //验证兑换数量是否合法
                    $data = $this->getPointProdExnum($v, $cartgoodslist[$v['pgoods_id']]['pgoods_choosenum'], $member_id);
                    if (!$data['state']) {
                        //删除积分礼品兑换信息
                        $cart_delid_arr[] = $cartgoodslist[$v['pgoods_id']]['pcart_id'];
                        unset($pointprod_list[$k]);
                    }
                    else {
                        $quantity = $data['data']['quantity'];
                        $pointprod_list[$k]['quantity'] = $quantity;

                        //计算单件礼品积分数
                        $pointprod_list[$k]['onepoints'] = $quantity * intval($v['pgoods_points']);

                        //计算所有礼品积分数
                        $pgoods_pointall = $pgoods_pointall + $pointprod_list[$k]['onepoints'];

                    }
                    break;
                default:
                    //删除积分礼品兑换信息
                    $cart_delid_arr[] = $cartgoodslist[$v['pgoods_id']]['pcart_id'];
                    unset($pointprod_list[$k]);
                    break;
            }
        }

        //删除不符合条件的礼品购物车信息
        if (is_array($cart_delid_arr) && count($cart_delid_arr) > 0) {
            $this->delPointcartById($cart_delid_arr, $member_id);
        }
        if (!$pointprod_list) {
            return array('state' => false, 'msg' => '购物车信息错误');
        }
        return array(
            'state' => true, 'data' => array('pointprod_list' => $pointprod_list, 'pgoods_pointall' => $pgoods_pointall)
        );
    }

    /**
     * 验证积分是否足够
     * @access public
     * @author csdeshang
     * @param $points int 所需积分
     * @param $member_id int 会员ID
     * @param $member_info array 会员详细信息，不必须
     * @return bool 积分是否足够
     */
    public function checkPointEnough($points, $member_id, $member_info = array())
    {
        $points = intval($points);
        if ($member_id <= 0) {
            return array('state' => false, 'msg' => '会员信息错误');
        }
        if (!$member_info) {
            $member_info = model('member')->getMemberInfoByID($member_id);
        }
        if (intval($member_info['member_points']) < $points) {
            return array('state' => false, 'msg' => '积分不足，暂时不能兑换');
        }
        return array('state' => true);
    }
}