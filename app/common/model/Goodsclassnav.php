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
class Goodsclassnav extends BaseModel {

    /**
     * 根据商品分类id取得数据
     * @access public
     * @author csdeshang
     * @param num $gc_id 分类ID
     * @return array
     */
    public function getGoodsclassnavInfoByGcId($gc_id) {
        return Db::name('goodsclassnav')->where(array('gc_id' => $gc_id))->find();
    }

    /**
     * 保存分类导航设置
     * @access public
     * @author csdeshang
     * @param type $data 更新数据
     * @return type
     */
    public function addGoodsclassnav($data) {
        return Db::name('goodsclassnav')->insert($data);
    }
    /**
     * 编辑存分类导航设置
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param int $gc_id 分类id
     * @return boolean
     */
    public function editGoodsclassnav($update, $gc_id) {
        return Db::name('goodsclassnav')->where(array('gc_id' => $gc_id))->update($update);
    }
}
?>
