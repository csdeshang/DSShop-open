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
class Mallconsult extends BaseModel
{
    public $page_info;

    /**
     * 咨询列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param int $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getMallconsultList($condition, $field = '*', $pagesize = 0, $order = 'mallconsult_id desc') {
        if($pagesize){
            $res= Db::name('mallconsult')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            return $res->items();
        }else{
            return Db::name('mallconsult')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 咨询数量
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return int
     */
    public function getMallconsultCount($condition) {
        return Db::name('mallconsult')->where($condition)->count();
    }

    /**
     * 单条咨询
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field 字段
     * @return type
     */
    public function getMallconsultInfo($condition, $field = '*') {
        return Db::name('mallconsult')->where($condition)->field($field)->find();
    }

    /**
     * 咨询详细信息
     * @access public
     * @author csdeshang
     * @param int $mallconsult_id ID编号
     * @return boolean|multitype:
     */
    public function getMallconsultDetail($mallconsult_id) {
        $consult_info = $this->getMallconsultInfo(array('mallconsult_id' => $mallconsult_id));
        if (empty($consult_info)) {
            return false;
        }

        $type_info = model('mallconsulttype')->getMallconsulttypeInfo(array('mallconsulttype_id' => $consult_info['mallconsulttype_id']), 'mallconsulttype_name');
        return array_merge($consult_info, $type_info);
    }

    /**
     * 添加咨询
     * @access public
     * @author csdeshang
     * @param array $insert 参数内容
     * @return bool
     */
    public function addMallconsult($insert) {
        $insert['mallconsult_addtime'] = TIMESTAMP;
        return Db::name('mallconsult')->insertGetId($insert);
    }

    /**
     * 编辑咨询
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param array $update 数据
     * @return boolean
     */
    public function editMallconsult($condition, $update) {
        return Db::name('mallconsult')->where($condition)->update($update);
    }

    /**
     * 删除咨询
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delMallconsult($condition) {
        return Db::name('mallconsult')->where($condition)->delete();
    }
}