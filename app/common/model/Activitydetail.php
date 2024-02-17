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
class Activitydetail extends BaseModel
{
    public $page_info;
    /**
     * 添加
     * @author csdeshang
     * @param array $data
     * @return bool
     */
    public function addActivitydetail($data){
        return Db::name('activitydetail')->insertGetId($data);
    }

    /**
     * 根据条件更新
     * @author csdeshang
     * @param array $data 更新内容
     * @param array $condition 更新条件
     * @return bool
     */
    public function editActivitydetail($data,$condition){
        return Db::name('activitydetail')->where($condition)->update($data);
    }

    /**
     * 根据条件删除
     * @author csdeshang
     * @param array $condition 条件数组
     * @return bool
     */
    public function delActivitydetail($condition){
        return Db::name('activitydetail')->where($condition)->delete();
    }
    /**
     * 根据条件查询活动内容信息
     * @author csdeshang
     * @param array $condition 查询条件数组
     * @param obj $pagesize	分页页数
     * @param string $order 排序
     * @return array 二维数组
     */
    public function getActivitydetailList($condition,$pagesize='',$order='activitydetail_sort desc'){
        if ($pagesize) {
            $res = Db::name('activitydetail')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        }else{
            return Db::name('activitydetail')->where($condition)->order($order)->select()->toArray();
        }
    }
    /**
     * 根据条件查询活动商品内容信息
     * @author csdeshang
     * @param array $condition 查询条件数组
     * @param obj $pagesize	分页页数
     * @param string $order 排序
     * @return array 二维数组
     */
    public function getGoodsJoinList($condition,$pagesize='',$order=''){
        $field	= 'activitydetail.*,goods.*';
        if ($pagesize) {
            $res=Db::name('activitydetail')->alias('activitydetail')->join('goods goods','activitydetail.item_id=goods.goods_id')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            return $res->items();
        }else{
            return Db::name('activitydetail')->alias('activitydetail')->join('goods goods','activitydetail.item_id=goods.goods_id')->field($field)->where($condition)->order($order)->select()->toArray();
        }
    }
    /**
     * 查询活动商品信息
     * @author csdeshang
     * @param array $condition 查询条件数组
     * @return array 二维数组
     */
    public function getActivitydetailAndGoodsList($condition){
        $field	= 'activitydetail.activitydetail_sort,goods.goods_id,goods.goods_name,goods.goods_price,goods.goods_image';
        $res= Db::name('activitydetail')->alias('activitydetail')->join('goods goods','activitydetail.item_id=goods.goods_id')->field($field)->where($condition)->select()->toArray();
        return $res;
    }
}