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
class Informsubjecttype extends BaseModel
{
    public $page_info;
    
    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addInformsubjecttype($data)
    {
        return Db::name('informsubjecttype')->insertGetId($data);

    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update_array 数据
     * @param array $condition 条件
     * @return bool
     */
    public function editInformsubjecttype($update_array, $condition)
    {
        return Db::name('informsubjecttype')->where($condition)->update($update_array);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delInformsubjecttype($condition)
    {
        return Db::name('informsubjecttype')->where($condition)->delete();
    }

    /**
     * 获得举报类型列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param int $pagesize 分页
     * @param str $order 排序
     * @return array
     */
    public function getInformsubjecttypeList($condition = '', $pagesize = '', $order = 'informtype_id asc') {
        if ($pagesize) {
            $res = Db::name('informsubjecttype')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('informsubjecttype')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 获得有效举报类型列表
     * @access public
     * @author csdeshang
     * @param int $pagesize 分页
     * @return array
     */
    public function getActiveInformsubjecttypeList($pagesize = '')
    {
        $condition = array();
        $condition[] = array('informtype_state','=',1);
        return $this->getInformsubjecttypeList($condition, $pagesize);

    }
}