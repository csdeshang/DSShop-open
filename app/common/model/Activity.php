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
class Activity extends BaseModel
{
    public $page_info;

    /**
     * 活动列表
     * @author csdeshang
     * @param type $condition   查询条件
     * @param type $pagesize        分页页数
     * @param type $order       排序
     * @return type
     */
    public function getActivityList($condition, $pagesize = '', $order = 'activity_sort asc') {
        if ($pagesize) {
            $res = Db::name('activity')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('activity')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 添加活动
     * @author csdeshang
     * @param type $data 查询数据
     * @return array 一维数组
     */
    public function addActivity($data)
    {
        return Db::name('activity')->insertGetId($data);
    }
    /**
     * 更新活动
     * @author csdeshang
     * @param type $data 活动数据
     * @param type $id   活动id
     * @return type
     */
    public function editActivity($data, $id)
    {
        return Db::name('activity')->where("activity_id='$id' ")->update($data);
    }

    /**
     * 删除活动
     * @author csdeshang
     * @param type $condition 删除条件
     * @return type
     */
    public function delActivity($condition)
    {
        return Db::name('activity')->where($condition)->delete();
    }

    /**
     * 根据id查询一条活动
     * @author csdeshang
     * @param int $id 活动id
     * @return array 一维数组
     */
    public function getOneActivityById($id)
    {
        return Db::name('activity')->where('activity_id',$id)->find();
    }
}