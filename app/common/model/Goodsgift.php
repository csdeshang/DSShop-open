<?php
/**
 * 商品赠品模型
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
class Goodsgift extends BaseModel
{
    
    /**
     * 插入数据
     * @access public
     * @author csdeshang
     * @param array $insert 插入内容
     * @return boolean
     */
    public function addGoodsgiftAll($insert) {
        return Db::name('goodsgift')->insertAll($insert);
    }

    /**
     * 查询赠品列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return array
     */
    public function getGoodsgiftList($condition) {
        return Db::name('goodsgift')->where($condition)->select()->toArray();
    }
    /**
     * 获取赠品列表根据商品ID
     * @access public
     * @author csdeshang
     * @param int $goods_id
     * @return array
     */
    public function getGoodsgiftListByGoodsId($goods_id) {
        $condition[] = array('goods_id','=',$goods_id);
        $list = $this->_rGoodsgiftCache($goods_id);
        if (empty($list)) {
            $gift_list = $this->getGoodsgiftList($condition);
            $list['gift'] = serialize($gift_list);
            $this->_wGoodsgiftCache($goods_id, $list);
        }
        $gift_list = unserialize($list['gift']);
        return $gift_list;
    }
    
    /**
     * 删除赠品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delGoodsgift($condition) {
        $gift_list = $this->getGoodsgiftList($condition);
        if (empty($gift_list)) {
            return true;
        }
        $result = Db::name('goodsgift')->where($condition)->delete();
        if ($result) {
            foreach ($gift_list as $val) {
                $this->_dGoodsgiftCache($val['goods_id']);
            }
        }
        return $result;
    }
    
    /**
     * 读取商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    private function _rGoodsgiftCache($goods_id) {
        return rcache($goods_id, 'goods_gift');
    }
    
    /**
     * 写入商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @param array $list 列表
     * @return boolean
     */
    private function _wGoodsgiftCache($goods_id, $list) {
        return wcache($goods_id, $list, 'goods_gift');
    }
    
    /**
     * 删除商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return boolean
     */
    private function _dGoodsgiftCache($goods_id) {
        return dcache($goods_id, 'goods_gift');
    }
}
