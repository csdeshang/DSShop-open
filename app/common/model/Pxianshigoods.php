<?php

/**
 * 秒杀活动商品模型 
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
class Pxianshigoods extends BaseModel {

    public $page_info;

    const XIANSHI_GOODS_STATE_CANCEL = 0;
    const XIANSHI_GOODS_STATE_NORMAL = 1;

    /**
     * 读取秒杀商品列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $field 字段
     * @param type $limit 个数限制
     * @return array 秒杀商品列表
     */
    public function getXianshigoodsList($condition, $pagesize = null, $order = '', $field = '*', $limit = 0) {
        if ($pagesize) {
            $res = Db::name('pxianshigoods')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return $xianshigoods_list = Db::name('pxianshigoods')->field($field)->where($condition)->limit($limit)->order($order)->select()->toArray();
        }
    }

    /**
     * 读取秒杀商品列表
     * @access public
     * @author csdeshang
     * @param type $condition 查询条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $field 字段
     * @param type $limit 个数限制
     * @return array 
     */
    public function getXianshigoodsExtendList($condition, $pagesize = null, $order = '', $field = '*', $limit = 0) {
        $xianshigoods_list = $this->getXianshigoodsList($condition, $pagesize, $order, $field, $limit);
        if (!empty($xianshigoods_list)) {
            for ($i = 0, $j = count($xianshigoods_list); $i < $j; $i++) {
                $xianshigoods_list[$i] = $this->getXianshigoodsExtendInfo($xianshigoods_list[$i]);
            }
        }
        return $xianshigoods_list;
    }

    /**
     * 获取秒杀列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $field 字段
     * @param type $limit 限制
     * @return type
     */
    public function getXianshigoodsExtendIds($condition, $pagesize = null, $order = '', $field = 'goods_id', $limit = 0) {
        $xianshigoods_id_list = $this->getXianshigoodsList($condition, $pagesize, $order, $field, $limit);

        if (!empty($xianshigoods_id_list)) {
            for ($i = 0; $i < count($xianshigoods_id_list); $i++) {

                $xianshigoods_id_list[$i] = $xianshigoods_id_list[$i]['goods_id'];
            }
        }

        return $xianshigoods_id_list;
    }

    /**
     * 根据条件读取限制折扣商品信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return array
     */
    public function getXianshigoodsInfo($condition) {
        $result = Db::name('pxianshigoods')->where($condition)->find();
        return $result;
    }

    /**
     * 根据秒杀商品编号读取限制折扣商品信息
     * @access public
     * @author csdeshang
     * @param type $xianshigoods_id ID编号
     * @return type
     */
    public function getXianshigoodsInfoByID($xianshigoods_id) {
        if (intval($xianshigoods_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition[] = array('xianshigoods_id','=',$xianshigoods_id);
        $xianshigoods_info = $this->getXianshigoodsInfo($condition);
        return $xianshigoods_info;
    }

    /**
     * @access public
     * @author csdeshang
     * 增加秒杀商品
     * @param type $xianshigoods_info 秒杀商品信息
     * @return bool
     */
    public function addXianshigoods($xianshigoods_info) {
        $xianshigoods_info['xianshigoods_state'] = self::XIANSHI_GOODS_STATE_NORMAL;
        $xianshigoods_id = Db::name('pxianshigoods')->insertGetId($xianshigoods_info);
        if($xianshigoods_id){
            // 发布秒杀锁定商品
            $this->_lockGoods($xianshigoods_info['goods_commonid'],$xianshigoods_info['goods_id']);
        }
        // 删除商品秒杀缓存
        $this->_dGoodsXianshiCache($xianshigoods_info['goods_id']);

        $xianshigoods_info['xianshigoods_id'] = $xianshigoods_id;
        $xianshigoods_info = $this->getXianshigoodsExtendInfo($xianshigoods_info);
        return $xianshigoods_info;
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param type $update 数据更新
     * @param type $condition 条件
     * @return type
     */
    public function editXianshigoods($update, $condition) {
        $result = Db::name('pxianshigoods')->where($condition)->update($update);
        if ($result) {
            $xianshigoods_list = $this->getXianshigoodsList($condition, null, '', 'goods_id');
            if (!empty($xianshigoods_list)) {
                foreach ($xianshigoods_list as $val) {
                    // 删除商品秒杀缓存
                    $this->_dGoodsXianshiCache($val['goods_id']);
                    // 插入对列 更新促销价格
                    model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsId','cron_value'=>serialize($val['goods_id'])));
                }
            }
        }
        return $result;
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function delXianshigoods($condition) {
        $xianshigoods_list = $this->getXianshigoodsList($condition, null, '', 'goods_id,goods_commonid');
        $result = Db::name('pxianshigoods')->where($condition)->delete();
        if ($result) {
            if (!empty($xianshigoods_list)) {
                foreach ($xianshigoods_list as $val) {
                    $this->_unlockGoods($val['goods_commonid'],$val['goods_id']);
                    // 删除商品秒杀缓存
                    $this->_dGoodsXianshiCache($val['goods_id']);
                    // 插入对列 更新促销价格
                    model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsId','cron_value'=>serialize($val['goods_id'])));
                }
            }
        }
        return $result;
    }

    /**
     * 获取秒杀商品扩展信息
     * @access public
     * @author csdeshang
     * @param type $xianshi_info  信息
     * @return string
     */
    public function getXianshigoodsExtendInfo($xianshi_info) {
        $xianshi_info['goods_url'] = (string)url('Goods/index', array('goods_id' => $xianshi_info['goods_id']));
        $xianshi_info['image_url'] = goods_cthumb($xianshi_info['goods_image'], 60);
        $xianshi_info['xianshigoods_price'] = ds_price_format($xianshi_info['xianshigoods_price']);
        $xianshi_info['xianshi_discount'] = number_format($xianshi_info['xianshigoods_price'] / $xianshi_info['goods_price'] * 10, 1, '.', '') . '折';
        return $xianshi_info;
    }

    /**
     * 获取推荐秒杀商品
     * @access public
     * @author csdeshang
     * @param type $count 推荐数量
     * @return type
     */
    public function getXianshigoodsCommendList($count = 4) {
        $condition = array();
        $condition[]=array('xianshigoods_state','=',self::XIANSHI_GOODS_STATE_NORMAL);
        $condition[]=array('xianshigoods_starttime','<', TIMESTAMP);
        $condition[]=array('xianshigoods_end_time','>', TIMESTAMP);
        $xianshi_list = $this->getXianshigoodsExtendList($condition, null, 'xianshigoods_recommend desc', '*', $count);
        return $xianshi_list;
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
     * 根据商品编号查询是否有可用秒杀活动，如果有返回秒杀活动，没有返回null
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品id
     * @return array
     */
    public function getXianshigoodsInfoByGoodsID($goods_id) {
        $info = $this->_rGoodsXianshiCache($goods_id);
        if (empty($info)) {
            $condition[]=array('xianshigoods_state','=',self::XIANSHI_GOODS_STATE_NORMAL);
            $condition[]=array('xianshigoods_end_time','>', TIMESTAMP);
            $condition[]=array('goods_id','=',$goods_id);
            $xianshigoods_list = $this->getXianshigoodsExtendList($condition, null, 'xianshigoods_starttime asc', '*', 1);
            $info['info'] = isset($xianshigoods_list[0]) ? serialize($xianshigoods_list[0]) : serialize("");
            $this->_wGoodsXianshiCache($goods_id, $info);
        }
        $xianshigoods_info = unserialize($info['info']);
        if (!empty($xianshigoods_info) && ($xianshigoods_info['xianshigoods_starttime'] > TIMESTAMP || $xianshigoods_info['xianshigoods_end_time'] < TIMESTAMP)) {
            $xianshigoods_info = array();
        }
        return $xianshigoods_info;
    }

    /**
     * 根据商品编号查询是否有可用秒杀活动，如果有返回秒杀活动，没有返回null
     * @access public
     * @author csdeshang
     * @param type $goods_string 商品编号字符串，例：'11,22,33'
     * @return type
     */
    public function getXianshigoodsListByGoodsString($goods_string) {
        $xianshigoods_list = $this->_getXianshigoodsListByGoods($goods_string);
        $xianshigoods_list = array_under_reset($xianshigoods_list, 'goods_id');
        return $xianshigoods_list;
    }

    /**
     * 根据商品编号查询是否有可用秒杀活动，如果有返回秒杀活动，没有返回null
     * @access public
     * @author csdeshang
     * @param type $goods_id_string  商品编号字符串
     * @return type
     */
    private function _getXianshigoodsListByGoods($goods_id_string) {
        $condition = array();
        $condition[]=array('xianshigoods_state','=',self::XIANSHI_GOODS_STATE_NORMAL);
        $condition[]=array('xianshigoods_starttime','<', TIMESTAMP);
        $condition[]=array('xianshigoods_end_time','>', TIMESTAMP);
        $condition[]=array('goods_id','in', $goods_id_string);
        $xianshigoods_list = $this->getXianshigoodsExtendList($condition, null, 'xianshigoods_id desc', '*');
        return $xianshigoods_list;
    }

    /**
     * 读取商品秒杀缓存
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品id
     * @return type
     */
    private function _rGoodsXianshiCache($goods_id) {
        return rcache($goods_id, 'goods_xianshi');
    }

    /**
     * 写入商品秒杀缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品id
     * @param array $info 信息
     * @return boolean
     */
    private function _wGoodsXianshiCache($goods_id, $info) {
        return wcache($goods_id, $info, 'goods_xianshi');
    }

    /**
     * 删除商品秒杀缓存
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品id
     * @return bool
     */
    private function _dGoodsXianshiCache($goods_id) {
        return dcache($goods_id, 'goods_xianshi');
    }

}
