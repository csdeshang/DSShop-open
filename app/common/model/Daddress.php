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
class Daddress extends BaseModel
{
    


    /**
     * 新增
     * @access public
     * @author csdeshang 
     * @param type $data 数据
     * @return type
     */
    public function addDaddress($data) {
        return Db::name('daddress')->insertGetId($data);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang 
     * @param type $condition 条件
     * @return type
     */
    public function delDaddress($condition) {
        return Db::name('daddress')->where($condition)->delete();
    }
    /**
     * 编辑更新
     * @access public
     * @author csdeshang 
     * @param type $data 更新数据
     * @param type $condition 条件
     * @return type
     */
    public function editDaddress($data, $condition) {
        return Db::name('daddress')->where($condition)->update($data);
    }


    /**
     * 查询单条
     * @access public
     * @author csdeshang 
     * @param type $condition 检索条件
     * @param type $fields 字段
     * @return type
     */
    public function getAddressInfo($condition, $fields = '*') {
        return Db::name('daddress')->field($fields)->where($condition)->find();
    }

    /**
     * 查询多条
     * @access public
     * @author csdeshang 
     * @param type $condition 条件
     * @param type $fields 字段
     * @param type $order 排序
     * @param type $limit 限制
     * @return type
     */
    public function getAddressList($condition, $fields = '*', $order = '', $limit = 0) {
        return Db::name('daddress')->field($fields)->where($condition)->order($order)->limit($limit)->select()->toArray();
    }
    
}
