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
class Groupbuyclass extends BaseModel
{
    public $page_info;

    /**
     * 读取抢购分类列表
     * @access public
     * @author csdeshang
     * @param array $condition  条件
     * @param int $pagesize 分页
     * @param str $order 排序
     * @return array
     */
    public function getGroupbuyclassList($condition = '', $pagesize = '',$order='gclass_id desc')
    {
        return Db::name('groupbuyclass')->where($condition)->order($order)->select()->toArray();
    }

    /**
     * 读取列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param int $pagesize 分页
     * @param int $max_deep 最大深度
     * @return array
     */
    public function getTreeList($condition = '', $pagesize = '', $max_deep = 1)
    {

        $gclass_list = $this->getGroupbuyclassList($condition, $pagesize);

        $tree_list = array();
        if (is_array($gclass_list)) {
            $tree_list = $this->_getTreeList($gclass_list, 0, 0, $max_deep);
        }
        return $tree_list;
    }

    /**
     * 按照顺序显示树形结构
     * @access public
     * @author csdeshang
     * @param array $list 列表
     * @param int $parent_id 父ID
     * @param int $deep 深度
     * @param int $max_deep 最大深度
     * @return array
     */
    private function _getTreeList($list, $parent_id, $deep = 0, $max_deep)
    {

        $result = array();
        foreach ($list as $node) {

            if ($node['gclass_parent_id'] == $parent_id) {

                if ($deep <= $max_deep) {
                    $temp = $this->_getTreeList($list, $node['gclass_id'], $deep + 1, $max_deep);
                    if (!empty($temp)) {
                        $node['have_child'] = 1;
                    }
                    else {
                        $node['have_child'] = 0;
                    }
                    //标记是否为叶子节点
                    if ($deep == $max_deep) {
                        $node['node'] = 1;
                    }
                    else {
                        $node['node'] = 0;
                    }

                    $node['deep'] = $deep;
                    $result[] = $node;
                    if (!empty($temp)) {
                        $result = array_merge($result, $temp);
                    }

                    unset($temp);
                }
            }
        }
        return $result;
    }

    /**
     * 根据编号获取所有下级编号的数组
     * @access public
     * @author csdeshang
     * @param array $gclass_id_array 分类id数组
     * @return array 数组类型的返回结果
     */
    public function getAllClassId($gclass_id_array)
    {

        $all_gclass_id_array = array();
        $gclass_list = $this->getGroupbuyclassList();
        foreach ($gclass_id_array as $gclass_id) {
            $all_gclass_id_array[] = $gclass_id;
            foreach ($gclass_list as $class) {
                if ($class['gclass_parent_id'] == $gclass_id) {
                    $all_gclass_id_array[] = $class['gclass_id'];
                }
            }
        }
        return $all_gclass_id_array;
    }


    /**
     * 判断是否存在
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return boolean
     */
    public function isGroupbuyclassExist($condition)
    {
        $list = Db::name('groupbuyclass')->where($condition)->select()->toArray();
        if (empty($list)) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addGroupbuyclass($data)
    {
        return Db::name('groupbuyclass')->insertGetId($data);

    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update_array 更新数据数组
     * @param array $where_array  更新条件数组
     * @return bool
     */
    public function editGroupbuyclass($update_array, $where_array)
    {
        return Db::name('groupbuyclass')->where($where_array)->update($update_array);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delGroupbuyclass($condition)
    {
        return Db::name('groupbuyclass')->where($condition)->delete();
    }

}