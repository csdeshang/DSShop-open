<?php

/**
 * 满即送活动规则模型 
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
class Pmansongrule extends BaseModel {

    
    /**
     * 读取满即送规则列表
     * @access public
     * @author csdeshang
     * @param type $mansong_id  满即送ID
     * @return type
     */
    public function getMansongruleListByID($mansong_id) {
        $condition = array();
        $condition[] = array('mansong_id','=',$mansong_id);
        $mansong_rule_list = Db::name('pmansongrule')->where($condition)->order('mansongrule_price desc')->select()->toArray();
        if (!empty($mansong_rule_list)) {
            $goods_model = model('goods');

            for ($i = 0, $j = count($mansong_rule_list); $i < $j; $i++) {
                $goods_id = intval($mansong_rule_list[$i]['goods_id']);
                if (!empty($goods_id)) {
                    $goods_info = $goods_model->getGoodsOnlineInfoByID($goods_id);
                    if (!empty($goods_info)) {
                        if (empty($mansong_rule_list[$i]['mansong_goods_name'])) {
                            $mansong_rule_list[$i]['mansong_goods_name'] = $goods_info['goods_name'];
                        }
                        $mansong_rule_list[$i]['goods_image'] = $goods_info['goods_image'];
                        $mansong_rule_list[$i]['goods_image_url'] = goods_cthumb($goods_info['goods_image']);
                        $mansong_rule_list[$i]['goods_storage'] = $goods_info['goods_storage'];
                        $mansong_rule_list[$i]['goods_id'] = $goods_id;
                        $mansong_rule_list[$i]['goods_url'] = (string)url('Goods/index', array('goods_id' => $goods_id));
                    }else{
                        $mansong_rule_list[$i]['goods_id'] = 0;
                    }
                }
            }
        }
        return $mansong_rule_list;
    }

    /**
     * 增加 
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return bool
     */
    public function addMansongrule($data) {
        return Db::name('pmansongrule')->insertGetId($data);
    }

    /**
     * 批量增加 
     * @access public
     * @author csdeshang
     * @param array $array 参数内容
     * @return bool
     */
    public function addMansongruleArray($array) {
        return Db::name('pmansongrule')->insertAll($array);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delMansongrule($condition) {
        return Db::name('pmansongrule')->where($condition)->delete();
    }

}
