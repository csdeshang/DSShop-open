<?php

/**
 * 拼团活动模型 
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
class Ppintuan extends BaseModel {

    public $page_info;

    const PINTUAN_STATE_CLOSE = 0;
    const PINTUAN_STATE_NORMAL = 1;
    const PINTUAN_STATE_CANCEL = 2;

    private $pintuan_state_array = array(
        self::PINTUAN_STATE_CLOSE => '已结束',
        self::PINTUAN_STATE_NORMAL => '正常',
        self::PINTUAN_STATE_CANCEL => '管理员关闭'
    );

    /**
     * 读取拼团列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 拼团列表
     */
    public function getPintuanList($condition, $pagesize = null, $order = '', $field = '*') {
        if ($pagesize) {
            $res = Db::name('ppintuan')->field($field)->where($condition)->order($order)->paginate(['list_rows' => $pagesize, 'query' => request()->param()], false);
            $pintuan_list = $res->items();
            $this->page_info = $res;
        } else {
            $pintuan_list = Db::name('ppintuan')->field($field)->where($condition)->order($order)->select()->toArray();
        }

        if (!empty($pintuan_list)) {
            for ($i = 0, $j = count($pintuan_list); $i < $j; $i++) {
                $pintuan_list[$i] = $this->getPintuanExtendInfo($pintuan_list[$i]);
            }
        }

        return $pintuan_list;
    }

    /**
     * 根据条件读取限制折扣信息
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array 拼团信息
     */
    public function getPintuanInfo($condition) {
        $pintuan_info = Db::name('ppintuan')->where($condition)->find();
        if(!empty($pintuan_info)){
            $pintuan_info = $this->getPintuanExtendInfo($pintuan_info);
        }
        return $pintuan_info;
    }

    /**
     * 根据拼团编号读取限制折扣信息
     * @access public
     * @author csdeshang
     * @param array $pintuan_id 限制折扣活动编号
     * @return array 拼团信息
     */
    public function getPintuanInfoByID($pintuan_id) {
        if (intval($pintuan_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition[] = array('pintuan_id','=',$pintuan_id);
        $pintuan_info = $this->getPintuanInfo($condition);
            return $pintuan_info;
    }

    /**
     * 拼团状态数组
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getPintuanStateArray() {
        return $this->pintuan_state_array;
    }

    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return type
     */
    public function addPintuan($data) {
        $data['pintuan_state'] = self::PINTUAN_STATE_NORMAL;
        $flag= Db::name('ppintuan')->insertGetId($data);
        if($flag){
            // 发布拼团锁定商品
            $this->_lockGoods($data['pintuan_goods_commonid']);
        }
        return $flag;
    }
    /**
     * 删除活动
     * @author csdeshang
     * @param type $condition 删除条件
     * @return type
     */
    public function delPintuan($condition)
    {
        return Db::name('ppintuan')->where($condition)->delete();
    }

     /**
     * 锁定商品
     * @access private
     * @author csdeshang
     * @param type $goods_commonid 商品编号
     */
    private function _lockGoods($goods_commonid)
    {
        $condition = array();
        $condition[] = array('goods_commonid','=',$goods_commonid);

        $goods_model = model('goods');
        $goods_model->editGoodsCommonLock($condition);
        $goods_model->editGoodsLock($condition);
    }

    /**
     * 解锁商品
     * @access private
     * @author csdeshang
     * @param type $goods_commonid 商品编号ID
     */
    private function _unlockGoods($goods_commonid)
    {
        $goods_model = model('goods');
        $goods_model->editGoodsCommonUnlock(array(array('goods_commonid' ,'in', $goods_commonid)));
        $goods_model->editGoodsUnlock(array(array('goods_commonid' ,'in', $goods_commonid)));
    }
 
    /**
     * 编辑更新
     * @param type $update 更新数据
     * @param type $condition 条件
     * @return type
     */
    public function editPintuan($update, $condition) {
        return Db::name('ppintuan')->where($condition)->update($update);
    }


    /**
     * 指定拼团活动结束,参团成功的继续参团,不成功的保持默认.
     * @access public
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function endPintuan($condition) {
        $goods_commonid=Db::name('ppintuan')->where($condition)->column('pintuan_goods_commonid');
        $data['pintuan_state'] = self::PINTUAN_STATE_CLOSE;
        $flag= Db::name('ppintuan')->where($condition)->update($data);
        if($flag){
            if(!empty($goods_commonid)){
                $this->_unlockGoods($goods_commonid);
            }
        }
        return $flag;
    }


    /**
     * 获取拼团扩展信息，包括状态文字和是否可编辑状态
     * @access public
     * @author csdeshang
     * @param type $pintuan_info 拼团信息
     * @return boolean
     */
    public function getPintuanExtendInfo($pintuan_info) {
        $pintuan_info['pintuan_state_text'] = $this->pintuan_state_array[$pintuan_info['pintuan_state']];
        //是否有正在参加拼团的订单
        $pintuan_order_count=Db::name('ppintuanorder')->where(array('pintuan_id'=>$pintuan_info['pintuan_id'],'pintuanorder_state'=>1))->count();
        if ($pintuan_info['pintuan_state'] == self::PINTUAN_STATE_NORMAL && $pintuan_info['pintuan_end_time'] > TIMESTAMP) {
            $pintuan_info['editable'] = true;
        } else {
            $pintuan_info['editable'] = false;
        }

        return $pintuan_info;
    }

    /**
     * 过期修改状态
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return boolean
     */
    public function editExpirePintuan($condition) {
        $condition[]=array('pintuan_end_time','<', TIMESTAMP);

        $condition[]=array('pintuan_state','=',self::PINTUAN_STATE_NORMAL);

        $updata = array();
        $update['pintuan_state'] = self::PINTUAN_STATE_CLOSE;
        $this->editPintuan($update, $condition);
        return true;
    }

    /**
     * 读取拼团列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $field 字段
     * @param type $limit 限制
     * @return type
     */
    public function getPintuanExtendList($condition, $pagesize = null, $order = 'pintuan_state asc', $field = '*', $limit = 0) {
        $pintuan_list = $this->getPintuanList($condition, $pagesize, $order, $field, $limit);
        if (!empty($pintuan_list)) {
            for ($i = 0, $j = count($pintuan_list); $i < $j; $i++) {
                $pintuan_list[$i] = $this->getPintuanExtendInfo($pintuan_list[$i]);
            }
        }
        return $pintuan_list;
    }

    /**
     * 根据商品编号查询是否有可用拼团活动，如果有返回抢购信息，没有返回null
     * @param type $goods_commonid 商品id
     * @return array
     */
    public function getPintuanInfoByGoodsCommonID($goods_commonid) {
        $info = $this->_rGoodsPintuanCache($goods_commonid);
        if (empty($info)) {
            $condition = array();
            $condition[]=array('pintuan_state','=',self::PINTUAN_STATE_NORMAL);
            $condition[]=array('pintuan_end_time','>', TIMESTAMP);
            $condition[]=array('pintuan_goods_commonid','=',$goods_commonid);
            $pintuan_goods_list = $this->getPintuanExtendList($condition, null, 'pintuan_starttime asc', '*', 1);
            $pintuan_goods = isset($pintuan_goods_list[0]) ? $pintuan_goods_list[0] : "";
            
            if (!empty($pintuan_goods)) {
                //获取此商品拼团对应的开团人
                $condition = array();
                $condition[] = array('pintuangroup_joined','>',0);
                $condition[] = array('pintuan_id','=',$pintuan_goods['pintuan_id']);
                $condition[] = array('pintuangroup_state','=',1);
                $pintuan_goods['pintuangroup_list'] = model('ppintuangroup')->getPpintuangroupList($condition);
            }

            //序列化存储到缓存
            $info['info'] = serialize($pintuan_goods);
            $this->_wGoodsPintuanCache($goods_commonid, $info);
        }
        $pintuan_goods_info = unserialize($info['info']);
        if (!empty($pintuan_goods_info) && ($pintuan_goods_info['pintuan_starttime'] > TIMESTAMP || $pintuan_goods_info['pintuan_end_time'] < TIMESTAMP)) {
            $pintuan_goods_info = array();
        }
        return $pintuan_goods_info;
    }

    /**
     * 读取商品抢购缓存
     * @access public
     * @author csdeshang
     * @param type $goods_commonid 商品id
     * @return type
     */
    private function _rGoodsPintuanCache($goods_commonid) {
        return rcache($goods_commonid, 'goods_pintuan');
    }

    /**
     * 写入商品抢购缓存
     * @access public
     * @author csdeshang
     * @param type $goods_commonid ID
     * @param type $info 信息
     * @return type
     */
    private function _wGoodsPintuanCache($goods_commonid, $info) {
        return wcache($goods_commonid, $info, 'goods_pintuan');
    }

    /**
     * 删除商品抢购缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品ID
     * @return boolean
     */
    public function _dGoodsPintuanCache($goods_commonid) {
        return dcache($goods_commonid, 'goods_pintuan');
    }
    
}
