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
class EditablePageConfig extends BaseModel {

    public $page_info;
    public $store_id=0;
    /**
     * 新增可编辑页面配置
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addEditablePageConfig($data) {
        return Db::name('editable_page_config')->insertGetId($data);
    }
    /**
     * 新增可编辑页面配置
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addEditablePageConfigAll($data) {
        return Db::name('editable_page_config')->insertAll($data);
    }
    /**
     * 删除一个可编辑页面配置
     * @author csdeshang
     * @param array $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delEditablePageConfig($condition) {
        //删除图片
        $editable_page_config_id_list=Db::name('editable_page_config')->where($condition)->column('editable_page_config_id');
        if($editable_page_config_id_list){
            $upload_condition = array();
            $upload_condition[] = array('upload_type','=',7);
            $upload_condition[] = array('item_id','in',$editable_page_config_id_list);
            $file_name_list=Db::name('upload')->where($upload_condition)->column('file_name');
            foreach($file_name_list as $file_name){
                @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_EDITABLE_PAGE. DIRECTORY_SEPARATOR .$file_name);
            }
            model('upload')->delUpload($upload_condition);
        }
        return Db::name('editable_page_config')->where($condition)->delete();
    }

    /**
     * 获取可编辑页面配置列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getEditablePageConfigList($condition = array(), $pagesize = '', $orderby = 'editable_page_config_sort_order asc') {
        if ($pagesize) {
            $result = Db::name('editable_page_config')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('editable_page_config')->where($condition)->order($orderby)->select()->toArray();
        }
    }
    public function getOneEditablePageConfig($condition = array()) {
        return Db::name('editable_page_config')->where($condition)->find();
    }
    /**
     * 更新可编辑页面配置记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editEditablePageConfig($condition, $data) {
        return Db::name('editable_page_config')->where($condition)->update($data);
    }

}

?>
