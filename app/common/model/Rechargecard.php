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
class Rechargecard extends BaseModel
{
    public $page_info;

    /**
     * 获取充值卡列表
     * @access public
     * @author csdeshang
     * @param type $condition 查询条件
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @return type
     */
    public function getRechargecardList($condition, $pagesize = 20, $limit = 0) {
        $order = 'rc_id desc';
        if ($pagesize) {
            $res = Db::name('rechargecard')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('rechargecard')->where($condition)->order($order)->limit($limit)->select()->toArray();
        }
    }

    /**
     * 通过卡号获取单条充值卡数据
     * @access public
     * @author csdeshang
     * @param type $sn 卡号
     * @return type
     */
    public function getRechargecardBySN($sn)
    {
        return Db::name('rechargecard')->where(array(
                                'rc_sn' => (string) $sn,
                            ))->find();
    }

    /**
     * 设置充值卡为已使用
     * @access public
     * @author csdeshang
     * @param type $id 表字增ID
     * @param type $memberId 会员ID
     * @param type $memberName 会员名称
     * @return type
     */
    public function setRechargecardUsedById($id, $memberId, $memberName)
    {
        return Db::name('rechargecard')->where(array('rc_id' => (string) $id,))->update(array('rc_tsused' => TIMESTAMP, 'rc_state' => 1, 'member_id' => $memberId, 'member_name' => $memberName,));
    }

    /**
     * 通过ID删除充值卡（自动添加未使用标记）
     * @access public
     * @author csdeshang
     * @param type $id 表自增id
     * @return type
     */
    public function delRechargecard($condition)
    {
        return Db::name('rechargecard')->where($condition)->delete();
    }

    /**
     * 通过给定的卡号数组过滤出来不能被新插入的卡号（卡号存在的）
     * @access public
     * @author csdeshang
     * @param array $sns 卡号数组
     * @return type
     */
    public function getOccupiedRechargecardSNsBySNs(array $sns)
    {
        $array = Db::name('rechargecard')->field('rc_sn')->where(array(array('rc_sn','in', $sns),))->select()->toArray();

        $data = array();

        foreach ((array) $array as $v) {
            $data[] = $v['rc_sn'];
        }

        return $data;
    }
    
    /**
     * 获取充值卡数量
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getRechargecardCount($condition) {
        return Db::name('rechargecard')->where($condition)->count();
    }
    
    /**
     * 插入充值卡
     * @access public
     * @author csdeshang
     * @param type $id 表自增id
     * @return type
     */
    public function addRechargecardAll($data)
    {
        return Db::name('rechargecard')->insertAll($data);
    }
}