<?php

namespace app\api\controller;

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
 * 领取红包控制器
 */
class Memberbonus extends MobileMember {

    public function initialize() {
        parent::initialize();
    }

    /**
     * @api {POST} api/Memberbonus/get_receive_list 红包记录
     * @apiVersion 3.0.6
     * @apiGroup Memberbonus
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function get_receive_list() {
        $bonus_model = model('bonus');
        $bonuslog_list = $bonus_model->getBonusreceiveList(array('member_id' => $this->member_info['member_id']), $this->pagesize);
        $result = array_merge(array('log_list' => $bonuslog_list), mobile_page($bonus_model->page_info));
        ds_json_encode(10000, '', $result);
    }

    /**
     * @api {POST} api/Memberbonus/receive 活动红包领取
     * @apiVersion 3.0.6
     * @apiGroup Memberbonus
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} bonus_id 活动ID
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function receive() {
        $bonus_id = intval(input('param.bonus_id'));
        if ($bonus_id < 0) {
            ds_json_encode(10001, '活动红包错误');
        }
        $bonus_model = model('bonus');
        $condition = array();
        $condition[] = array('bonus_id', '=', $bonus_id);
        $bonus = $bonus_model->getOneBonus($condition); //获取当前红包的领取金额
        if ($bonus['bonus_begintime'] > TIMESTAMP) {
            ds_json_encode(10001, '活动红包未开始');
        }
        if ($bonus['bonus_type'] != 1) {
            ds_json_encode(10001, '不是活动红包不能直接领取');
        }
        if ($bonus['bonus_state'] == 2 || TIMESTAMP > $bonus['bonus_endtime']) {
            ds_json_encode(10001, '活动红包已过期');
        }
        if ($bonus['bonus_state'] == 3) {
            ds_json_encode(10001, '活动红包已失效');
        }

        //判断当前用户是否领取过
        $condition = array();
        $condition[] = array('bonus_id', '=', $bonus_id);
        $condition[] = array('member_id', '=', $this->member_info['member_id']);
        $bonusreceive = $bonus_model->getOneBonusreceive($condition);
        if ($bonusreceive) {
            ds_json_encode(10001, '您在' . date('Y-m-d H:i:s', $bonusreceive['bonusreceive_time']) . '已领取过此活动红包,领取的金额是:' . $bonusreceive['bonusreceive_price']);
        }
        //获取未领取单个红包
        $condition = array();
        $condition[] = array('bonus_id', '=', $bonus_id);
        $condition[] = array('member_id', '=', 0);
        $bonusreceive = $bonus_model->getOneBonusreceive($condition);
        if (empty($bonusreceive)) {
            ds_json_encode(10001, '活动红包已领完');
        }

        Db::startTrans();
        try {
            $res = $bonus_model->receiveBonus($this->member_info, $bonus, $bonusreceive, '领取活动红包');
            if (!$res['code']) {
                throw new \think\Exception($res['msg'], 10006);
            }
            Db::commit();
            ds_json_encode(10000, '获得' . $bonusreceive['bonusreceive_price'] . '元红包');
        } catch (\Exception $e) {
            Db::rollback();
            ds_json_encode(10001, $e->getMessage());
        }
    }
}
