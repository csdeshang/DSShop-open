<?php

namespace app\crontab\controller;

use app\common\logic\Queue;
use think\facade\Db;
use think\facade\Cache;

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
 * 定时器
 */
class Minutes extends BaseCron {

    /**
     * 默认方法
     */
    public function index() {
        $this->_cron_common();
        $this->_cron_mail_send();
        $this->_cron_pintuan();
        $this->_cron_bonus();
        $this->_cron_sms();
    }

    /**
     * 短信的处理
     */
    private function _cron_sms() {
        $smslog_model = model('smslog');
        $condition = array();
        $condition[] = array('smslog_state', '=', 0);
        $smslog_list = $smslog_model->getSmsList($condition, '', 100, 'smslog_id asc');
        $sms = new \sendmsg\Sms();
        foreach ($smslog_list as $val) {
            $smslog_param = json_decode($val['smslog_msg'], true);
            $smslog_msg = $smslog_param['message'];
            $send_result = $sms->send($val['smslog_phone'], $smslog_param);
            if ($send_result['code'] == true) {
                $smslog_state = 1;
            } else {
                $smslog_state = 2;
            }
            $condition = array();
            $condition[] = array('smslog_id', '=', $val['smslog_id']);
            $update = array(
                'smslog_state' => $smslog_state,
                'smslog_msg' => $smslog_msg,
                'smslog_smstime' => TIMESTAMP,
            );
            $smslog_model->editSms($update, $condition);
        }
    }

    /**
     * 拼团相关处理
     */
    private function _cron_pintuan() {
        $ppintuan_model = model('ppintuan');
        $ppintuangroup_model = model('ppintuangroup');
        $ppintuanorder_model = model('ppintuanorder');
        //自动关闭时间过期的店铺拼团活动
        $condition = array();
        $condition[] = array('pintuan_end_time', '<', TIMESTAMP);
        $ppintuan_model->endPintuan($condition);

        //查看正在进行开团的列表.
        $condition = array();
        $condition[] = array('pintuangroup_state', '=', 1);
        $pintuangroup_list = $ppintuangroup_model->getPpintuangroupList($condition);

        $success_ids = array(); #拼团开团成功的拼团开团ID
        $fail_ids = array(); #拼团开团失败的拼团开团ID
        foreach ($pintuangroup_list as $key => $pintuangroup) {

            //判断当前参团是否已过期
            if (TIMESTAMP >= $pintuangroup['pintuangroup_starttime'] + $pintuangroup['pintuangroup_limit_hour'] * 3600) {

                //当已参团人数  大于 当前开团的  参团人数   
                if ($pintuangroup['pintuangroup_joined'] >= $pintuangroup['pintuangroup_limit_number']) {

                    //满足开团人数,查看对应的订单是否付款,未付款则拼团失败,订单取消,订单款项退回.
                    $condition = array();
                    $condition[] = array('ppintuanorder.pintuangroup_id', '=', $pintuangroup['pintuangroup_id']);
                    if (!$pintuangroup['pintuangroup_is_virtual']) {
                        $condition[] = array('order.order_state', '=', 20);
                        $count = Db::name('ppintuanorder')->alias('ppintuanorder')->join('order order', 'order.order_id=ppintuanorder.order_id')->where($condition)->count();
                    } else {
                        $condition[] = array('vrorder.order_state', '=', 20);
                        $count = Db::name('ppintuanorder')->alias('ppintuanorder')->join('vrorder vrorder', 'vrorder.order_id=ppintuanorder.order_id')->where($condition)->count();
                    }
                    if ($count == $pintuangroup['pintuangroup_joined']) {
                        //表示全部付款,拼团成功
                        $success_ids[] = $pintuangroup['pintuangroup_id'];
                    } else {
                        $fail_ids[] = $pintuangroup['pintuangroup_id'];
                    }
                } else {
                    //未满足开团人数
                    $fail_ids[] = $pintuangroup['pintuangroup_id'];
                }
            }
        }

        $condition = array();
        //在拼团失败的所有订单，已经付款的订单列表，取消订单,并且退款，未付款的订单自动取消订单
        $condition[] = array('ppintuanorder.pintuangroup_id', 'in', implode(',', $fail_ids));
        $condition[] = array('ppintuanorder.pintuanorder_type', '=', 0);
        $ppintuanorder_list = Db::name('ppintuanorder')->field('order.*')->alias('ppintuanorder')->join('order order', 'order.order_id=ppintuanorder.order_id')->where($condition)->select()->toArray();

        //针对已付款,拼团没成功的订单,进行取消订单以及退款操作
        $order_model = model('order');
        $logic_order = model('order', 'logic');

        foreach ($ppintuanorder_list as $key => $order_info) {
            Db::startTrans();
            try {
                $logic_order->changeOrderStateCancel($order_info, 'system', '系统', '拼团未成功系统自动关闭订单', true, false, true);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                continue;
            }
        }

        $condition = array();
        $condition[] = array('ppintuanorder.pintuangroup_id', 'in', implode(',', $fail_ids));
        $condition[] = array('ppintuanorder.pintuanorder_type', '=', 1);
        $condition[] = array('vrorder.order_state', '=', 20);
        $ppintuanorder_list = Db::name('ppintuanorder')->field('vrorder.*')->alias('ppintuanorder')->join('vrorder vrorder', 'vrorder.order_id=ppintuanorder.order_id')->where($condition)->select()->toArray();

        $logic_vrorder = model('vrorder', 'logic');

        foreach ($ppintuanorder_list as $key => $order_info) {
            $logic_vrorder->changeOrderStateCancel($order_info, 'system', '系统', '拼团未成功系统自动关闭订单', false);
        }

        //失败修改拼团相关数据库信息
        $condition = array();
        $condition[] = array('pintuangroup_id', 'in', implode(',', $fail_ids));
        $ppintuangroup_model->failPpintuangroup($condition);

        //成功修改拼团相关数据库信息
        $condition = array();
        $condition[] = array('pintuangroup_is_virtual', '=', 0);
        $condition[] = array('pintuangroup_id', 'in', implode(',', $success_ids));
        $condition2 = array();
        $condition2[] = array('pintuangroup_id', 'in', implode(',', $success_ids));
        $ppintuangroup_model->successPpintuangroup($condition, $condition2);
        //给成功拼团的虚拟订单发送兑换码
        $vrorder_model = model('vrorder');
        $condition = array();
        $condition[] = array('order_promotion_type', '=', 2);
        $condition[] = array('promotions_id', 'in', implode(',', $success_ids));
        $vrorder_list = $vrorder_model->getVrorderList($condition, 1000);
        foreach ($vrorder_list as $vrorder) {
            $vrorder_model->addVrorderCode($vrorder);
            $condition = array();
            $condition[] = array('pintuangroup_id', '=', $vrorder['promotions_id']);
            $ppintuangroup_model->successPpintuangroup($condition, $condition);
        }
    }

    /**
     * 处理过期红包
     */
    private function _cron_bonus() {
        $condition = array();
        $condition[] = array('bonus_endtime', '<', TIMESTAMP);
        $condition[] = array('bonus_state', '=', 1);
        $data = array(
            'bonus_state' => 2,
        );
        model('bonus')->editBonus($condition, $data);
    }

    /**
     * 发送邮件消息
     */
    private function _cron_mail_send() {
        //每次发送数量
        $_num = 50;
        $mailcron_model = model('mailcron');
        $cron_array = $mailcron_model->getMailCronList(array(), $_num);
        if (!empty($cron_array)) {
            $email = new \sendmsg\Email();
            $mail_array = array();
            foreach ($cron_array as $val) {
                $return = $email->send_sys_email($val['mailcron_address'], $val['mailcron_subject'], $val['mailcron_contnet']);
                if ($return) {
                    // 记录需要删除的id
                    $mail_array[] = $val['mailcron_id'];
                }
            }
            // 删除已发送的记录
            $mailcron_model->delMailCron(array(array('mailcron_id', 'in', $mail_array)));
        }
    }

    /**
     * 执行通用任务
     */
    private function _cron_common() {

        //查找待执行任务
        $cron_model = model('cron');
        $cron = $cron_model->getCronList(array(array('cron_exetime', '<=', TIMESTAMP)));

        if (!is_array($cron))
            return;
        $cron_array = array();
        $QueueLogic = new Queue();
        foreach ($cron as $v) {
            $value = unserialize($v['cron_value']);
            $key = $v['cron_type'];
            $res = $QueueLogic->$key($value);
            if ($res['code']) {
                $cron_model->delCron(array(array('cron_id', '=', $v['cron_id'])));
            }
        }
    }
}

?>
