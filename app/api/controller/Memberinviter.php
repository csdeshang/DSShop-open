<?php

namespace app\api\controller;
use think\facade\Db;
use think\facade\Lang;
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
 * 分销控制器
 */
class Memberinviter extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/memberinviter.lang.php');
        if (!config('ds_config.inviter_open')) {
            ds_json_encode(10001,lang('inviter_not_open'));
        }
    }

    

    /**
     * @api {POST} api/Memberinviter/check 检测是否有推广权限，符合条件自动新增推广员
     * @apiVersion 3.0.6
     * @apiGroup Memberinviter
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function check() {
        $inviter_model = model('inviter');
        $inviter_info = $inviter_model->getInviterInfo('i.inviter_id=' . $this->member_info['member_id']);
        if (!$inviter_info) {
            //是否有分销门槛
            if (config('ds_config.inviter_condition')) {
                //检查消费金额
                $temp = Db::name('order')->where('buyer_id=' . $this->member_info['member_id'] . ' AND order_state=' . ORDER_STATE_SUCCESS . ' AND lock_state=0')->field('SUM(order_amount) AS order_amount,SUM(refund_amount) AS refund_amount')->find();
                if (!$temp || ($temp['order_amount']-$temp['refund_amount']) < config('ds_config.inviter_condition_amount')) {
                    ds_json_encode(10001, sprintf(lang('inviter_condition_amount'), !$temp?0:($temp['order_amount']-$temp['refund_amount']), config('ds_config.inviter_condition_amount')));
                }
            }
            $inviter_model->addInviter(array(
                'inviter_id' => $this->member_info['member_id'],
                'inviter_state' => config('ds_config.inviter_view') ? 0 : 1,
                'inviter_applytime' => TIMESTAMP,
            ));
            if (config('ds_config.inviter_view')) {
                ds_json_encode(10001,lang('inviter_view'));
            } else {
                ds_json_encode(10000, '');
            }
        } else {
            if ($inviter_info['inviter_state'] == 0) {
                ds_json_encode(10001,lang('inviter_view'));
            } elseif ($inviter_info['inviter_state'] == 2) {
                ds_json_encode(10001,lang('inviter_close'));
            }else{
                ds_json_encode(10000, '');
            }
        }
    }

    /**
     * @api {POST} api/Memberinviter/index 首页显示
     * @apiVersion 3.0.6
     * @apiGroup Memberinviter
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.refer_qrcode_logo  分销海报
     * @apiSuccess {String} result.inviter_url  分销url
     * @apiSuccess {String} result.refer_qrcode_weixin  分销微信二维码
     * @apiSuccess {String} result.wx_error_msg  微信错误信息
     */
    public function index() {
        $member_info = $this->member_info;
        //生成微信推广二维码
        $inviter_model=model('inviter');
        $qrcode_weixin = $inviter_model->qrcode_weixin($member_info);
        //生成URL推广二维码
        $inviter_model->qrcode_logo($member_info);
        
        $result = array(
            'refer_qrcode_logo' => UPLOAD_SITE_URL . '/' . ATTACH_INVITER . '/' . $member_info['member_id'] . '_poster.png',
            'inviter_url' => config('ds_config.h5_site_url') . '/pages/home/memberregister/Register?inviter_id=' . $member_info['member_id'],
            'refer_qrcode_weixin' => $qrcode_weixin['refer_qrcode_weixin'],
            'wx_error_msg' => $qrcode_weixin['wx_error_msg']
        );
        ds_json_encode(10000, '',$result);
    }

    /**
     * @api {POST} api/Memberinviter/user 获取推广会员
     * @apiVersion 3.0.6
     * @apiGroup Memberinviter
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.list  用户列表
     * @apiSuccess {Int} result.list.member_id  用户ID
     * @apiSuccess {String} result.list.member_name  用户名称
     * @apiSuccess {String} result.list.member_avatar  用户头像
     * @apiSuccess {String} result.list.member_addtime  注册时间
     * @apiSuccess {String} result.list.member_logintime  登录时间
     * @apiSuccess {Object[]} result.list.inviters  上级分销员列表
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function user() {
        $member_model = model('member');
        $condition = array();
        $condition[] = array('inviter_id','=',$this->member_info['member_id']);
        if (input('param.member_name')) {
            $condition[] = array('member_name','like', '%' . input('param.member_name') . '%');
        }
        $list = $member_model->getMemberList($condition, 'member_id,member_name,member_avatar,member_addtime,member_logintime', 10, 'member_id desc');
        if (is_array($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['member_avatar'] = get_member_avatar($val['member_avatar']) . '?' . microtime();
                $list[$key]['member_addtime'] = $val['member_addtime'] ? date('Y-m-d H:i:s', $val['member_addtime']) : '';
                $list[$key]['member_logintime'] = $val['member_logintime'] ? date('Y-m-d H:i:s', $val['member_logintime']) : '';
                //该会员的2级内推荐会员
                $list[$key]['inviters'] = array();
                $inviter_1 = Db::name('member')->where('inviter_id', $val['member_id'])->field('member_id,member_name')->find();
                if ($inviter_1) {
                    $list[$key]['inviters'][] = $inviter_1['member_name'];
                    $inviter_2 = Db::name('member')->where('inviter_id', $inviter_1['member_id'])->field('member_id,member_name')->find();
                    if ($inviter_2) {
                        $list[$key]['inviters'][] = $inviter_2['member_name'];
                    }
                }
            }
        }
        $result = array_merge(array('list' => $list), mobile_page($member_model->page_info));
        ds_json_encode(10000, '',$result);
    }
    /**
     * @api {POST} api/Memberinviter/order 获取推广业绩
     * @apiVersion 3.0.6
     * @apiGroup Memberinviter
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.list  分销业绩列表 （返回字段参考orderinviter）
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function order() {
        $condition = array();
        $condition[] = array('orderinviter_member_id','=',$this->member_info['member_id']);
        if (input('param.orderinviter_order_sn')) {
            $condition[] = array('orderinviter_order_sn','like', '%' . input('param.orderinviter_order_sn') . '%');
        }
        $list = Db::name('orderinviter')->where($condition)->order('orderinviter_id desc')->paginate(['list_rows'=>10,'query' => request()->param()],false);
        $order_list=$list->items();
        foreach($order_list as $key => $val){
            $order_list[$key]['orderinviter_valid_text']=lang('orderinviter_valid_array')[$val['orderinviter_valid']];
        }
        $result = array_merge(array('list' => $order_list), mobile_page($list));
        ds_json_encode(10000, '',$result);
    }

}
