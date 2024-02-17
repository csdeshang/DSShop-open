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
class Favorites extends BaseModel {

    public $page_info;

    /**
     * 收藏列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param string $field 查询字段
     * @param int $pagesize 分页信息
     * @param string $order 排序
     * @return array
     */
    public function getFavoritesList($condition, $field = '*', $pagesize = 0, $order = 'favlog_id desc') {
        if ($pagesize) {
        $res = Db::name('favorites')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
        $this->page_info = $res;
        return $res->items();
        } else {
            return Db::name('favorites')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 收藏商品列表
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @param string $field 字段
     * @param int $pagesize 分页信息
     * @param string $order 排序
     * @return array
     */
    public function getGoodsFavoritesList($condition, $field = '*', $pagesize = 0, $order = 'favlog_id desc') {
        return $this->getFavoritesList($condition, $field, $pagesize, $order);
    }

    /**
     * 取单个收藏的内容
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array 数组类型的返回结果
     */
    public function getOneFavorites($condition) {
        return Db::name('favorites')->where($condition)->find();
    }

    /**
     * 获取商品收藏数
     * @access public
     * @author csdeshang
     * @param int $goodsId 商品ID
     * @param int $memberId 会员ID
     * @return int
     */
    public function getGoodsFavoritesCountByGoodsId($goodsId, $memberId = 0) {
        $condition = array();
        $condition[] = array('fav_id','=',$goodsId);
        if ($memberId > 0) {
            $condition[] = array('member_id','=',$memberId);
        }

        return (int) Db::name('favorites')->where($condition)->count();
    }

    /**
     * 新增收藏
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addFavorites($data) {
        if (empty($data)) {
            return false;
        }
        $goods_id = intval($data['fav_id']);
        $goods_model = model('goods');
        $goods = $goods_model->getGoodsInfoByID($goods_id);
        $data['goods_name'] = $goods['goods_name'];
        $data['goods_image'] = $goods['goods_image'];
        $data['favlog_price'] = $goods['goods_promotion_price']; //商品收藏时价格
        $data['favlog_msg'] = $goods['goods_promotion_price']; //收藏备注，默认为收藏时价格，可修改
        $data['gc_id'] = $goods['gc_id'];
        return Db::name('favorites')->insertGetId($data);
    }

    /**
     * 修改记录
     * @access public
     * @author csdeshang
     * @param type $condition 修改条件
     * @param type $data 修改数据
     * @return boolean
     */
    public function editFavorites($condition, $data) {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = Db::name('favorites')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return bool 布尔类型的返回结果
     */
    public function delFavorites($condition) {
        if (empty($condition)) {
            return false;
        }
        return Db::name('favorites')->where($condition)->delete();
    }

    /**
     * 获取商品收藏数
     * @access public
     * @author csdeshang
     * @param int $id 会员ID
     * @return int
     */
    public function getGoodsFavoritesCountByMemberId($id) {
        return Db::name('favorites')->where('member_id',$id)->count();
    }

}
