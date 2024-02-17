<?php
/**
 * 优惠套装
 *
 */

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
class Pbundling extends BaseModel
{

    const STATE1 = 1;       // 开启
    const STATE0 = 0;       // 关闭

    public $page_info;

    /**
     * 组合活动数量
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @return array
     */
    public function getBundlingCount($condition)
    {
        return Db::name('pbundling')->where($condition)->count();
    }

    /**
     * 活动列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param string $field 查询字段
     * @param string $order 排序信息
     * @param int $pagesize 分页信息
     * @param int $limit 限制数量
     * @param int $count 计数
     * @return array
     */
    public function getBundlingList($condition, $field = '*', $order = 'bl_id desc', $pagesize = 0, $limit = 0, $count = 0)
    {
        if ($pagesize) {
            $res = Db::name('pbundling')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            return $res->items();
        }
        else {
            return Db::name('pbundling')->where($condition)->order($order)->limit($limit,$count)->select()->toArray();
        }
    }

    /**
     * 开启的活动列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param int $limit 限制
     * @return array
     */
    public function getBundlingOpenList($condition, $field = '*', $order = 'bl_id desc', $limit = 0)
    {
        $condition[]=array('bl_state','=',self::STATE1);
        return $this->getBundlingList($condition, $field, $order, 0, $limit);
    }

    /**
     * 获取活动详细信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getBundlingInfo($condition)
    {
        return Db::name('pbundling')->where($condition)->find();
    }

    /**
     * 保存活动
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean
     */
    public function addBundling($data)
    {
        return Db::name('pbundling')->insertGetId($data);
    }

    /**
     * 更新活动
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editBundling($update, $condition)
    {
        return Db::name('pbundling')->where($condition)->update($update);
    }

    /**
     * 更新活动关闭
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @return boolean
     */
    public function editBundlingCloseByGoodsIds($condition)
    {
        $bundlinggoods_list = $this->getBundlingGoodsList($condition, 'bl_id');
        if (!empty($bundlinggoods_list)) {
            $blid_array = array();
            foreach ($bundlinggoods_list as $val) {
                $blid_array[] = $val['bl_id'];
            }
            $update = array('bl_state' => self::STATE0);
            return Db::name('pbundling')->where(array(array('bl_id','in', $blid_array)))->update($update);
        }
        return true;
    }

    /**
     * 删除套餐活动
     * @access public
     * @author csdeshang
     * @param array $blids 活动id
     * @return boolean
     */
    public function delBundling($blids)
    {
        $blid_array = explode(',', $blids);
        foreach ($blid_array as $val) {
            if (!is_numeric($val)) {
                return false;
            }
        }
        $where = array();
        $where[]=array('bl_id','in', $blid_array);
        $bl_list = $this->getBundlingList($where, 'bl_id');
        $bl_list = array_under_reset($bl_list, 'bl_id');
        $blid_array = array_keys($bl_list);

        $where = array();
        $where[]=array('bl_id','in', $blid_array);
        $rs = Db::name('pbundling')->where($where)->delete();
        if ($rs) {
            $res= $this->delBundlingGoods($where);
            if($res)
                return true;
            return false;
        }
        else {
            return false;
        }
    }

    /**
     * 删除套餐活动（平台后台使用）
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delBundlingForAdmin($condition)
    {
        $rs = Db::name('pbundling')->where($condition)->delete();
        if ($rs) {
            return $this->delBundlingGoods($condition);
        }
        else {
            return false;
        }
    }





    /**
     * 套餐商品列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param string $group 分组
     * @return array
     */
    public function getBundlingGoodsList($condition, $field = '*', $order = 'blgoods_id asc', $group = '')
    {
        return Db::name('pbundlinggoods')->field($field)->where($condition)->group($group)->order($order)->select()->toArray();
    }

    /**
     * 保存套餐商品
     * @access public
     * @author csdeshang
     * @param unknown $data 参数内容
     * @return boolean
     */
    public function addBundlingGoodsAll($data)
    {
        $result = Db::name('pbundlinggoods')->insertAll($data);
        if ($result) {
            foreach ((array)$data as $v) {
                // 发布套餐锁定商品
                $this->_lockGoods($v['goods_commonid'],$v['goods_id']);
                $this->_dGoodsBundlingCache($v['goods_id']);
            }
        }
        return $result;
    }

    /**
     * 删除套餐商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delBundlingGoods($condition)
    {
        $list = $this->getBundlingGoodsList($condition, 'goods_id,goods_commonid');
        if (empty($list)) {
            return true;
        }
        $result = Db::name('pbundlinggoods')->where($condition)->delete();

        if ($result) {
            foreach ($list as $v) {
                $this->_unlockGoods($v['goods_commonid'],$v['goods_id']);
                $this->_dGoodsBundlingCache($v['goods_id']);
            }
            return true;
        }
        return $result;
    }

     /**
     * 锁定商品
     * @access private
     * @author csdeshang
     * @param type $goods_commonid 商品编号
     */
    private function _lockGoods($goods_commonid,$goods_id)
    {
        $condition = array();
        $condition[] = array('goods_commonid','=',$goods_commonid);

        $goods_model = model('goods');
        $goods_model->editGoodsCommonLock($condition);
                
        $condition = array();
        $condition[] = array('goods_id','=',$goods_id);
        $goods_model->editGoodsLock($condition);
    }

    /**
     * 解锁商品
     * @access private
     * @author csdeshang
     * @param type $goods_commonid 商品编号ID
     */
    private function _unlockGoods($goods_commonid,$goods_id)
    {
        $goods_model = model('goods');
        $goods_model->editGoodsUnlock(array('goods_id' => $goods_id));
        if(!$goods_model->getGoodsCount(array('goods_commonid' => $goods_commonid,'goods_lock'=>1))){
            $goods_model->editGoodsCommonUnlock(array('goods_commonid' => $goods_commonid));
        }
    }
    /**
     * 根据商品id查询套餐数据
     * @access public
     * @author csdeshang
     * @param unknown $goods_id 商品ID
     * @return array
     */
    public function getBundlingCacheByGoodsId($goods_id)
    {
        $array = $this->_rGoodsBundlingCache($goods_id);
        if (empty($array)) {
            $bundling_array = array();
            $b_goods_array = array();
            // 根据商品id查询bl_id
            $b_g_list = $this->getBundlingGoodsList(array('goods_id' => $goods_id, 'blgoods_appoint' => 1), 'bl_id');
            if (!empty($b_g_list)) {
                $b_id_array = array();
                foreach ($b_g_list as $val) {
                    $b_id_array[] = $val['bl_id'];
                }

                // 查询套餐列表
                $bundling_list = $this->getBundlingOpenList(array(array('bl_id','in', $b_id_array)));
                // 整理
                if (!empty($bundling_list)) {
                    foreach ($bundling_list as $val) {
                        $bundling_array[$val['bl_id']]['id'] = $val['bl_id'];
                        $bundling_array[$val['bl_id']]['name'] = $val['bl_name'];
                        $bundling_array[$val['bl_id']]['cost_price'] = 0;
                        $bundling_array[$val['bl_id']]['price'] = $val['bl_discount_price'];
                        $bundling_array[$val['bl_id']]['freight'] = $val['bl_freight'];
                    }
                    $blid_array = array_keys($bundling_array);

                    $b_goods_list = $this->getBundlingGoodsList(array(array('bl_id','in', $blid_array)));
                    if (!empty($b_goods_list) && count($b_goods_list) > 1) {
                        $goodsid_array = array();
                        foreach ($b_goods_list as $val) {
                            $goodsid_array[] = $val['goods_id'];
                        }
                        $goods_list = model('goods')->getGoodsList(array(array('goods_id','in', $goodsid_array)), 'goods_id,goods_name,goods_price,goods_image');
                        $goods_list = array_under_reset($goods_list, 'goods_id');
                        foreach ($b_goods_list as $val) {
                            if (isset($goods_list[$val['goods_id']])) {
                                $k = (intval($val['goods_id']) == $goods_id) ? 0 : $val['goods_id'];    // 排序当前商品放到最前面
                                $b_goods_array[$val['bl_id']][$k]['id'] = $val['goods_id'];
                                $b_goods_array[$val['bl_id']][$k]['image'] = goods_thumb($goods_list[$val['goods_id']], 240);
                                $b_goods_array[$val['bl_id']][$k]['name'] = $goods_list[$val['goods_id']]['goods_name'];
                                $b_goods_array[$val['bl_id']][$k]['shop_price'] = ds_price_format($goods_list[$val['goods_id']]['goods_price']);
                                $b_goods_array[$val['bl_id']][$k]['price'] = ds_price_format($val['blgoods_price']);
                                $bundling_array[$val['bl_id']]['cost_price'] += $goods_list[$val['goods_id']]['goods_price'];
                            }
                        }
                    }
                }
            }
            $array = array(
                'bundling_array' => serialize($bundling_array), 'b_goods_array' => serialize($b_goods_array)
            );
            $this->_wGoodsBundlingCache($goods_id, $array);
        }
        return $array;
    }

    /**
     * 读取商品优惠套装缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    private function _rGoodsBundlingCache($goods_id)
    {
        return rcache($goods_id, 'goods_bundling');
    }

    /**
     * 写入商品优惠套装缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @param array $array 缓存数组
     * @return boolean
     */
    private function _wGoodsBundlingCache($goods_id, $array)
    {
        return wcache($goods_id, $array, 'goods_bundling');
    }

    /**
     * @access public
     * @author csdeshang
     * 删除商品优惠套装缓存
     * @param int $goods_id 商品ID
     * @return boolean
     */
    private function _dGoodsBundlingCache($goods_id)
    {
        return dcache($goods_id, 'goods_bundling');
    }
}