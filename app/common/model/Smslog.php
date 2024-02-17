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
class Smslog extends BaseModel {

    public $page_info;

    /**
     * 发送验证码
     * @author csdeshang
     * @param type $smslog_phone 手机号
     * @param type $smslog_param 短信参数
     * @param type $smslog_type 类型
     * @param type $smslog_captcha 验证码
     * @param type $member_id 会员ID
     * @param type $member_name 会员名
     * @param type $if_queue 是否放入队列
     * @return type
     */
    function sendSms($smslog_phone, $smslog_param, $smslog_type = '', $smslog_captcha = '', $member_id = '0', $member_name = '', $if_queue = false) {
        $smslog_msg = $smslog_param['message'];
        //通过手机号判断是否允许发送短信
        $begin_add_time = strtotime(date('Y-m-d', TIMESTAMP));
        $end_add_time = strtotime(date('Y-m-d', TIMESTAMP)) + 24 * 3600;

        //同一IP 每天只能发送20条短信
        $condition = array();
        $condition[] = array('smslog_ip', '=', request()->ip());
        $condition[] = array('smslog_smstime', 'between', array($begin_add_time, $end_add_time));
        if ($smslog_captcha && $this->getSmsCount($condition) > 20) {
            return array('state' => FALSE, 'code' => 10001, 'message' => '同一IP地址一天内只能发送20条短信，请勿多次获取动态码！');
        }

        //同一手机号,60秒才能提交发送一次
        $condition = array();
        $condition[] = array('smslog_phone', '=', $smslog_phone);
        $condition[] = array('smslog_smstime', 'between', array(TIMESTAMP - 30, TIMESTAMP));
        if ($smslog_captcha && $this->getSmsCount($condition) > 0) {
            return array('state' => FALSE, 'code' => 10001, 'message' => '同一手机30秒后才能再次发送短信，请勿多次获取动态码！');
        }

        //同一手机号,每天只能发送5条短信
        $condition = array();
        $condition[] = array('smslog_phone', '=', $smslog_phone);
        $condition[] = array('smslog_smstime', 'between', array($begin_add_time, $end_add_time));
        if ($smslog_captcha && $this->getSmsCount($condition) > 5) {
            return array('state' => FALSE, 'code' => 10001, 'message' => '同一手机一天内只能发送5条短信，请勿多次获取动态码！');
        }

        // 相同的短信内容，一天不能发送3次
        $condition = array();
        $condition[] = array('smslog_msg', '=', $smslog_msg);
        $condition[] = array('smslog_smstime', 'between', array($begin_add_time, $end_add_time));
        if ($this->getSmsCount($condition) > 3) {
            return array('state' => FALSE, 'code' => 10001, 'message' => '相同的短信内容，一天不能发送3次！');
        }

        //通过手机号获取现绑定的客户信息
        if (empty($member_id) || empty($member_name)) {
            //通过手机号查询用户名
            $member = model('member')->getMemberInfo(array('member_mobile' => $smslog_phone));
            $member_id = isset($member['member_id']) ? $member['member_id'] : '0';
            $member_name = isset($member['member_name']) ? $member['member_name'] : '';
        }
        $smslog_state = 0;
        if (!$if_queue) {
            $sms = new \sendmsg\Sms();
            $send_result = $sms->send($smslog_phone, $smslog_param);

            if ($send_result['code'] == true) {
                $smslog_state = 1;
            } else {
                return array('state' => FALSE, 'code' => 10001, 'message' => $send_result['msg']);
            }
        } else {
            $smslog_msg = json_encode($smslog_param);
        }
        $log['smslog_phone'] = $smslog_phone;
        $log['smslog_captcha'] = $smslog_captcha;
        $log['smslog_ip'] = request()->ip();
        $log['smslog_msg'] = $smslog_msg;
        $log['smslog_type'] = $smslog_type;
        $log['smslog_smstime'] = TIMESTAMP;
        $log['member_id'] = $member_id;
        $log['member_name'] = $member_name;
        $log['smslog_state'] = $smslog_state;
        $result = $this->addSms($log);
        if ($result >= 0) {
            return array('state' => TRUE, 'code' => 10000, 'message' => '');
        } else {
            return array('state' => FALSE, 'code' => 10001, 'message' => '手机短信发送失败');
        }
    }

    /**
     * 修改短信记录
     * @access public
     * @author csdeshang
     * @param type $log_array 日志数组
     * @return type
     */
    public function editSms($update, $condition) {
        return Db::name('smslog')->where($condition)->update($update);
    }

    /**
     * 增加短信记录
     * @access public
     * @author csdeshang
     * @param type $log_array 日志数组
     * @return type
     */
    public function addSms($log_array) {
        $log_id = Db::name('smslog')->insertGetId($log_array);
        return $log_id;
    }

    /**
     * 查询单条记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return boolean
     */
    public function getSmsInfo($condition) {
        if (empty($condition)) {
            return false;
        }
        $result = Db::name('smslog')->where($condition)->order('smslog_id desc')->find();
        return $result;
    }

    /**
     * 查询记录
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $limit 限制
     * @param type $order 排序
     * @return type
     */
    public function getSmsList($condition = array(), $pagesize = '', $limit = 0, $order = 'smslog_id desc') {
        if ($pagesize) {
            $result = Db::name('smslog')->where($condition)->order($order)->paginate(['list_rows' => $pagesize, 'query' => request()->param()], false);
            $this->page_info = $result;
            $result = $result->items();
        } else {
            $result = Db::name('smslog')->where($condition)->limit($limit)->order($order)->select()->toArray();
        }

        return $result;
    }

    /**
     * 获取数据条数
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getSmsCount($condition) {
        return Db::name('smslog')->where($condition)->count();
    }

    /**
     * 删除短信记录
     */
    public function delSmsLog($condition) {
        return Db::name('smslog')->where($condition)->delete();
    }

}

?>
