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
class Mailtemplates extends BaseModel {
    
    /**
     * 取单条信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $fields 字段
     */
    public function getTplInfo($condition = array(), $fields = '*') {
        return Db::name('mailmsgtemlates')->where($condition)->field($fields)->find();
    }

    /**
     * 模板列表
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @return array 数组形式的返回结果
     */
    public function getTplList($condition = array()) {
        return Db::name('mailmsgtemlates')->where($condition)->select()->toArray();
    }
    
    /**
     * 编辑模板
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @param type $condition 条件
     * @return type
     */
    public function editTpl($data = array(), $condition = array()) {
        return Db::name('mailmsgtemlates')->where($condition)->update($data);
    }

}

?>
