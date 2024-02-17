<?php
/**
 * 商品推荐组合模型
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
class Goodscombo extends BaseModel
{
    
    /**
     * 插入数据
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean
     */
    public function addGoodscomboAll($data) {
        $result = Db::name('goodscombo')->insertAll($data);
        if ($result) {
            foreach ((array)$data as $v) {
                if ($v['goods_id']) $this->_dGoodscomboCache($v['goods_id']);
            }
        }
        return $result;
    }
    
    /**
     * 查询组合商品列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return boolean
     */
    public function getGoodscomboList($condition) {
        return Db::name('goodscombo')->where($condition)->select()->toArray();
    }

    /**
     * 查询组合商品列表
     * @access public
     * @author csdeshang
     * @param type $condition 查询条件
     * @return boolean
     */
    public function delGoodscombo($condition) {
        $list = $this->getGoodscomboList($condition, 'goods_id');
        if (empty($list)) {
            return true;
        }
        $result = Db::name('goodscombo')->where($condition)->delete();
        if ($result) {
            foreach ($list as $v) {
                $this->_dGoodscomboCache($v['goods_id']);
            }
        }
        return $result;
    }
    /**
     * 获取商品组合缓存
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品ID
     * @return array
     */
    public function getGoodscomboCacheByGoodsId($goods_id) {
        $array = $this->_rGoodscomboCache($goods_id);
        if (empty($array)) {
            $gcombo_list = array();
            $combo_list = $this->getGoodscomboList(array('goods_id' => $goods_id));
            if (!empty($combo_list)) {
                $comboid_array= array();
                foreach ($combo_list as $val) {
                    $comboid_array[] = $val['combo_goodsid'];
                }
                $gcombo_list = model('goods')->getGeneralGoodsList(array(array('goods_id','in', $comboid_array)));
            }
            $array = array('gcombo_list' => serialize($gcombo_list));
            $this->_wGoodscomboCache($goods_id, $array);
        }
        return $array;
    }

    /**
     * 读取商品推荐搭配缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品id
     * @return array
     */
    private function _rGoodscomboCache($goods_id) {
        return rcache($goods_id, 'goods_combo');
    }

    /**
     * 写入商品推荐搭配缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @param array $array 数组内容
     * @return boolean
     */
    private function _wGoodscomboCache($goods_id, $array) {
        return wcache($goods_id, $array, 'goods_combo', 60);
    }

    /**
     * 删除商品推荐搭配缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品第
     * @return boolean
     */
    private function _dGoodscomboCache($goods_id) {
        return dcache($goods_id, 'goods_combo');
    }
}
