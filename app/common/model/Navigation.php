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
class Navigation extends BaseModel {

    public $page_info;

    /**
     * 获取导航列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param int $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getNavigationList($condition, $pagesize = '', $order = 'nav_sort desc') {
        if ($pagesize) {
            $nav_list = Db::name('navigation')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $nav_list;
            return $nav_list->items();
        } else {
            return Db::name('navigation')->where($condition)->order('nav_sort')->select()->toArray();
        }
    }

    /**
     * 新增导航
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @return bool
     */
    public function addNavigation($data) {
        $add_navigation = Db::name('navigation')->insert($data);
        return $add_navigation;
    }
    /**
     * 编辑导航
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @param type $condition 条件
     * @return bool
     */
    public function eidtNavigation($data, $condition) {
        return Db::name('navigation')->where($condition)->update($data);
    }
    
    /**
     * 获取单个导航
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return array
     */
    public function getOneNavigation($condition) {
        return Db::name('navigation')->where($condition)->find();
    }
    /**
     * 删除导航
     * @access public
     * @author csdeshang
     * @param type $conditions 条件
     * @return bool
     */
    public function delNavigation($conditions) {
        return Db::name('navigation')->where($conditions)->delete();
    }

}
