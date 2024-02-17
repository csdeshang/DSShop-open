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
class Consult extends BaseModel
{
    public $page_info;
    /**
     * 咨询数量
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @return int
     */
    public function getConsultCount($condition)
    {
        return Db::name('consult')->where($condition)->count();
    }

    /**
     * 添加咨询
     * @access public
     * @author csdeshang 
     * @param array $data 参数内容
     * @return int
     */
    public function addConsult($data)
    {
        return Db::name('consult')->insertGetId($data);
    }

    /**
     * 商品咨询列表
     * @access public
     * @author csdeshang 
     * @param array $condition 检索条件
     * @param str $field 字段
     * @param int $pagesize 分页信息
     * @param str $order 排序
     * @return array
     */
    public function getConsultList($condition, $field = '*', $pagesize=10, $order = 'consult_id desc')
    {
         $res=Db::name('consult')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
         $this->page_info=$res;
         return $res->items();
    }
    /**
     * 获取咨询信息
     * @access public
     * @author csdeshang 
     * @param array $condition 咨询条件
     * @return array
     */
    public function getConsultInfo($condition)
    {
        return Db::name('consult')->where($condition)->find();
    }

    /**
     * 删除咨询
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     */
    public function delConsult($condition)
    {
        return Db::name('consult')->where($condition)->delete();
    }

    /**
     * 回复咨询
     * @access public
     * @author csdeshang 
     * @param array $condition 条件
     * @param array $data 参数内容
     * @return type
     */
    public function editConsult($condition, $data)
    {
        $data['consult_replytime'] = TIMESTAMP;
        return Db::name('consult')->where($condition)->update($data);
    }
}