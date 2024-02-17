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
class Consulttype extends BaseModel
{
    /**
     * 咨询类型列表
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @param string $field 字段
     * @param string $order 排序
     * @return array
     */
    public function getConsulttypeList($condition, $field = '*', $order = 'consulttype_sort asc,consulttype_id desc')
    {
        return Db::name('consulttype')->where($condition)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 单条咨询类型
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @param string $field 字段
     * @return array
     */
    public function getConsulttypeInfo($condition, $field = '*')
    {
        return Db::name('consulttype')->where($condition)->field($field)->find();
    }

    /**
     * 添加咨询类型
     * @access public
     * @author csdeshang 
     * @param array $data 参数内容
     * @return bool
     */
    public function addConsulttype($data)
    {
        return Db::name('consulttype')->insertGetId($data);
    }

    /**
     * 编辑咨询类型
     * @access public
     * @author csdeshang 
     * @param array $condition 检索条件
     * @param array $update 更新数据
     * @return boolean
     */
    public function editConsulttype($condition, $update)
    {
        return Db::name('consulttype')->where($condition)->update($update);
    }

    /**
     * 删除咨询类型
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delConsulttype($condition)
    {
        return Db::name('consulttype')->where($condition)->delete();
    }
}