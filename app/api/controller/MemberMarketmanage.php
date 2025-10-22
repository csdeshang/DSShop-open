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
 * 用户营销活动控制器
 */
class MemberMarketmanage extends MobileMember {

    public function initialize() {
        parent::initialize();
    }

    /**
     * @api {POST} api/MemberMarketmanage/get_log 活动记录
     * @apiVersion 3.0.6
     * @apiGroup MemberMarketmanage
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function get_log() {
        $marketmanage_model = model('marketmanage');
        $marketmanagelog_list = $marketmanage_model->getMarketmanageLogList(array('member_id' => $this->member_info['member_id']), $this->pagesize);
        $result = array_merge(array('log_list' => $marketmanagelog_list), mobile_page($marketmanage_model->page_info));
        ds_json_encode(10000, '', $result);
    }

    /**
     * @api {POST} api/MemberMarketmanage/add_log 参加活动
     * @apiVersion 3.0.6
     * @apiGroup MemberMarketmanage
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {Int} marketmanage_id 活动ID
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function add_log() {
        $can_draw = true;
        $count_left = 0;
        $marketmanage_id = intval(input('param.marketmanage_id'));
        if ($marketmanage_id < 0) {
            ds_json_encode(10001, lang('param_error'));
        }
        $marketmanage_model = model('marketmanage');

        $predeposit_model = model('predeposit');
        Db::startTrans();
        try {

            $condition = array();
            $condition[] = array('marketmanage_id', '=', $marketmanage_id);
            $marketmanage = $marketmanage_model->getOneMarketmanage($condition);

            if (!$marketmanage) {
                throw new \think\Exception('没有该活动', 10006);
            }
            if ($marketmanage['marketmanage_begintime'] > TIMESTAMP) {
                throw new \think\Exception('活动未开始', 10006);
            }
            if ($marketmanage['marketmanage_endtime'] < TIMESTAMP) {
                throw new \think\Exception('活动已结束', 10006);
            }

            //是否有足够的积分
            if ($this->member_info['member_points'] < $marketmanage['marketmanage_point']) {
                throw new \think\Exception('您没有足够的积分', 10006);
            }

            if ($marketmanage['marketmanage_jointype'] != 2) {//有参与次数限制
                //判断当前用户是否参与过
                $condition = array();
                $condition[] = array('marketmanage_id', '=', $marketmanage_id);
                $condition[] = array('member_id', '=', $this->member_info['member_id']);
                $marketmanage_joincount = $marketmanage['marketmanage_joincount'];
                switch ($marketmanage['marketmanage_jointype']) {
                    case 0:
                        break;
                    case 1:
                        $condition[] = array('marketmanagelog_time', 'between', array(strtotime(date('Y-m-d 0:0:0')), TIMESTAMP));
                        break;
                    default :
                        throw new \think\Exception('活动参与类型设置错误', 10006);
                }
                $marketmanagelog = $marketmanage_model->getMarketmanageLogList($condition);
                if (count($marketmanagelog) >= $marketmanage_joincount) {
                    throw new \think\Exception('您已经参与' . count($marketmanagelog) . '次了，请下次再来', 10006);
                }

                $count_left = $marketmanage_joincount - count($marketmanagelog) - 1;
                if ($count_left == 0) {
                    $can_draw = false;
                }
            }
            $marketmanage_type_list = $marketmanage_model->marketmanage_type_list();
            if (!isset($marketmanage_type_list[$marketmanage['marketmanage_type']])) {
                throw new \think\Exception('活动类型设置错误', 10006);
            }
            if ($marketmanage['marketmanage_point'] > 0) {
                //扣除会员积分
                $insert_arr = array();
                $insert_arr['pl_memberid'] = $this->member_info['member_id'];
                $insert_arr['pl_membername'] = $this->member_info['member_name'];
                $insert_arr['pl_points'] = -$marketmanage['marketmanage_point'];
                $insert_arr['pl_desc'] = '参加' . $marketmanage_type_list[$marketmanage['marketmanage_type']] . '消耗积分';
                $flag = model('points')->savePointslog('marketmanage', $insert_arr, true);
                if (!$flag) {
                    throw new \think\Exception('积分扣除失败', 10006);
                }
            }

            //查看是否还有奖品
            $condition = 'marketmanage_id=' . $marketmanage_id . ' AND marketmanageaward_count>marketmanageaward_send AND marketmanageaward_probability>0';
            $marketmanageaward = $marketmanage_model->getMarketmanageAwardList($condition);
            if (empty($marketmanageaward)) {
                $result = array('draw_result' => false); //未中奖
            } else {
                $pro = array();
                $sum = 0;
                $marketmanageaward_list = array();
                foreach ($marketmanageaward as $val) {
                    $sum += $val['marketmanageaward_probability'];
                    $pro[] = array('marketmanageaward_probability' => $val['marketmanageaward_probability'], 'marketmanageaward_id' => $val['marketmanageaward_id']);
                    $marketmanageaward_list[$val['marketmanageaward_id']] = $val;
                }
                $total_percent = count($pro) * 100;
                $pro[] = array('marketmanageaward_probability' => ($total_percent - $sum), 'marketmanageaward_id' => 0); //未中奖概率
                $pro = array_reverse($pro); //从未中奖到一等奖排序
                $sum = $total_percent;
                foreach ($pro as $v) {
                    $r = mt_rand(1, $sum);
                    if ($r <= $v['marketmanageaward_probability']) {
                        if ($v['marketmanageaward_id'] == 0) {
                            $result = array('draw_result' => false); //未中奖
                        } else {
                            $result = array('draw_result' => true, 'draw_info' => $marketmanageaward_list[$v['marketmanageaward_id']]); //已中奖
                        }

                        break;
                    } else {
                        $sum = max(0, $sum - $v['marketmanageaward_probability']);
                    }
                }
                if ($result['draw_result']) {//中奖后续操作
                    switch ($result['draw_info']['marketmanageaward_type']) {
                        case 1://奖励积分
                            $result['draw_info']['marketmanageaward_text'] = $result['draw_info']['marketmanageaward_point'] . '积分';
                            //扣除会员积分
                            $insert_arr = array();
                            $insert_arr['pl_memberid'] = $this->member_info['member_id'];
                            $insert_arr['pl_membername'] = $this->member_info['member_name'];
                            $insert_arr['pl_points'] = $result['draw_info']['marketmanageaward_point'];
                            $insert_arr['pl_desc'] = '参加' . $marketmanage_type_list[$marketmanage['marketmanage_type']] . '中奖得到积分';
                            $flag = model('points')->savePointslog('marketmanage', $insert_arr, true);
                            if (!$flag) {
                                throw new \think\Exception('积分增加失败', 10006);
                            }
                            break;
                        case 2://奖励红包
                            $bonus_model = model('bonus');
                            $condition = array('bonus_id' => $result['draw_info']['bonus_id']);
                            $bonus = $bonus_model->getOneBonus($condition); //获取当前红包的领取金额
                            if (!$bonus) {
                                throw new \think\Exception('红包设置错误', 10006);
                            }
                            if ($bonus['bonus_type'] != 3) {
                                throw new \think\Exception('红包设置错误', 10006);
                            }
                            //获取未领取单个红包
                            $condition = array();
                            $condition[] = array('bonus_id', '=', $result['draw_info']['bonus_id']);
                            $condition[] = array('member_id', '=', 0);
                            $bonusreceive = $bonus_model->getOneBonusreceive($condition);
                            if (empty($bonusreceive)) {
                                throw new \think\Exception('红包已发完', 10006);
                            }
                            $result['draw_info']['marketmanageaward_text'] = $bonusreceive['bonusreceive_price'] . '元红包';

                            $res = $bonus_model->receiveBonus($this->member_info, $bonus, $bonusreceive, $marketmanage_type_list[$marketmanage['marketmanage_type']] . '红包');
                            if (!$res['code']) {
                                throw new \think\Exception($res['msg'], 10006);
                            }
                            break;
                        case 3://奖励优惠券
                            $voucher_model = model('voucher');
                            //验证是否可以兑换代金券
                            $data = $voucher_model->getCanChangeTemplateInfo($result['draw_info']['vouchertemplate_id'], $this->member_info['member_id'], 0, true);
                            if ($data['state'] == false) {
                                throw new \think\Exception($data['msg'], 10006);
                            }

                            $result['draw_info']['marketmanageaward_text'] = $data['info']['vouchertemplate_price'] . '元代金券';
                            //添加代金券信息
                            $data = $voucher_model->exchangeVoucher($data['info'], $this->member_info['member_id'], $this->member_info['member_name'], true);
                            if ($data['state'] != true) {
                                throw new \think\Exception($data['msg'], 10006);
                            }
                            break;
                        default:
                            throw new \think\Exception('活动奖品类型设置错误', 10006);
                    }
                    if ($result['draw_result']) {
                        //增加中奖数量
                        $condition = array();
                        $condition[] = array('marketmanageaward_id', '=', $result['draw_info']['marketmanageaward_id']);
                        $flag = $marketmanage_model->editMarketmanageAward($condition, array('marketmanageaward_send' => $result['draw_info']['marketmanageaward_send'] + 1));
                        if (!$flag) {
                            //                        echo $marketmanage_model->getLastSql();
                            throw new \think\Exception('更新中奖总数失败', 10006);
                        }
                        //更新中奖总数
                        $condition = array();
                        $condition[] = array('marketmanage_id', '=', $marketmanage_id);
                        $flag = $marketmanage_model->editMarketmanage($condition, array('marketmanage_totalwin' => $marketmanage['marketmanage_totalwin'] + 1));
                        if (!$flag) {
                            throw new \think\Exception('更新中奖总数失败', 10006);
                        }
                    }
                }

                //增加参与记录
                $flag = $marketmanage_model->addMarketmanageLog(array(
                    'member_id' => $this->member_info['member_id'],
                    'member_name' => $this->member_info['member_name'],
                    'marketmanage_id' => $marketmanage_id,
                    'marketmanageaward_id' => $result['draw_result'] ? $result['draw_info']['marketmanageaward_id'] : 0,
                    'marketmanagelog_win' => $result['draw_result'] ? 1 : 0,
                    'marketmanagelog_time' => TIMESTAMP,
                    'marketmanagelog_remark' => '参与' . $marketmanage['marketmanage_name'] . ($result['draw_result'] ? ('中' . $result['draw_info']['marketmanageaward_level'] . '等奖') : '未中奖'),
                ));
                if (!$flag) {
                    throw new \think\Exception('新增参与记录失败', 10006);
                }
                //更新参与总数
                $condition = array();
                $condition[] = array('marketmanage_id', '=', $marketmanage_id);
                $flag = $marketmanage_model->editMarketmanage($condition, array('marketmanage_totalcount' => $marketmanage['marketmanage_totalcount'] + 1));
                if (!$flag) {
                    throw new \think\Exception('更新参与总数失败', 10006);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            ds_json_encode(10001, $e->getMessage());
        }
        $result['can_draw'] = $can_draw;
        $result['count_left'] = $count_left;
        ds_json_encode(10000, '', $result);
    }
}
