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
class Spec extends BaseModel {
public $page_info;


    /**
     * 规格列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @return type
     */
    public function getSpecList($condition, $pagesize = '', $order = 'sp_id desc') {
        if($pagesize){
            $result= Db::name('spec')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$result;
            return $result->items();
        }else{
            return Db::name('spec')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 单条规格信息
     * @access public
     * @author csdeshang
     * @param type $sp_id 规格ID
     * @param type $field 字段
     * @return type
     */
    public function getSpecInfo($sp_id, $field = '*') {
        return Db::name('spec')->where('sp_id',$sp_id)->field($field)->find();
    }

    /**
     * 规格值列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @return type
     */
    public function getSpecvalueList($where, $field = '*', $order = 'spvalue_sort asc,spvalue_id asc') {
        $result = Db::name('specvalue')->field($field)->where($where)->order($order)->select()->toArray();
        return empty($result) ? array() : $result;
    }

    /**
     * 更新规格值
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $where  条件
     * @return boolean
     */
    public function editSpecvalue($update, $where) {
        $result = Db::name('specvalue')->where($where)->update($update);
        return $result;
    }

    /**
     * 增加规格值 
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return boolean
     */
    public function addSpecvalue($data) {
        $result = Db::name('specvalue')->insertGetId($data);
        return $result;
    }

    /**
     * 添加规格 多条
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return boolean
     */
    public function addSpecvalueALL($data) {
        $result = Db::name('specvalue')->insertAll($data);
        return $result;
    }

    /**
     * 删除规格值
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @return boolean
     */
    public function delSpecvalue($where) {
        $result = Db::name('specvalue')->where($where)->delete();
        return $result;
    }

    /**
     * 更新规格信息
     * @access public
     * @author csdeshang
     * @param type $update 更新数据
     * @param type $condition 条件
     * @return boolean
     */
    public function editSpec($update, $condition) {
        if (empty($update)) {
            return false;
        }
        return Db::name('spec')->where($condition)->update($update);
    }

    /**
     * 添加规格信息
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return type
     */
    public function addSpec($data) {
        // 规格表插入数据
        $result = Db::name('spec')->insertGetId($data);
        return $result;
    }
 
    /**
     * 删除规格
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function delSpec($condition) {
        return Db::name('spec')->where($condition)->delete();
    }

}

?>
