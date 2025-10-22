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
class Predeposit extends BaseModel {

    public $page_info;
    public $rcblog_type_text=array(
        'order_pay'=>'下单使用',
        'order_freeze'=>'下单冻结',
        'order_cancel'=>'取消订单解冻',
        'order_comb_pay'=>'下单扣除被冻结',
        'recharge'=>'平台充值卡充值',
        'refund'=>'确认退款',
        'vr_refund'=>'虚拟兑码退款',
		'bonus'=>'红包奖励',
    );
    public $lg_type_text=array(
        'order_pay'=>'下单支付',
        'store_rechargew_pay'=>'店铺充值',
        'store_withdraw'=>'店铺提现',
        'order_freeze'=>'下单冻结',
        'order_cancel'=>'取消订单解冻',
        'order_comb_pay'=>'下单扣除被冻结',
        'recharge'=>'充值',
        'refund'=>'退款',
        'vr_refund'=>'虚拟退款',
        'cash_apply'=>'申请提现冻结',
        'cash_pay'=>'提现',
        'cash_del'=>'取消提现解冻',
        'sys_add_money'=>'系统增加',
        'sys_del_money'=>'系统减少',
        'sys_freeze_money'=>'系统冻结',
        'sys_unfreeze_money'=>'系统解冻',
        'order_inviter'=>'订单佣金',
    );


    /**
     * 增加充值卡
     * @access public
     * @author csdeshang
     * @param type $sn
     * @param type $session
     * @return type
     * @throws \app\common\model\Exception
     */        
    public function addRechargecard($sn, $session) {
        $memberId = $session['member_id'];
        $memberName = $session['member_name'];

        if ($memberId < 1 || !$memberName) {
            return array('message' => '当前登录状态为未登录，不能使用充值卡');
        }

        $rechargecard_model = model('rechargecard');

        $card = $rechargecard_model->getRechargecardBySN($sn);

        if (empty($card) || $card['rc_state'] != 0 || $card['member_id'] != 0) {
            return array('message' => '充值卡不存在或已被使用');
        }

        $card['member_id'] = $memberId;
        $card['member_name'] = $memberName;

        Db::startTrans();
        try {

            $rechargecard_model->setRechargecardUsedById($card['rc_id'], $memberId, $memberName);

            $card['amount'] = $card['rc_denomination'];
            $this->changeRcb('recharge', $card);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 取得充值列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 页面大小
     * @param type $fields 字段
     * @param type $order 排序
     * @return type
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        if ($pagesize) {
            $result = Db::name('pdrecharge')->where($condition)->field($fields)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('pdrecharge')->where($condition)->field($fields)->order($order)->select()->toArray();
        }
    }

    /**
     * 添加充值记录
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @return bool
     */
    public function addPdRecharge($data) {
        return Db::name('pdrecharge')->insertGetId($data);
    }

    /**
     * 编辑
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @param type $condition 条件
     * @return bool
     */
    public function editPdRecharge($data, $condition = array()) {
        return Db::name('pdrecharge')->where($condition)->update($data);
    }

    /**
     * 取得单条充值信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $fields 字段
     * @return type
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return Db::name('pdrecharge')->where($condition)->field($fields)->find();
    }

    /**
     * 取充值信息总数
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getPdRechargeCount($condition = array()) {
        return Db::name('pdrecharge')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return int
     */
    public function getPdcashCount($condition = array()) {
        return Db::name('pdcash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return int
     */
    public function getPdLogCount($condition = array()) {
        return Db::name('pdlog')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 页面信息
     * @param type $fields 字段
     * @param type $order 排序
     * @param type $limit 限制
     * @return array
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = 0) {
        if ($pagesize) {
            $pdlog_list_paginate = Db::name('pdlog')->where($condition)->field($fields)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $pdlog_list_paginate;
            return $pdlog_list_paginate->items();
        } else {
            $pdlog_list_paginate = Db::name('pdlog')->where($condition)->field($fields)->order($order)->limit($limit)->select()->toArray();
            return $pdlog_list_paginate;
        }
    }

    /**
     * 变更充值卡余额
     * @access public
     * @author csdeshang
     * @param type $type 类型
     * @param type $data 数据
     * @return type
     */
    public function changeRcb($type, $data = array()) {
        $amount = (float) $data['amount'];
        if ($amount < .01) {
            throw new \think\Exception('参数错误', 10006);
        }

        $available = $freeze = 0;
        $desc = null;

        switch ($type) {
            case 'order_pay':
                $available = -$amount;
                $desc = '下单，使用充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_freeze':
                $available = -$amount;
                $freeze = $amount;
                $desc = '下单，冻结充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_cancel':
                $available = $amount;
                $freeze = -$amount;
                $desc = '取消订单，解冻充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'order_comb_pay':
                $freeze = -$amount;
                $desc = '下单，扣除被冻结的充值卡余额，订单号: ' . $data['order_sn'];
                break;

            case 'recharge':
                $available = $amount;
                $desc = '平台充值卡充值，充值卡号: ' . $data['rc_sn'];
                break;

            case 'refund':
                $available = $amount;
                $desc = '确认退款，订单号: ' . $data['order_sn'];
                break;

            case 'vr_refund':
                $available = $amount;
                $desc = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
                break;
            case 'bonus':
                $available = $amount;
                $desc = $data['rcblog_description'];

                break;
            default:
                throw new \think\Exception('参数错误', 10006);
        }

        $update = array();
        if ($available) {
            $update['available_rc_balance'] = Db::raw('available_rc_balance+'.$available);
        }
        if ($freeze) {
            $update['freeze_rc_balance'] = Db::raw('freeze_rc_balance+'.$freeze);
        }

        if (!$update) {
            throw new \think\Exception('参数错误', 10006);
        }

        // 更新会员
        $updateSuccess = model('member')->editMember(array(
            'member_id' => $data['member_id'],
                ), $update,$data['member_id']);

        if (!$updateSuccess) {
            throw new \think\Exception('操作失败', 10006);
        }

        // 添加日志
        $rcblog = array(
            'member_id' => $data['member_id'],
            'member_name' => $data['member_name'],
            'rcblog_type' => $type,
            'rcblog_addtime' => TIMESTAMP,
            'available_amount' => $available,
            'freeze_amount' => $freeze,
            'rcblog_description' => $desc,
        );

        $insertSuccess = Db::name('rcblog')->insertGetId($rcblog);
        if (!$insertSuccess) {
            throw new \think\Exception('操作失败', 10006);
        }

        $msg = array(
            'code' => 'recharge_card_balance_change',
            'member_id' => $data['member_id'],
            'param' => array(
                'time' => date('Y-m-d H:i:s', TIMESTAMP),
                'url' => HOME_SITE_URL .'/Predeposit/rcb_log_list',
                'available_amount' => ds_price_format($available),
                'freeze_amount' => ds_price_format($freeze),
                'description' => $desc,
            ),
            'ali_param' => array(
                'time' => date('Y-m-d H:i:s', TIMESTAMP),
                'available_amount' => ds_price_format($available),
                'freeze_amount' => ds_price_format($freeze),
                'description' => $desc,
            ),
            'ten_param' => array(
                date('Y-m-d H:i:s', TIMESTAMP),
                $desc,
                ds_price_format($available),
                ds_price_format($freeze),
            ),
            'weixin_param'=>array(
                    'url' => config('ds_config.h5_site_url').'/pages/member/predeposit/RechargeCardList',
                    'data'=>array(
                        "keyword1" => array(
                            "value" => isset($this->rcblog_type_text[$type])?$this->rcblog_type_text[$type]:$type,
                            "color" => "#333"
                        ),
                        "keyword2" => array(
                            "value" => $amount,
                            "color" => "#333"
                        ),
                        "keyword3" => array(
                            "value" => date('Y-m-d H:i'),
                            "color" => "#333"
                        ),
                        "keyword4" => array(
                            "value" => $available,
                            "color" => "#333"
                        )
                    ),
                )
        );

        // 发送买家消息
        model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'sendMemberMsg','cron_value'=>serialize($msg)));
        return $insertSuccess;
    }

    /**
     * 变更预存款
     * @access public
     * @author csdeshang
     * @param type $change_type
     * @param type $data
     * @return type
     */
    public function changePd($change_type, $data = array()) {
        if (empty($data['member_id']) || intval($data['member_id']) <= 0) {
            throw new \think\Exception('changePd 方法未传 member_id 系统严重错误');
        }
        //获取用户信息【加锁】
        $member_info = Db::name('member')->field('member_id,available_predeposit,freeze_predeposit')->where('member_id', $data['member_id'])->lock(true)->find();
        if (empty($member_info)) {
            throw new \think\Exception('changePd 方法未传 member_id 用户不存在');
        }
        if ($data['amount'] <= 0) {
            throw new \think\Exception('changePd 方法 amount金额错误');
        }
        
        $data_log = array();
        $data_pd = array();
        $data_msg = array();

        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name'];
        $data_log['lg_addtime'] = TIMESTAMP;
        $data_log['lg_type'] = $change_type;

        $data_msg['time'] = date('Y-m-d H:i:s');
        switch ($change_type) {
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: ' . $data['order_sn'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['available_predeposit']){
                    throw new \think\Exception('order_pay 用户金额不足');
                }
                
                $data_pd['available_predeposit'] = Db::raw('available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: ' . $data['order_sn'];
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit+'.$data['amount']);
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['available_predeposit']){
                    throw new \think\Exception('order_freeze 用户金额不足');
                }
                $data_pd['available_predeposit'] = Db::raw('available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: ' . $data['order_sn'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['freeze_predeposit']){
                    throw new \think\Exception('order_cancel 用户金额不足');
                }
                
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit-'.$data['amount']);
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: ' . $data['order_sn'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['freeze_predeposit']){
                    throw new \think\Exception('order_comb_pay 用户金额不足');
                }
                
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'recharge':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '充值，充值单号: ' . $data['pdr_sn'];
                $data_log['lg_admin_name'] = isset($data['admin_name']) ? $data['admin_name'] : '会员' . $data['member_name'] . '在线充值';
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            case 'refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '确认退款，订单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'vr_refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '虚拟兑码退款成功，订单号: ' . $data['order_sn'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_apply':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: ' . $data['order_sn'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['available_predeposit']){
                    throw new \think\Exception('cash_apply 用户金额不足');
                }
                
                $data_pd['available_predeposit'] = Db::raw('available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '提现成功，提现单号: ' . $data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['freeze_predeposit']){
                    throw new \think\Exception('cash_pay 用户金额不足');
                }
                
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = 0;
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'cash_del':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: ' . $data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['freeze_predeposit']){
                    throw new \think\Exception('cash_del 用户金额不足');
                }
                
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_add_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【增加】，充值单号: ' . $data['pdr_sn'].',备注：'.$data['lg_desc'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_del_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【减少】，充值单号: ' . $data['pdr_sn'].',备注：'.$data['lg_desc'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['available_predeposit']){
                    throw new \think\Exception('sys_del_money 用户金额不足');
                }
                
                $data_pd['available_predeposit'] = Db::raw('available_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_freeze_money':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【冻结】，充值单号: ' . $data['pdr_sn'].',备注：'.$data['lg_desc'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['available_predeposit']){
                    throw new \think\Exception('sys_freeze_money 用户金额不足');
                }
                
                $data_pd['available_predeposit'] = Db::raw('available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = -$data['amount'];
                $data_msg['freeze_amount'] = $data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'sys_unfreeze_money':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '管理员调节预存款【解冻】，充值单号: ' . $data['pdr_sn'].',备注：'.$data['lg_desc'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);
                
                //判断是否有充足金额
                if($data['amount'] > $member_info['freeze_predeposit']){
                    throw new \think\Exception('sys_unfreeze_money 用户金额不足');
                }
                
                $data_pd['freeze_predeposit'] = Db::raw('freeze_predeposit-'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = -$data['amount'];
                $data_msg['desc'] = $data_log['lg_desc'];
                break;
            case 'order_inviter':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = $data['lg_desc'];
                $data_pd['available_predeposit'] = Db::raw('available_predeposit+'.$data['amount']);

                $data_msg['av_amount'] = $data['amount'];
                $data_msg['freeze_amount'] = 0;
                $data_msg['desc'] = $data_log['lg_desc'];
                break;

            //end

            default:
                throw new \think\Exception('预存款更新参数错误', 10006);
                break;
        }

        $update = model('member')->editMember(array('member_id' => $data['member_id']), $data_pd,$data['member_id']);

        if (!$update) {
            throw new \think\Exception('预存款更新异常', 10006);
        }
        $insert = Db::name('pdlog')->insertGetId($data_log);
        if (!$insert) {
            throw new \think\Exception('新增预存款记录异常', 10006);
        }

        // 支付成功发送买家消息
        $message = array();
        $message['code'] = 'predeposit_change';
        $message['member_id'] = $data['member_id'];
        $data_msg['av_amount'] = ds_price_format($data_msg['av_amount']);
        $data_msg['freeze_amount'] = ds_price_format($data_msg['freeze_amount']);
        $message['ali_param'] = $data_msg;
        $message['ten_param'] = array($data_msg['time'],$data_msg['desc'],$data_msg['av_amount'],$data_msg['freeze_amount']);
        $data_msg['pd_url'] = HOME_SITE_URL .'/Predeposit/pd_log_list';
        $message['param'] = $data_msg;
        $message['weixin_param']=array(
                    'url' => config('ds_config.h5_site_url').'/pages/member/predeposit/PredepositList',
                    'data'=>array(
                        "keyword1" => array(
                            "value" => isset($this->lg_type_text[$change_type])?$this->lg_type_text[$change_type]:$change_type,
                            "color" => "#333"
                        ),
                        "keyword2" => array(
                            "value" => $data['amount'],
                            "color" => "#333"
                        ),
                        "keyword3" => array(
                            "value" => date('Y-m-d H:i'),
                            "color" => "#333"
                        ),
                        "keyword4" => array(
                            "value" => $data_msg['av_amount'],
                            "color" => "#333"
                        )
                    ),
                );
        model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'sendMemberMsg','cron_value'=>serialize($message)));
        return $insert;
    }

    /**
     * 删除充值记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function delPdRecharge($condition) {
        return Db::name('pdrecharge')->where($condition)->delete();
    }

    /**
     * 取得提现列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 页面
     * @param type $fields 字段
     * @param type $order 排序
     * @param type $limit 限制
     * @return type
     */
    public function getPdcashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = 0) {
        if ($pagesize) {
            $pdcash_list_paginate = Db::name('pdcash')->where($condition)->field($fields)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $pdcash_list_paginate;
            return $pdcash_list_paginate->items();
        } else {
            return Db::name('pdcash')->where($condition)->field($fields)->order($order)->limit($limit)->select()->toArray();
        }
    }

    /**
     * 添加提现记录
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @return bool
     */
    public function addPdcash($data) {
        return Db::name('pdcash')->insertGetId($data);
    }

    /**
     * 编辑提现记录
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @param type $condition 条件
     * @return bool
     */
    public function editPdcash($data, $condition = array()) {
        return Db::name('pdcash')->where($condition)->update($data);
    }

    /**
     * 取得单条提现信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $fields 字段
     * @return type
     */
    public function getPdcashInfo($condition = array(), $fields = '*') {
        return Db::name('pdcash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function delPdcash($condition) {
        return Db::name('pdcash')->where($condition)->delete();
    }

}
