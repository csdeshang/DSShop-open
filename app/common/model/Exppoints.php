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
class Exppoints extends BaseModel {

    public $page_info;
    /**
     * 操作经验值
     * @access public
     * @author csdeshang
     * @param  string $stage 操作阶段 login(登录),comments(评论),order(下单)
     * @param  array $insertarr 该数组可能包含信息 array('explog_memberid'=>'会员编号','explog_membername'=>'会员名称','explog_points'=>'经验值','explog_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号');
     * @return bool
     */
    function saveExppointslog($stage, $insertarr) {
        if (!$insertarr['explog_memberid']) {
            return false;
        }
        $exppoints_rule = config("ds_config.exppoints_rule") ? unserialize(config("ds_config.exppoints_rule")) : array();
        if(empty($exppoints_rule['exp_login'])){
            return;
        }
        //记录原因文字
        switch ($stage) {
            case 'login':
                if (!isset($insertarr['explog_desc'])) {
                    $insertarr['explog_desc'] = '会员登录';
                }
                $insertarr['explog_points'] = 0;
                if (intval($exppoints_rule['exp_login']) > 0) {
                    $insertarr['explog_points'] = intval($exppoints_rule['exp_login']);
                }
                break;
            case 'comments':
                if (!isset($insertarr['explog_desc'])) {
                    $insertarr['explog_desc'] = '评论商品';
                }
                $insertarr['explog_points'] = 0;
                if (intval($exppoints_rule['exp_comments']) > 0) {
                    $insertarr['explog_points'] = intval($exppoints_rule['exp_comments']);
                }
                break;
            case 'system':
                break;
            case 'order':
                if (!isset($insertarr['explog_desc'])) {
                    $insertarr['explog_desc'] = '订单' . $insertarr['order_sn'] . '购物消费';
                }
                $insertarr['explog_points'] = 0;
                $exppoints_rule['exp_orderrate'] = floatval($exppoints_rule['exp_orderrate']);
                if ($insertarr['orderprice'] && $exppoints_rule['exp_orderrate'] > 0) {
                    $insertarr['explog_points'] = @intval($insertarr['orderprice'] / $exppoints_rule['exp_orderrate']);
                    $exp_ordermax = intval($exppoints_rule['exp_ordermax']);
                    if ($exp_ordermax > 0 && $insertarr['explog_points'] > $exp_ordermax) {
                        $insertarr['explog_points'] = $exp_ordermax;
                    }
                }
                break;
        }
        //新增日志
        $value_array = array();
        $value_array['explog_memberid'] = $insertarr['explog_memberid'];
        $value_array['explog_membername'] = $insertarr['explog_membername'];
        $value_array['explog_points'] = $insertarr['explog_points'];
        $value_array['explog_addtime'] = TIMESTAMP;
        $value_array['explog_desc'] = $insertarr['explog_desc'];
        $value_array['explog_stage'] = $stage;
        $result = false;
        if ($value_array['explog_points'] != '0') {
            $result = self::addExppointslog($value_array);
        }
        if ($result) {
            //更新member内容
            $obj_member = model('member');
            $upmember_array = array();
            $upmember_array['member_exppoints'] = Db::raw('member_exppoints+'.$insertarr['explog_points']);
            $obj_member->editMember(array('member_id' => $insertarr['explog_memberid']), $upmember_array,$insertarr['explog_memberid']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加经验值日志信息
     * @access public
     * @author csdeshang
     * @param array $data 添加信息数组
     * @return array
     */
    public function addExppointslog($data) {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('exppointslog')->insertGetId($data);
        return $result;
    }

    /**
     * 经验值日志总条数
     * @access public
     * @author csdeshang
     * @param array $where 条件数组
     * @param string $field 查询字段
     * @return int
     */
    public function getExppointslogCount($where, $field = '*') {
        $count = Db::name('exppointslog')->field($field)->where($where)->count();
        return $count;
    }

    /**
     * 经验值日志列表
     * @access public
     * @author csdeshang
     * @param array $where 条件数组
     * @param string $field 查询字段
     * @param int $pagesize 分页信息
     * @param string $order 排序
     * @return array
     */
    public function getExppointslogList($where, $field = '*', $pagesize = 0, $order = '') {
        if($pagesize){
            $result = Db::name('exppointslog')->field($field)->where($where)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            $res = $result->items();
        }else{
            $res = Db::name('exppointslog')->field($field)->where($where)->order($order)->select()->toArray();
        }
        return $res;
    }

    /**
     * 获得阶段说明文字
     * @access public
     * @author csdeshang
     * @return string
     */
    public function getStage() {
        $stage_arr = array();
        $stage_arr['login'] = '会员登录';
        $stage_arr['comments'] = '商品评论';
        $stage_arr['order'] = '订单消费';
        $stage_arr['system'] = '系统调整';
        return $stage_arr;
    }

}

?>
