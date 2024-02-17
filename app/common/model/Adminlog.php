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
class Adminlog extends BaseModel {

    public $page_info;

    /**
     * 获取日志记录列表
     * @author csdeshang
     * @param type $condition 查询条件
     * @param type $pagesize      分页信息
     * @param type $order     排序
     * @return type
     */
    public function getAdminlogList($condition, $pagesize = '', $order) {
        if ($pagesize) {
            $result = Db::name('adminlog')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('adminlog')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 删除日志记录
     * @author csdeshang
     * @param type $condition 删除条件
     * @return type
     */
    public function delAdminlog($condition) {
        return Db::name('adminlog')->where($condition)->delete();
    }

    /**
     * 获取日志条数
     * @author csdeshang
     * @param type $condition 查询条件
     * @return type
     */
    public function getAdminlogCount($condition) {
        return Db::name('adminlog')->where($condition)->count();
    }
    
    /**
     * 增加日子
     * @author csdeshang
     * @param type $data
     * @return type
     */
    public function addAdminlog($data) {
        return Db::name('adminlog')->insertGetId($data);
    }

}
