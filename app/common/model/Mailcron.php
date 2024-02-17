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
class Mailcron extends BaseModel
{
    /**
     * 新增商家消息任务计划
     * @access public
     * @author csdeshang
     * @param array $insert 插入数据
     */
    public function addMailCron($insert) {
        return Db::name('mailcron')->insertGetId($insert);
    }
 
    /**
     * 查看商家消息任务计划
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param int $limit 限制
     * @param string $order 排序
     * @return array
     */
    public function getMailCronList($condition, $limit = 0, $order = 'mailcron_id asc') {
        return Db::name('mailcron')->where($condition)->limit($limit)->order($order)->select()->toArray();
    }

    /**
     * 删除商家消息任务计划
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delMailCron($condition) {
        return Db::name('mailcron')->where($condition)->delete();
    }
}