<?php

namespace app\common\model;
use think\facade\Db;


/**
 * ============================================================================
 * DSMall多用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 数据层模型
 */
class EditablePageModel extends BaseModel {

    public $page_info;

    /**
     * 新增可编辑页面模块
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addEditablePageModel($data) {
        return Db::name('editable_page_model')->insertGetId($data);
    }

    /**
     * 删除一个可编辑页面模块
     * @author csdeshang
     * @param array $editable_page_model_id 可编辑页面模块id
     * @return bool 布尔类型的返回结果
     */
    public function delEditablePageModel($editable_page_model_id) {
        return Db::name('editable_page_model')->where('editable_page_model_id', $editable_page_model_id)->delete();
    }

    /**
     * 获取可编辑页面模块列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getEditablePageModelList($condition = array(), $pagesize = '', $orderby = 'editable_page_model_id desc') {
        if ($pagesize) {
            $result = Db::name('editable_page_model')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('editable_page_model')->where($condition)->order($orderby)->select()->toArray();
        }
    }
    public function getOneEditablePageModel($condition = array()) {
        return Db::name('editable_page_model')->where($condition)->find();
    }
    /**
     * 更新可编辑页面模块记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editEditablePageModel($condition, $data) {
        return Db::name('editable_page_model')->where($condition)->update($data);
    }



}

?>
