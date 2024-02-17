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
class Informsubject extends BaseModel
{
    public $page_info;
    
    /**
     * 增加投诉主题
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addInformsubject($data)
    {
        return Db::name('informsubject')->insertGetId($data);
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update_array 数据
     * @param array $where_array  条件
     * @return bool
     */
    public function editInformsubject($update_array, $where_array)
    {
        return Db::name('informsubject')->where($where_array)->update($update_array);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delInformsubject($condition)
    {
        return Db::name('informsubject')->where($condition)->delete();
    }

    /**
     * 获得列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页
     * @param string $field 字段
     * @param string $order 排序
     * @return array
     */
    public function getInformsubjectList($condition = '', $pagesize = '', $field = '',$order='informsubject_id asc')
    {
        if($pagesize){
            $res = Db::name('informsubject')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        }else{
            return Db::name('informsubject')->field($field)->where($condition)->order($order)->select()->toArray();
        }
    }
    
    /**
     * 获取单个信息
     * @access public
     * @author csdeshang
     * @param type $condition 查询条件
     * @return type
     */
    public function getOneInformsubject($condition){
        return Db::name('informsubject')->where($condition)->find();
    }
    
}