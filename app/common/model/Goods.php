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
class Goods extends BaseModel {

    const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE10 = 10;     // 存入仓库
    public $page_info;

    /**
     * 新增商品数据
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return type
     */
    public function addGoods($data) {
        $result = Db::name('goods')->insertGetId($data);
        if ($result) {
            $this->_dGoodsCache($result);
            $this->_dGoodsCommonCache($data['goods_commonid']);
            $this->_dGoodsSpecCache($data['goods_commonid']);
        }
        return $result;
    }

    /**
     * 新增商品公共数据
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return type
     */
    public function addGoodsCommon($data) {
        return Db::name('goodscommon')->insertGetId($data);
    }

    /**
     * 新增多条商品数据
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return type
     */
    public function addGoodsImagesAll($data) {
        $result = Db::name('goodsimages')->insertAll($data);
        if ($result) {
            foreach ($data as $val) {
                $this->_dGoodsImageCache($val['goods_commonid'] . '|' . $val['color_id']);
            }
        }
        return $result;
    }

    /**
     * 商品SKU列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field 字段
     * @param type $group 分组
     * @param type $order 排序
     * @param type $limit 限制
     * @param type $pagesize 分页
     * @param type $lock 是否锁定
     * @param type $count 计数
     * @return array
     */
    public function getGoodsList($condition, $field = '*', $group = '', $order = '', $limit = 0, $pagesize = 0, $lock = false, $count = 0) {
//        $condition = $this->_getRecursiveClass($condition);
        if ($pagesize) {
            $result = Db::name('goods')->field($field)->where($condition)->group($group)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            $result = Db::name('goods')->field($field)->where($condition)->limit($limit)->group($group)->order($order)->select()->toArray();
            return $result;
        }
    }

    /**
     * 获取指定分类指定的随机商品列表 
     * @access public
     * @author csdeshang
     * @param int $gcId 一级分类ID
     * @param int $notEqualGoodsId 此商品ID除外
     * @param int $size 列表最大长度
     * @return array|null
     */
    public function getGoodsGcRandList($gcId, $notEqualGoodsId = 0, $size = 4) {
        $condition = array();
        $condition[] = array('gc_id_1','=',(int)$gcId);
        if ($notEqualGoodsId > 0) {
            $condition[] = array('goods_id','<>', (int)$notEqualGoodsId);
        }
        return Db::name('goods')->where($condition)->limit($size)->select()->toArray();
    }

    /**
     * 出售中的商品SKU列表（只显示不同颜色的商品，前台商品索引，店铺也商品列表等使用）
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param string $field 字段
     * @param type $order 排序
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @return type
     */
    public function getGoodsListByColorDistinct($condition, $field = '*', $order = 'goods_id asc', $pagesize = 0,$limit=0) {
        $condition[]=array('goods_state','=',self::STATE1);
//        $condition = $this->_getRecursiveClass($condition);

        $field = "CONCAT(goods_commonid) as nc_distinct ," . $field;
        $count = Db::name('goods')->where($condition)->field("distinct CONCAT(goods_commonid)")->count();
        $goods_list = array();
        if ($count != 0) {
            $goods_list = $this->getGoodsOnlineList($condition, $field, $pagesize, $order, $limit, 'CONCAT(goods_commonid)', false, $count);
        }
        return $goods_list;
    }

    /**
     * 获取goodscommon和goods联表列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param string $field 字段
     * @param type $order 排序
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @return type
     */
    public function getGoodsUnionList($condition, $field, $order = 'goodscommon.goods_commend desc,goodscommon.goods_sort asc',$group='', $pagesize = 0,$limit=0) {
        $condition[]=array('goodscommon.goods_state','=',self::STATE1);
//        if(isset($condition['goodscommon.gc_id'])){
//            $condition = $this->_getRecursiveClass($condition,'goodscommon.');
//        }

        $result=Db::name('goodscommon')->alias('goodscommon')->join('goods goods','goods.goods_commonid=goodscommon.goods_commonid')->field($field)->where($condition)->order($order);
        if($group){
            $result=$result->group($group);
        }
        if ($pagesize) {
            $result=$result->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            $result = $result->limit($limit)->select()->toArray();
            return $result;
        }
    }

    /**
     * 在售商品SKU列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param int $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getGeneralGoodsList($condition, $field = '*', $pagesize = 0, $order = 'goods_id desc') {
        $condition[]=array('is_virtual','=',0);
        $condition[]=array('is_goodsfcode','=',0);
        $condition[]=array('is_presell','=',0);
        $condition[]=array('goods_state','=',1);
        return $this->getGoodsList($condition, $field, '', $order, 0, $pagesize, false, 0);
    }

    /**
     * 在售商品SKU列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param str $field 字段
     * @param int $pagesize 分页
     * @param str $order 排序
     * @param int $limit 限制
     * @param str $group 分组
     * @param bool $lock 是否锁定
     * @param int $count 计数
     * @return array
     */
    public function getGoodsOnlineList($condition, $field = '*', $pagesize = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0) {
        $condition[]=array('goods_state','=',self::STATE1);
        return $this->getGoodsList($condition, $field, $group, $order, $limit, $pagesize, $lock, $count);
    }

    /**
     * 出售中的普通商品列表，即不包括虚拟商品、F码商品、预售商品
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field 字段
     * @param type $pagesize 分页
     * @param type $type 类型
     * @return array
     */
    public function getGoodsListForPromotion($condition, $field = '*', $pagesize = 0, $type = '') {
        switch ($type) {
            case 'xianshi':
            case 'bargain':
            case 'bundling':
                $condition[]=array('goodscommon.is_virtual','=',0);
                $condition[]=array('goodscommon.is_goodsfcode','=',0);
                $condition[]=array('goodscommon.is_presell','=',0);
                $condition[]=array('goods.goods_lock','=',0);
                $condition[]=array('goodscommon.goods_state','=',self::STATE1);
                return $this->getGoodsUnionList($condition, $field, 'goodscommon.goods_commonid asc','', $pagesize);
            case 'combo':
                $condition[]=array('is_virtual','=',0);
                $condition[]=array('is_goodsfcode','=',0);
                $condition[]=array('is_presell','=',0);
                $condition[]=array('goods_state','=',self::STATE1);
                return $this->getGoodsList($condition, $field, '', '', 0, $pagesize);
            case 'gift':
                $condition[]=array('is_virtual','=',0);
                return $this->getGoodsList($condition, $field, '', '', 0, $pagesize);
            default:
                break;
        }
        
    }

    /**
     * 商品列表 卖家中心使用
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonList($condition, $field = '*', $pagesize = 10, $order = 'goods_commonid desc') {
//        $condition = $this->_getRecursiveClass($condition);
        if ($pagesize) {
            $result = Db::name('goodscommon')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('goodscommon')->field($field)->where($condition)->order($order)->select()->toArray();
        }
    }
	
	/**
	 * 仓库中的商品列表
	 * @access public
	 * @author csdeshang
	 * @param array $condition 条件
	 * @param array $field 字段
	 * @param string $pagesize 分页
	 * @param string $order 排序
	 * @return array
	 */
	public function getGoodsCommonLockUpList($condition, $field = '*', $pagesize = 10, $order = "goods_commonid desc") {
	    $condition[]=array('goods_state','=',self::STATE0);
	    return $this->getGoodsCommonList($condition, $field, $pagesize, $order);
	}

    /**
     * 出售中的商品列表 卖家中心使用
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOnlineList($condition, $field = '*', $pagesize = 10, $order = "goods_commonid desc") {
        $condition[]=array('goods_state','=',self::STATE1);
        return $this->getGoodsCommonList($condition, $field, $pagesize, $order);
    }

    /**
     * 出售中的普通商品列表，即不包括虚拟商品、F码商品、预售商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param str $field 字段
     * @param int $pagesize 分页
     * @param str $type 排序
     * @return array
     */
    public function getGoodsCommonListForPromotion($condition, $field = '*', $pagesize = 10, $type) {
        if ($type == 'groupbuy') {
            $condition[]=array('is_virtual','=',0);
            $condition[]=array('is_goodsfcode','=',0);
            $condition[]=array('is_presell','=',0);
            $condition[]=array('goods_lock','=',0);
            $condition[]=array('goods_state','=',self::STATE1);
        }
        return $this->getGoodsCommonList($condition, $field, $pagesize);
    }

    /**
     * 出售中的未参加促销的虚拟商品列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param str $field 字段
     * @param int $pagesize 分页
     * @return array
     */
    public function getGoodsCommonListForVrPromotion($condition, $field = '*', $pagesize = 10) {
        $condition[]=array('is_virtual','=',1);
        $condition[]=array('is_goodsfcode','=',0);
        $condition[]=array('is_presell','=',0);
        $condition[]=array('goods_lock','=',0);
        $condition[]=array('goods_state','=',self::STATE1);

        return $this->getGoodsCommonList($condition, $field, $pagesize);
    }

    /**
     * 仓库中的商品列表 卖家中心使用
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOfflineList($condition, $field = '*', $pagesize = 10, $order = "goods_commonid desc") {
        $condition[]=array('goods_state','=',self::STATE0);
        return $this->getGoodsCommonList($condition, $field, $pagesize, $order);
    }

    /**
     * 查询推荐商品(随机排序) 
     * @access public
     * @author csdeshang
     * @param int $limit 限制
     * @return array
     */
    public function getGoodsCommendList($limit = 5) {
        $goods_commend_list = $this->getGoodsOnlineList(array(array('goods_commend','=',1)), 'goods_id,goods_name,goods_advword,goods_image,goods_promotion_price,goods_salenum,goods_price', 0, '', $limit, 'goods_commonid');
        if (!empty($goods_id_list)) {
            $tmp = array();
            foreach ($goods_id_list as $v) {
                $tmp[] = $v['goods_id'];
            }
            $goods_commend_list = $this->getGoodsOnlineList(array(array('goods_id','in', $tmp)), 'goods_id,goods_name,goods_advword,goods_image,goods_promotion_price', 0, '', $limit);
        }
        return $goods_commend_list;
    }

    /**
     * 计算商品库存
     * @access public
     * @author csdeshang
     * @param array $goods_list 商品列表
     * @return array|boolean
     */
    public function calculateStorage($goods_list) {
        // 计算库存
        if (!empty($goods_list)) {
            $goodsid_array = array();
            foreach ($goods_list as $value) {
                $goodscommonid_array[] = $value['goods_commonid'];
            }
            $goods_storage = $this->getGoodsList(array(array('goods_commonid','in', $goodscommonid_array)), 'goods_storage,goods_commonid,goods_id,goods_storage_alarm');
            $storage_array = array();
            foreach ($goods_storage as $val) {
                if ($val['goods_storage_alarm'] != 0 && $val['goods_storage'] <= $val['goods_storage_alarm']) {
                    $storage_array[$val['goods_commonid']]['alarm'] = true;
                }
                //初始化
                if (!isset($storage_array[$val['goods_commonid']]['sum'])) {
                    $storage_array[$val['goods_commonid']]['sum'] = 0;
                }
                $storage_array[$val['goods_commonid']]['sum'] += $val['goods_storage'];
                $storage_array[$val['goods_commonid']]['goods_id'] = $val['goods_id'];
            }
            return $storage_array;
        } else {
            return false;
        }
    }

    /**
     * 更新商品SUK数据
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoods($update, $condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id');
        if (empty($goods_list)) {
            return true;
        }
        $goodsid_array = array();
        foreach ($goods_list as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        return $this->editGoodsById($update, $goodsid_array);
    }

    /**
     * 更新商品SUK数据
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param int|array $goodsid_array 商品ID
     * @return boolean|unknown
     */
    public function editGoodsById($update, $goodsid_array) {
        if (empty($goodsid_array)) {
            return true;
        }
        $condition[]=array('goods_id','in', (array)$goodsid_array);
        $update['goods_edittime'] = TIMESTAMP;
        $result = Db::name('goods')->where($condition)->update($update);
        if ($result) {
            foreach ((array) $goodsid_array as $value) {
                $this->_dGoodsCache($value);
            }
        }
        return $result;
    }

    /**
     * 更新商品促销价 (需要验证抢购和秒杀是否进行)
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsPromotionPrice($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid');
        $goods_array = array();
        foreach ($goods_list as $val) {
            $goods_array[$val['goods_commonid']][$val['goods_id']] = $val;
        }
        $groupbuy_model = model('groupbuy');
        $pxianshigoods_model = model('pxianshigoods');
        foreach ($goods_array as $key => $val) {
            // 查询抢购时候进行
            $groupbuy = $groupbuy_model->getGroupbuyOnlineInfo(array(array('goods_commonid' ,'=', $key)));
            if (!empty($groupbuy)) {
                // 更新价格
                $this->editGoods(array('goods_promotion_price' => $groupbuy['groupbuy_price'], 'goods_promotion_type' => 1), array('goods_commonid' => $key));
                continue;
            }
            foreach ($val as $k => $v) {
                // 查询秒杀时候进行
                $xianshigoods = $pxianshigoods_model->getXianshigoodsInfo(array(array('goods_id' ,'=', $k), array('xianshigoods_state','=',1), array('xianshigoods_starttime','<', TIMESTAMP), array('xianshigoods_end_time','>', TIMESTAMP)));
                if (!empty($xianshigoods)) {
                    // 更新价格
                    $this->editGoodsById(array('goods_promotion_price' => $xianshigoods['xianshigoods_price'], 'goods_promotion_type' => 2), $k);
                    continue;
                }

                // 没有促销使用原价
                $this->editGoodsById(array('goods_promotion_price' => Db::raw('goods_price'), 'goods_promotion_type' => 0), $k);
            }
        }
        return true;
    }

    /**
     * 更新商品数据
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommon($update, $condition) {
        $common_list = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($common_list)) {
            return false;
        }
        $commonid_array = array();
        foreach ($common_list as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        return $this->editGoodsCommonById($update, $commonid_array);
    }

    /**
     * 更新商品数据
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param int|array $commonid_array 商品ID
     * @return boolean|unknown
     */
    public function editGoodsCommonById($update, $commonid_array) {
        if (empty($commonid_array)) {
            return true;
        }
        $condition[]=array('goods_commonid','in', $commonid_array);
        $result = Db::name('goodscommon')->where($condition)->update($update);
        if ($result) {
            foreach ((array) $commonid_array as $val) {
                $this->_dGoodsCommonCache($val);
            }
        }
        return $result;
    }

    /**
     * 锁定商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommonLock($condition) {
        $update = array('goods_lock' => 1);
        return $this->editGoodsCommon($update, $condition);
    }
    /**
     * 锁定商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsLock($condition) {
        $update = array('goods_lock' => 1);
        return $this->editGoods($update, $condition);
    }
    /**
     * 解锁商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommonUnlock($condition) {
        $update = array('goods_lock' => 0);
        return $this->editGoodsCommon($update, $condition);
    }
    /**
     * 解锁商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsUnlock($condition) {
        $update = array('goods_lock' => 0);
        return $this->editGoods($update, $condition);
    }
    /**
     * 更新商品信息
     * @access public
     * @author csdeshang
     * @param array $condition 更新条件
     * @param array $update1 更新数据1
     * @param array $update2 更新数据2
     * @return boolean
     */
    public function editProduces($condition, $update1, $update2 = array()) {
        $update2 = empty($update2) ? $update1 : $update2;
        $goods_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        if (empty($goods_array)) {
            return true;
        }
        $commonid_array = array();
        foreach ($goods_array as $val) {
            $commonid_array[] = $val['goods_commonid'];
        }
        $return1 = $this->editGoodsCommonById($update1, $commonid_array);
        $return2 = $this->editGoods($update2, array(array('goods_commonid','in', $commonid_array)));
        if ($return1 && $return2) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 更新未锁定商品信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $update1 更新数据1
     * @param array $update2 更新数据2
     * @return boolean
     */
    public function editProducesNoLock($condition, $update1, $update2 = array()) {
        $condition[]=array('goods_lock','=',0);
        return $this->editProduces($condition, $update1, $update2);
    }

    /**
     * 商品下架
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOffline($condition) {
        $update = array('goods_state' => self::STATE0);
        return $this->editProducesNoLock($condition, $update);
    }

    /**
     * 商品上架
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOnline($condition) {
        $update = array('goods_state' => self::STATE1);
        // 禁售商品、审核失败商品不能上架。
        $condition[]=array('goods_state','=',self::STATE0);
        // 修改预约商品状态
        $update['is_appoint'] = 0;
        return $this->editProduces($condition, $update);
    }


    /**
     * 获取单条商品SKU信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @return array
     */
    public function getGoodsInfo($condition, $field = '*') {

        return Db::name('goods')->field($field)->where($condition)->find();
    }

  
    /**
     * 获取单条商品SKU信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return type
     */
    public function getGoodsOnlineInfoForShare($goods_id) {
        $goods_info = $this->getGoodsOnlineInfoAndPromotionById($goods_id);
        if (empty($goods_info)) {
            return array();
        }
        //抢购
        if (!empty($goods_info['groupbuy_info'])) {
            $goods_info['promotion_type'] = '抢购';
            $goods_info['promotion_price'] = $goods_info['groupbuy_info']['groupbuy_price'];
        }

        if (!empty($goods_info['xianshi_info'])) {
            $goods_info['promotion_type'] = '秒杀';
            $goods_info['promotion_price'] = $goods_info['xianshi_info']['xianshigoods_price'];
        }
        return $goods_info;
    }

    /**
     * 查询出售中的商品详细信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsOnlineInfoAndPromotionById($goods_id) {
        $goods_info = $this->getGoodsInfoAndPromotionById($goods_id);
        if (empty($goods_info) || $goods_info['goods_state'] != self::STATE1) {
            return array();
        }
        return $goods_info;
    }

    /**
     * 查询商品详细信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsInfoAndPromotionById($goods_id) {
        $goods_info = $this->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            return array();
        }
        $goods_info['groupbuy_info'] = '';
        $goods_info['pintuan_info'] = '';
        $goods_info['xianshi_info'] = '';
        $goods_info['mgdiscount_info'] = '';
        
        //原价
        $goods_info['goods_original_price']=$goods_info['goods_price'];
        //抢购
        if (config('ds_config.groupbuy_allow')) {
            $goods_info['groupbuy_info'] = model('groupbuy')->getGroupbuyInfoByGoodsCommonID($goods_info['goods_commonid']);
        }
        
        //拼团
        if (empty($goods_info['groupbuy_info'])) {
            $goods_info['pintuan_info'] = model('ppintuan')->getPintuanInfoByGoodsCommonID($goods_info['goods_commonid']);
        }

        //秒杀
        if (empty($goods_info['groupbuy_info']) && empty($goods_info['pintuan_info'])) {
            if (config('ds_config.promotion_allow') && empty($goods_info['groupbuy_info'])) {
                $goods_info['xianshi_info'] = model('pxianshigoods')->getXianshigoodsInfoByGoodsID($goods_info['goods_id']);
            }
        }
        
        //会员等级折扣
        if (empty($goods_info['groupbuy_info']) && empty($goods_info['pintuan_info']) && empty($goods_info['xianshi_info'])) {
            if (config('ds_config.mgdiscount_allow')) {
                $goods_info['mgdiscount_info'] = model('pmgdiscount')->getPmgdiscountInfoByGoodsInfo($goods_info);
            }
        }
        
        return $goods_info;
    }

    /**
     * 查询出售中的商品列表及其促销信息
     * @access public
     * @author csdeshang
     * @param array $goodsid_array 商品ID数组
     * @return array
     */
    public function getGoodsOnlineListAndPromotionByIdArray($goodsid_array) {
        if (empty($goodsid_array) || !is_array($goodsid_array))
            return array();

        $goods_list = array();
        foreach ($goodsid_array as $goods_id) {
            $goods_info = $this->getGoodsOnlineInfoAndPromotionById($goods_id);
            if (!empty($goods_info))
                $goods_list[] = $goods_info;
        }

        return $goods_list;
    }

    /**
     * 获取单条商品信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @return array
     */
    public function getGoodsCommonInfo($condition, $field = '*') {
        return Db::name('goodscommon')->field($field)->where($condition)->find();
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品ID
     * @return array
     */
    public function getGoodsCommonInfoByID($goods_commonid) {
        $common_info = $this->_rGoodsCommonCache($goods_commonid);
        if (empty($common_info)) {
            $common_info = $this->getGoodsCommonInfo(array('goods_commonid' => $goods_commonid));
            $this->_wGoodsCommonCache($goods_commonid, $common_info);
        }
        return $common_info;
    }

    /**
     * 获得商品SKU某字段的和
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @return boolean
     */
    public function getGoodsSum($condition, $field) {
        return Db::name('goods')->where($condition)->sum($field);
    }

    /**
     * 获得商品SKU数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getGoodsCount($condition) {
        return Db::name('goods')->where($condition)->count();
    }

    /**
     * 获得出售中商品SKU数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @return int
     */
    public function getGoodsOnlineCount($condition, $field = '*') {
        $condition[]=array('goods_state','=',self::STATE1);
        return Db::name('goods')->where($condition)->count($field);
    }

    /**
     * 获得商品数量
     * @access public
     * @author csdeshang
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonCount($condition) {
        return Db::name('goodscommon')->where($condition)->count();
    }

    /**
     * 出售中的商品数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getGoodsCommonOnlineCount($condition) {
        $condition[]=array('goods_state','=',self::STATE1);
        return $this->getGoodsCommonCount($condition);
    }



    /**
     * 商品图片列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $order 字段
     * @param string $field 排序
     * @return array
     */
    public function getGoodsImageList($condition, $field = '*', $order = 'goodsimage_isdefault desc,goodsimage_sort asc') {
        return Db::name('goodsimages')->field($field)->where($condition)->order($order)->select()->toArray();
    }

    /**
     * 删除商品SKU信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delGoods($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid');
        if (!empty($goods_list)) {
            $goodsid_array = array();
            // 删除商品二维码
            foreach ($goods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
                // 删除商品缓存
                $this->_dGoodsCache($val['goods_id']);
                // 删除商品规格缓存
                $this->_dGoodsSpecCache($val['goods_commonid']);
            }
            // 删除属性关联表数据
            Db::name('goodsattrindex')->where(array(array('goods_id','in', $goodsid_array)))->delete();
            // 删除优惠套装商品
            model('pbundling')->delBundlingGoods(array(array('goods_id','in', $goodsid_array)));
            // 优惠套餐活动下架
            model('pbundling')->editBundlingCloseByGoodsIds(array(array('goods_id','in', $goodsid_array)));
            // 秒杀
            model('pxianshigoods')->delXianshigoods(array(array('goods_id','in', $goodsid_array)));
            //删除商品浏览记录
            model('goodsbrowse')->delGoodsbrowse(array(array('goods_id','in', $goodsid_array)));
            // 删除买家收藏表数据
            model('favorites')->delFavorites(array(array('fav_id','in', $goodsid_array)));
            // 删除商品赠品
            model('goodsgift')->delGoodsgift(array(array('goods_id','in', $goodsid_array)));
            model('goodsgift')->delGoodsgift(array(array('gift_goodsid','in', $goodsid_array)));
            // 删除推荐组合
            model('goodscombo')->delGoodscombo(array(array('goods_id','in', $goodsid_array)));
            model('goodscombo')->delGoodscombo(array(array('combo_goodsid','in', $goodsid_array)));
        }
        return Db::name('goods')->where($condition)->delete();
    }
    /**
     * 编辑商品图片表信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsImages($update, $condition) {
        $result = Db::name('goodsimages')->where($condition)->update($update);
        return $result;
    }
    /**
     * 删除商品图片表信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delGoodsImages($condition) {
        $image_list = $this->getGoodsImageList($condition, 'goods_commonid,color_id');
        if (empty($image_list)) {
            return true;
        }
        $result = Db::name('goodsimages')->where($condition)->delete();
        if ($result) {
            foreach ($image_list as $val) {
                $this->_dGoodsImageCache($val['goods_commonid'] . '|' . $val['color_id']);
            }
        }
        return $result;
    }

    /**
     * 商品删除及相关信息
     * @access public
     * @author csdeshang
     * @param  array $condition 列表条件
     * @return boolean
     */
    public function delGoodsAll($condition) {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid');
        if (empty($goods_list)) {
            return false;
        }
        $goodsid_array = array();
        $commonid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
            $commonid_array[] = $val['goods_commonid'];
            // 商品公共缓存
            $this->_dGoodsCommonCache($val['goods_commonid']);
            // 商品规格缓存
            $this->_dGoodsSpecCache($val['goods_commonid']);
        }
        $commonid_array = array_unique($commonid_array);

        // 删除商品表数据
        $this->delGoods(array(array('goods_id','in', $goodsid_array)));
        // 删除商品公共表数据
        Db::name('goodscommon')->where(array(array('goods_commonid','in', $commonid_array)))->delete();
        // 删除商品图片表数据
        $this->delGoodsImages(array(array('goods_commonid','in', $commonid_array)));
        // 删除商品F码

        model('goodsfcode')->delGoodsfcode(array(array('goods_commonid','in', $commonid_array)));

        return true;
    }

    /**删除未锁定商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return type
     */
    public function delGoodsNoLock($condition) {
        $condition[]=array('goods_lock','=',0);
        $common_array = $this->getGoodsCommonList($condition, 'goods_commonid', 0);
        $common_array = array_under_reset($common_array, 'goods_commonid');
        $commonid_array = array_keys($common_array);
        return $this->delGoodsAll(array(array('goods_commonid','in', $commonid_array)));
    }


    /**
     * 获得商品子分类的ID
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return array
     */
    public function _getRecursiveClass($condition,$gc_id,$prefix='') {
        if (!is_array($gc_id)) {
            $gc_list = model('goodsclass')->getGoodsclassForCacheModel();
            if (!empty($gc_list[$gc_id])) {
                $all_gc_id=array($gc_id);
                $gcchild_id = empty($gc_list[$gc_id]['child']) ? array() : explode(',', $gc_list[$gc_id]['child']);
                $gcchildchild_id = empty($gc_list[$gc_id]['childchild']) ? array() : explode(',', $gc_list[$gc_id]['childchild']);
                $all_gc_id = array_merge($all_gc_id, $gcchild_id, $gcchildchild_id);
                if($prefix){
                    $prefix=$prefix.'.';
                }
                $condition[] = array($prefix.'gc_id','in', implode(',', $all_gc_id));
            }
        }
        return $condition;
    }

    /**
     * 由ID取得在售单个虚拟商品信息
     * @access public
     * @author csdeshang
     * @param array $goods_id 商品ID
     * @return array
     */
    public function getVirtualGoodsOnlineInfoByID($goods_id) {
        $goods_info = $this->getGoodsInfoByID($goods_id);
        return $goods_info['is_virtual'] == 1 && $goods_info['virtual_indate'] >= TIMESTAMP ? $goods_info : array();
    }

    /**
     * 取得商品详细信息（优先查询缓存）（在售）
     * 如果未找到，则缓存所有字段
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsOnlineInfoByID($goods_id) {
        $goods_info = $this->getGoodsInfoByID($goods_id);
        if ($goods_info['goods_state'] != self::STATE1) {
            $goods_info = array();
        }
        return $goods_info;
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsInfoByID($goods_id) {
        $goods_info = $this->_rGoodsCache($goods_id);
        if (empty($goods_info)) {
            $goods_info = $this->getGoodsInfo(array('goods_id' => $goods_id));
            $this->_wGoodsCache($goods_id, $goods_info);
        }
        return $goods_info;
    }

    /**
     * 验证是否为普通商品
     * @access public
     * @author csdeshang
     * @param array $goods 商品数组
     * @return boolean
     */
    public function checkIsGeneral($goods) {
        if ($goods['is_virtual'] == 1 || $goods['is_goodsfcode'] == 1 || $goods['is_presell'] == 1) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否允许送赠品
     * @access public
     * @author csdeshang
     * @param type $goods 商品
     * @return boolean
     */
    public function checkGoodsIfAllowGift($goods) {
        if ($goods['is_virtual'] == 1) {
            return false;
        }
        return true;
    }
    /**
     * 验证是否允许关联套餐
     * @access public
     * @author csdeshang
     * @param type $goods 商品
     * @return boolean
     */
    public function checkGoodsIfAllowCombo($goods) {
        if ($goods['is_virtual'] == 1 || $goods['is_goodsfcode'] == 1 || $goods['is_presell'] == 1 || $goods['is_appoint'] == 1) {
            return false;
        }
        return true;
    }
    /**
     * 获得有库存的商品
     * @access public
     * @author csdeshang
     * @param type $common_id ID编号
     * @return type
     */
    public function getGoodsStorageByCommonId($common_id) {
        $condition = array();
        $condition[] = array('goods_state', '=', self::STATE1);
        $condition[] = array('goods_commonid', '=', $common_id);
        $condition[] = array('goods_storage', '>', 0);
        $goods_info = $this->getGoodsInfo($condition);

        return $goods_info;
    }

    /**
     * 获得商品规格数组
     * @access public
     * @author csdeshang
     * @param type $common_id ID编号
     * @return type
     */
    public function getGoodsSpecListByCommonId($common_id) {
        $spec_list = $this->_rGoodsSpecCache($common_id);
        if (empty($spec_list)) {
            $spec_array = $this->getGoodsList(array('goods_commonid' => $common_id), 'goods_spec,goods_id,goods_image,color_id,goods_storage');
            $spec_list['spec'] = serialize($spec_array);
            $this->_wGoodsSpecCache($common_id, $spec_list);
        }
        $spec_array = unserialize($spec_list['spec']);
        return $spec_array;
    }

    /**
     * 获得商品图片数组
     * @access public
     * @author csdeshang
     * @param type $key 键值
     * @return type
     */
    public function getGoodsImageByKey($key) {
        $image_list = $this->_rGoodsImageCache($key);
        if (empty($image_list)) {
            $array = explode('|', $key);
            list($common_id, $color_id) = $array;
            $image_more = $this->getGoodsImageList(array('goods_commonid' => $common_id, 'color_id' => $color_id), 'goodsimage_url');
            $image_list['image'] = serialize($image_more);
            $this->_wGoodsImageCache($key, $image_list);
        }
        $image_more = unserialize($image_list['image']);
        return $image_more;
    }

    /**
     * 读取商品缓存
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品id
     * @return type
     */
    private function _rGoodsCache($goods_id) {
        return rcache($goods_id, 'goods');
    }

    /**
     * 写入商品缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品id
     * @param array $goods_info 商品信息
     * @return boolean
     */
    private function _wGoodsCache($goods_id, $goods_info) {
        return wcache($goods_id, $goods_info, 'goods');
    }

    /**
     * 删除商品缓存
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品id
     * @return boolean
     */
    private function _dGoodsCache($goods_id) {
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_TAOBAO . DIRECTORY_SEPARATOR . 'goods_csv_' . $goods_id . '.zip');
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_TAOBAO . DIRECTORY_SEPARATOR . 'goods_image_' . $goods_id . '.zip');
        return dcache($goods_id, 'goods');
    }

    /**
     * 读取商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品id
     * @return array
     */
    private function _rGoodsCommonCache($goods_commonid) {
        return rcache($goods_commonid, 'goodscommon');
    }

    /**
     * 写入商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品ID
     * @param array $common_info 商品信息
     * @return boolean
     */
    private function _wGoodsCommonCache($goods_commonid, $common_info) {
        return wcache($goods_commonid, $common_info, 'goodscommon');
    }

    /**
     * 删除商品公共缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品ID
     * @return boolean
     */
    private function _dGoodsCommonCache($goods_commonid) {
        return dcache($goods_commonid, 'goodscommon');
    }

    /**
     * 读取商品规格缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品id
     * @return array
     */
    private function _rGoodsSpecCache($goods_commonid) {
        return rcache($goods_commonid, 'goods_spec');
    }

    /**
     * 写入商品规格缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品id
     * @param array $spec_list 规格列表
     * @return boolean
     */
    private function _wGoodsSpecCache($goods_commonid, $spec_list) {
        return wcache($goods_commonid, $spec_list, 'goods_spec');
    }

    /**
     * 删除商品规格缓存
     * @access public
     * @author csdeshang
     * @param int $goods_commonid 商品id
     * @return boolean
     */
    private function _dGoodsSpecCache($goods_commonid) {
        return dcache($goods_commonid, 'goods_spec');
    }

    /**
     * 读取商品图片缓存
     * @access public
     * @author csdeshang
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @return array
     */
    private function _rGoodsImageCache($key) {
        return rcache($key, 'goods_image');
    }

    /**
     * 写入商品图片缓存
     * @access public
     * @author csdeshang
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @param array $image_list 图片列表
     * @return boolean
     */
    private function _wGoodsImageCache($key, $image_list) {
        return wcache($key, $image_list, 'goods_image');
    }

    /**
     * 删除商品图片缓存
     * @access public
     * @author csdeshang
     * @param int $key ($goods_commonid .'|'. $color_id)
     * @return boolean
     */
    private function _dGoodsImageCache($key) {
        return dcache($key, 'goods_image');
    }

    /**
     * 获取单条商品信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID 
     * @return array
     */
    public function getGoodsDetail($goods_id) {
        if ($goods_id <= 0) {
            return null;
        }
        $result1 = $this->getGoodsInfoAndPromotionById($goods_id);

        if (empty($result1)) {
            return null;
        }
        $result2 = $this->getGoodsCommonInfoByID($result1['goods_commonid']);
        $goods_info = array_merge($result2, $result1);

        $goods_info['spec_value'] = unserialize($goods_info['spec_value']);
        $goods_info['spec_name'] = unserialize($goods_info['spec_name']);
        $goods_info['goods_spec'] = unserialize($goods_info['goods_spec']);
        $goods_info['goods_attr'] = unserialize($goods_info['goods_attr']);

        // 手机商品描述
        if ($goods_info['mobile_body'] != '') {
            $mobile_body_array = unserialize($goods_info['mobile_body']);
            if (is_array($mobile_body_array)) {
                $mobile_body = '';
                foreach ($mobile_body_array as $val) {
                    switch ($val['type']) {
                        case 'text':
                            $mobile_body .= '<div>' . $val['value'] . '</div>';
                            break;
                        case 'image':
                            $mobile_body .= '<img src="' . $val['value'] . '">';
                            break;
                    }
                }
                $goods_info['mobile_body'] = $mobile_body;
            }
        }
        
        // 查询所有规格商品
        $spec_array = $this->getGoodsSpecListByCommonId($goods_info['goods_commonid']);
        $spec_list = array();       // 各规格商品地址，js使用
        $spec_list_mobile = array();       // 各规格商品地址，js使用
        $spec_image = array();      // 各规格商品主图，规格颜色图片使用
        foreach ($spec_array as $key => $value) {
            $s_array = unserialize($value['goods_spec']);
            $tmp_array = array();
            if (!empty($s_array) && is_array($s_array)) {
                foreach ($s_array as $k => $v) {
                    $tmp_array[] = $k;
                }
            }
            sort($tmp_array);
            $spec_sign = implode('|', $tmp_array);
            $tpl_spec = array();
            $tpl_spec['stock'] = $value['goods_storage'];
            $tpl_spec['sign'] = $spec_sign;
            $tpl_spec['url'] = (string)url('home/Goods/index', ['goods_id' => $value['goods_id']]);
            $spec_list[] = $tpl_spec;
            $spec_list_mobile[$spec_sign] = $value['goods_id'];
            $spec_image[$value['color_id']] = goods_thumb($value, 240);
        }
        $spec_list = json_encode($spec_list);

        // 商品多图
        $image_more = $this->getGoodsImageByKey($goods_info['goods_commonid'] . '|' . $goods_info['color_id']);
        $goods_image = array();
        $goods_image_mobile = array();
        if (!empty($image_more)) {
            foreach ($image_more as $val) {
                $goods_image[] = array(goods_cthumb($val['goodsimage_url'], 240), goods_cthumb($val['goodsimage_url'], 480), goods_cthumb($val['goodsimage_url'], 1280));
                $goods_image_mobile[] = goods_cthumb($val['goodsimage_url'], 480);
            }
        } else {
            $goods_image[] = array(goods_thumb($goods_info,240),goods_thumb($goods_info,480),goods_thumb($goods_info,1280));
            $goods_image_mobile[] = goods_thumb($goods_info, 480);
        }

        //抢购
        if (!empty($goods_info['groupbuy_info'])) {
            $goods_info['promotion_type'] = 'groupbuy';
            $goods_info['title'] = '抢购';
            $goods_info['remark'] = $goods_info['groupbuy_info']['groupbuy_remark'];
            $goods_info['promotion_price'] = $goods_info['groupbuy_info']['groupbuy_price'];
            $goods_info['down_price'] = ds_price_format($goods_info['goods_price'] - $goods_info['groupbuy_info']['groupbuy_price']);
            $goods_info['upper_limit'] = $goods_info['groupbuy_info']['groupbuy_upper_limit'];
            $goods_info['promotion_end_time'] = $goods_info['groupbuy_info']['groupbuy_endtime'];
            unset($goods_info['groupbuy_info']);
        }

        //秒杀
        if (!empty($goods_info['xianshi_info'])) {
            $goods_info['promotion_type'] = 'xianshi';
            $goods_info['title'] = $goods_info['xianshi_info']['xianshi_title'];
            $goods_info['remark'] = $goods_info['xianshi_info']['xianshi_title'];
            $goods_info['promotion_price'] = $goods_info['xianshi_info']['xianshigoods_price'];
            $goods_info['down_price'] = ds_price_format($goods_info['goods_price'] - $goods_info['xianshi_info']['xianshigoods_price']);
            $goods_info['lower_limit'] = $goods_info['xianshi_info']['xianshigoods_lower_limit'];
            $goods_info['explain'] = $goods_info['xianshi_info']['xianshi_explain'];
            $goods_info['promotion_end_time'] = $goods_info['xianshi_info']['xianshigoods_end_time'];
            unset($goods_info['xianshi_info']);
        }
        //拼团
        if (!empty($goods_info['pintuan_info'])) {
            $goods_info['pintuan_type'] = 'pintuan';
            $goods_info['pintuan_id'] = $goods_info['pintuan_info']['pintuan_id'];
            $goods_info['pintuan_title'] = $goods_info['pintuan_info']['pintuan_name'];
            $goods_info['pintuan_price'] = round($goods_info['pintuan_info']['pintuan_zhe'] * $goods_info['goods_price'] / 10, 2);
            $goods_info['pintuan_limit_number'] = $goods_info['pintuan_info']['pintuan_limit_number'];
            $goods_info['pintuan_limit_hour'] = $goods_info['pintuan_info']['pintuan_limit_hour'];
            $goods_info['pintuan_limit_quantity'] = $goods_info['pintuan_info']['pintuan_limit_quantity'];
            $goods_info['pintuan_end_time'] = $goods_info['pintuan_info']['pintuan_end_time'];
            //拼团开团信息
            $goods_info['pintuangroup_list'] = $goods_info['pintuan_info']['pintuangroup_list'];
            $goods_info['pintuangroup_count'] = count($goods_info['pintuangroup_list']);
            unset($goods_info['pintuan_info']);
        }
        
        //会员等级折扣
        if (!empty($goods_info['mgdiscount_info'])) {
            $goods_info['mgdiscount_type'] = 'mgdiscount';
            $goods_info['goods_mgdiscount_arr'] = $goods_info['mgdiscount_info'];
            unset($goods_info['mgdiscount_info']);
        }
        
        // 验证是否允许送赠品
        $gift_array=array();
        if ($this->checkGoodsIfAllowGift($goods_info)) {
            $gift_array = model('goodsgift')->getGoodsgiftListByGoodsId($goods_id);
            if (!empty($gift_array)) {
                $goods_info['is_have_gift'] = 'gift';
            }
        }

        // 加入购物车按钮
        $goods_info['cart'] = true;
        //虚拟、F码、预售不显示加入购物车
        if ($goods_info['is_virtual'] == 1 || $goods_info['is_goodsfcode'] == 1 || $goods_info['is_presell'] == 1) {
            $goods_info['cart'] = false;
        }

        // 立即购买文字显示
        $goods_info['buynow_text'] = '立即购买';
        if ($goods_info['is_presell'] == 1) {
            $goods_info['buynow_text'] = '预售购买';
        } elseif ($goods_info['is_goodsfcode'] == 1) {
            $goods_info['buynow_text'] = 'F码购买';
        }
        $mansong_info=model('pmansong')->getMansongInfoByID();
        if(empty($mansong_info)){
            $mansong_info=array();
        }
        //满即送
        $mansong_info = ($goods_info['is_virtual'] == 1) ? array() : $mansong_info;

        // 商品受关注次数加1
        $goods_info['goods_click'] = intval($goods_info['goods_click']) + 1;
        if (config('ds_config.cache_open')) {
            wcache('updateRedisDate', array($goods_id => $goods_info['goods_click']), 'goodsClick');
        } else {
            $this->editGoodsById(array('goods_click' => Db::raw('goods_click+1')), $goods_id);
        }
        $result = array();
        $result['goods_info'] = $goods_info;
        $result['spec_list'] = $spec_list;
        $result['spec_list_mobile'] = $spec_list_mobile;
        $result['spec_image'] = $spec_image;
        $result['goods_image'] = $goods_image;
        $result['goods_image_mobile'] = $goods_image_mobile;
        $result['mansong_info'] = $mansong_info;
        $result['gift_array'] = $gift_array;
        return $result;
    }
    /**
     * 获取移动端商品
     * @access public
     * @author csdeshang
     * @param type $goods_commonid 商品ID
     * @return array
     */
    public function getMobileBodyByCommonID($goods_commonid) {
        $common_info = $this->_rGoodsCommonCache($goods_commonid);
        if (empty($common_info)) {
            $common_info = $this->getGoodsCommonInfo(array('goods_commonid' => $goods_commonid));
            $this->_wGoodsCommonCache($goods_commonid, $common_info);
        }


        // 手机商品描述
        if ($common_info['mobile_body'] != '') {
            $mobile_body_array = unserialize($common_info['mobile_body']);
            if (is_array($mobile_body_array)) {
                $mobile_body = '';
                foreach ($mobile_body_array as $val) {
                    switch ($val['type']) {
                        case 'text':
                            $mobile_body .='<div>' . $val['value'] . '</div>';
                            break;
                        case 'image':
                            $mobile_body .='<img src="' . $val['value'] . '">';
                            break;
                    }
                }
                $common_info['mobile_body'] = $mobile_body;
            }
        }
        return $common_info;
    }
    /**
     * 存入仓库
     * @access public
     * @author csdeshang
     * @param array $update 数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesLockUp($update, $condition,$type) {
        if ($type == 'reduction'){
            $update_param['goods_state'] = self::STATE1;
        }else{
            $update_param['goods_state'] = self::STATE10;
        }
        $update = array_merge($update, $update_param);
        $return = $this->editProduces($condition, $update, $update_param);
        if ($return) {
            return true;
        } else {
            return false;
        }
    }
    


    /**
     * 下单变更库存销量
     * @param unknown $goods_buy_quantity
     */
    public function createOrderUpdateStorage($goods_buy_quantity)
    {
        foreach ($goods_buy_quantity as $goods_id => $quantity) {
            $data = array();
            $data['goods_storage'] = Db::raw('goods_storage-'.$quantity);
            $data['goods_salenum'] = Db::raw('goods_salenum+'.$quantity);
            $condition=array();
            $condition[]=array('goods_id','=',$goods_id);
            $condition[]=array('goods_storage','>',0);
            $result = $this->editGoods($data, $condition);
        }
        if (!$result) {
            return ds_callback(false, '变更商品库存与销量失败');
        }
        else {
            return ds_callback(true);
        }
    }

    /**
     * 取消订单变更库存销量
     * @param unknown $goods_buy_quantity
     */
    public function cancelOrderUpdateStorage($goods_buy_quantity)
    {
        foreach ($goods_buy_quantity as $goods_id => $quantity) {
            $data = array();
            $data['goods_storage'] = Db::raw('goods_storage+'.$quantity);
            $data['goods_salenum'] = Db::raw('goods_salenum-'.$quantity);
            $result = $this->editGoodsById($data, $goods_id);
            if (!$result) {
                return ds_callback(false, '变更商品库存与销量失败');
            }
        }
            return ds_callback(true);
    }

}