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
class Arrivalnotice extends BaseModel
{
    /**
     * 通知列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param number $limit 数量限制
     * @param string $order 排序
     * @return array
     */
    public function getArrivalNoticeList($condition = array(), $field = '*', $limit = 0, $order = 'arrivalnotice_id desc',$pagesize = '') {
        if ($pagesize) {
            $result = Db::name('arrivalnotice')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('arrivalnotice')->where($condition)->field($field)->limit($limit)->order($order)->select()->toArray();
        }
    }

    /**
     * 单条通知
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param string $field 字段
     * @return type
     */
    public function getArrivalNoticeInfo($condition, $field = '*') {
        return Db::name('arrivalnotice')->where($condition)->field($field)->find();
    }

    /**
     * 通知数量
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $order 排序
     * @return array
     */
    public function getArrivalNoticeCount($condition) {
        return Db::name('arrivalnotice')->where($condition)->count();
    }


    /**
     * 添加通知
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addArrivalNotice($data) {
        $data['arrivalnotice_addtime'] = TIMESTAMP;
        return Db::name('arrivalnotice')->insertGetId($data);
    }

    /**
     * 修改通知
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function editArrivalNotice($data, $condition)
    {
        return Db::name('arrivalnotice')->where($condition)->update($data);
    }

    /**
     * 删除通知
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delArrivalNotice($condition) {
        return Db::name('arrivalnotice')->where($condition)->delete();
    }
}