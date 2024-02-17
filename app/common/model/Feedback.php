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
class Feedback extends BaseModel {
public $page_info;
    /**
     * 列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页数
     * @param string $order 排序
     * @return array
     */
    public function getFeedbackList($condition, $pagesize = 10, $order = 'fb_id desc') {
        $list = Db::name('feedback')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
        $this->page_info=$list;
        return $list;
    }

    /**
     * 新增
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addFeedback($data) {
        return Db::name('feedback')->insertGetId($data);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delFeedback($condition) {
        return Db::name('feedback')->where($condition)->delete();
    }

}

?>
