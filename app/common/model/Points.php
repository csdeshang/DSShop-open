<?php

/**
 * 积分
 */

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
class Points extends BaseModel {

    public $page_info;

    /**
     * 操作积分
     * @access public
     * @author csdeshang
     * @param  string $stage 操作阶段 regist(注册),login(登录),comments(评论),order(下单),system(系统),other(其他),pointorder(积分礼品兑换),app(同步积分兑换)
     * @param  array $insertarr 该数组可能包含信息 array('pl_memberid'=>'会员编号','pl_membername'=>'会员名称','pl_adminid'=>'管理员编号','pl_adminname'=>'管理员名称','pl_points'=>'积分','pl_desc'=>'描述','orderprice'=>'订单金额','order_sn'=>'订单编号','order_id'=>'订单序号','point_ordersn'=>'积分兑换订单编号');
     * @param  bool $if_repeat 是否可以重复记录的信息,true可以重复记录，false不可以重复记录，默认为true
     * @return bool
     */
    function savePointslog($stage, $insertarr, $if_repeat = true) {
        if (!$insertarr['pl_memberid']) {
            return false;
        }

        //记录原因文字
        switch ($stage) {
            case 'regist':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '注册会员';
                }
                $insertarr['pl_points'] = intval(config('ds_config.points_reg'));
                break;
            case 'login':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '会员登录';
                }
                $insertarr['pl_points'] = intval(config('ds_config.points_login'));
                break;
            case 'comments':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '评论商品';
                }
                $insertarr['pl_points'] = intval(config('ds_config.points_comments'));
                break;
            case 'order':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '订单' . $insertarr['order_sn'] . '购物消费';
                }
                $insertarr['pl_points'] = 0;
                if ($insertarr['orderprice']) {
                    $insertarr['pl_points'] = @intval($insertarr['orderprice'] / config('ds_config.points_orderrate'));
                    if ($insertarr['pl_points'] > intval(config('ds_config.points_ordermax'))) {
                        $insertarr['pl_points'] = intval(config('ds_config.points_ordermax'));
                    }
                }
                //订单添加赠送积分列
                $obj_order = model('order');
                $data = array();
                $data['order_pointscount'] = Db::raw('order_pointscount+'.$insertarr['pl_points']);
                $obj_order->editOrdercommon($data, array('order_id' => $insertarr['order_id']));
                break;
            case 'system':
                break;
            case 'pointorder':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '兑换礼品信息' . $insertarr['point_ordersn'] . '消耗积分';
                }
                break;
            case 'app':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = lang('points_pointorderdesc_app');
                }
                break;
            case 'signin':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '签到得到积分';
                }
                break;
            case 'inviter':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '邀请新会员[' . $insertarr['invited'] . ']注册';
                }
                $insertarr['pl_points'] = intval(config('ds_config.points_invite'));
                break;
            case 'rebate':
                if (!isset($insertarr['pl_desc'])) {
                    $insertarr['pl_desc'] = '邀请人消费得到积分';
                }
                break;
            case 'marketmanage':
                break; 
            case 'other':
                break;
        }
        $save_sign = true;
        if ($if_repeat == false) {
            //检测是否有相关信息存在，如果没有，入库
            $condition[] = array('pl_memberid','=',$insertarr['pl_memberid']);
            $condition[] = array('pl_stage','=',$stage);
            $log_array = self::getPointsInfo($condition);
            if (!empty($log_array)) {
                $save_sign = false;
            }
        }
        if ($save_sign == false) {
            return true;
        }
        //新增日志
        $value_array = array();
        $value_array['pl_memberid'] = $insertarr['pl_memberid'];
        $value_array['pl_membername'] = $insertarr['pl_membername'];
        if (isset($insertarr['pl_adminid'])) {
            $value_array['pl_adminid'] = $insertarr['pl_adminid'];
        }
        if (isset($insertarr['pl_adminname'])) {
            $value_array['pl_adminname'] = $insertarr['pl_adminname'];
        }
        $value_array['pl_points'] = $insertarr['pl_points'];
        $value_array['pl_addtime'] = TIMESTAMP;
        $value_array['pl_desc'] = $insertarr['pl_desc'];
        $value_array['pl_stage'] = $stage;
        $result = false;
        if ($value_array['pl_points'] != '0') {
            $result = self::addPointslog($value_array);
        }
        if ($result) {
            //更新member内容
            $obj_member = model('member');
            $upmember_array = array();
            $upmember_array['member_points'] = Db::raw('member_points+'.$insertarr['pl_points']);
            $obj_member->editMember(array('member_id' => $insertarr['pl_memberid']), $upmember_array,$insertarr['pl_memberid']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加积分日志信息
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return boolean
     */
    public function addPointslog($data) {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('pointslog')->insertGetId($data);
        return $result;
    }

    /**
     * 积分日志列表
     * @access public
     * @author csdeshang
     * @param type $condition
     * @param type $pagesize
     * @param type $field
     * @return type
     */
    public function getPointslogList($condition, $pagesize = '', $field = '*',$limit = 0,$order='pl_addtime desc') {
        if ($pagesize) {
            $result = Db::name('pointslog')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('pointslog')->where($condition)->field($field)->limit($limit)->order($order)->select()->toArray();
        }
    }

    /**
     * 积分日志详细信息
     * @access public
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getPointsInfo($condition, $field = '*') {
        //得到条件语句
        return Db::name('pointslog')->field($field)->where($condition)->find();
    }

    /**
     * 积分日志数量
     */
    public function getPointsCount($condition) {
        return Db::name('pointslog')->where($condition)->count();
    }
}
