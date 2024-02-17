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
class Goodsclassstaple extends BaseModel {

    /**
     * 常用分类列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $order 排序
     * @param string $field 字段
     * @param int $limit 限制
     * @return array 二维数组
     */
    public function getGoodsclassstapleList($condition, $field = '*', $order = 'staple_counter desc', $limit = 20) {
        $result = Db::name('goodsclassstaple')->field($field)->where($condition)->order($order)->limit($limit)->select()->toArray();
        return $result;
    }

    /**
     * 一条记录
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @param string $field 字段
     * @return array 一维数组结构的返回结果
     */
    public function getGoodsclassstapleInfo($condition, $field = '*') {
        $result = Db::name('goodsclassstaple')->field($field)->where($condition)->find();
        return $result;
    }

    /**
     * 添加常用分类，如果已存在计数器+1
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @return boolean
     */
    public function autoIncrementStaple($data) {
        $where = array(
            'gc_id_1' => intval($data['gc_id_1']),
            'gc_id_2' => intval($data['gc_id_2']),
            'gc_id_3' => intval($data['gc_id_3']),
        );
        $staple_info = $this->getGoodsclassstapleInfo($where);
        if (empty($staple_info)) {
            $insert = array(
                'staple_name' => $data['gctag_name'],
                'gc_id_1' => intval($data['gc_id_1']),
                'gc_id_2' => intval($data['gc_id_2']),
                'gc_id_3' => intval($data['gc_id_3']),
                'type_id' => $data['type_id'],
            );
            $this->addGoodsclassstaple($insert);
        } else {
            $update = array('staple_counter' => Db::raw('staple_counter+1'));
            $where = array('staple_id' => $staple_info['staple_id']);
            $this->editGoodsclassstaple($update, $where);
        }
        return true;
    }

    /**
     * 新增
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean 布尔类型的返回结果
     */
    public function addGoodsclassstaple($data) {
        $result = Db::name('goodsclassstaple')->insertGetId($data);
        return $result;
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update 更新内容
     * @param array $where 条件
     * @return boolean
     */
    public function editGoodsclassstaple($update, $where) {
        $result = Db::name('goodsclassstaple')->where($where)->update($update);
        return $result;
    }

    /**
     * 删除常用分类
     * @access public
     * @author csdeshang
     * @param array $condtion 条件
     * @return boolean
     */
    public function delGoodsclassstaple($condtion) {
        $result = Db::name('goodsclassstaple')->where($condtion)->delete();
        return $result;
    }

}

?>
