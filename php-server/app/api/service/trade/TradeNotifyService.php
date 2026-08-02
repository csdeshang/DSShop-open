<?php



namespace app\api\service\trade;

use think\facade\Db;

use app\deshang\base\BaseService;
use app\deshang\core\ThirdPartyLoader;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\PayException;
use app\deshang\service\user\DeshangUserBalanceService;
use app\deshang\service\order\DeshangTblOrderService;

use app\common\enum\trade\TradePayEnum;
use app\common\enum\trade\TradePaymentConfigEnum;
use app\common\enum\user\UserBalanceEnum;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderMergeEnum;

use app\common\dao\system\SysAccessLogsDao;
use app\common\dao\trade\TradePayLogDao;
use app\common\dao\user\UserRechargeLogDao;
use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderMergeDao;


class TradeNotifyService extends BaseService
{


    public function __construct()
    {
        parent::__construct();
    }


    public function notify(string $trade_channel, string $trade_scene, string $trade_type)
    {



        // 验证来源类型
        if (!in_array($trade_channel, array_keys(TradePaymentConfigEnum::getPaymentChannelDict()))) {
            throw new CommonException('来源类型错误');
        }

        // 验证来源场景
        if (!in_array($trade_scene, array_keys(TradePaymentConfigEnum::getPaymentSceneDict()))) {
            throw new CommonException('来源场景错误');
        }

        // 验证来源类型
        if (!in_array($trade_type, array('pay', 'refund'))) {
            throw new CommonException('来源类型错误');
        }

        $trade = ThirdPartyLoader::trade($trade_channel, $trade_scene, $trade_type);



        if ($trade_type == 'pay') {
            // 支付相关的回调处理

            // 接收回调
            $result = $trade->callback();
            if ($result['status'] == 'success') {

                Db::startTrans();
                try {
                    $this->updateTradePayLog($result);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
                    throw $e;
                }
                // 确认回调
                return $trade->confirm();


            } else {
                throw new PayException('支付失败');
            }
        } else if ($trade_type == 'refund') {
            // 退款相关的回调处理

            // 退款只要发起成功 就进行退款，回调不进行处理
            // 确认回调
            return $trade->confirm();
        }





        // $log = array(
        //     'user_id' => '',
        //     'username' => '',
        //     'ip' => '',
        //     'root' => '',
        //     'method' => '',
        //     'controller' => '',
        //     'action' => '',
        //     'url' => '',
        //     'params' => '', //请求参数 占用较大 不需要可删除
        //     'result' => json_encode($result, true), //返回结果 占用较大 不需要可删除
        //     'duration' => '',
        //     'http_code' => '',
        //     'code' => '',
        // );
        // (new SysAccessLogsDao())->createAccessLog($log);
    }


    // 更新支付回调
    private function updateTradePayLog($result)
    {
        // 根据out_trade_no 查询支付记录
        $trade_pay_log = (new TradePayLogDao())->getTradePayLogInfo(['out_trade_no' => $result['out_trade_no']]);

        if (empty($trade_pay_log)) {
            throw new PayException('支付记录不存在');
        }

        // 状态是否成功
        if ($trade_pay_log['pay_status'] == TradePayEnum::PAY_STATUS_SUCCESS) {
            throw new PayException('支付记录已成功');
        }

        // 金额是否匹配
        if ($trade_pay_log['pay_amount'] != $result['total_amount']) {
            throw new PayException('支付记录金额不匹配');
        }


        // 获取支付类型
        $source_type = $trade_pay_log['source_type'];
        // 关联ID
        $source_id = $trade_pay_log['source_id'];
        if (!in_array($source_type, array_keys(TradePayEnum::getSourceTypeDict()))) {
            throw new PayException('支付类型错误');
        }



        if ($source_type == TradePayEnum::SOURCE_TYPE_RECHARGE) {
            // 充值

            // 获取充值信息
            $recharge_log = (new UserRechargeLogDao())->getRechargeLogInfo([['id', '=', $source_id], ['out_trade_no', '=', $result['out_trade_no']]]);
            if (empty($recharge_log)) {
                throw new CommonException('充值记录不存在');
            }
            // 状态是否成功
            if ($recharge_log['recharge_status'] == 1) {
                throw new CommonException('充值记录已成功');
            }

            //金额是否匹配
            if ($recharge_log['recharge_amount'] != $result['total_amount']) {
                throw new CommonException('充值金额不匹配');
            }

            // 更新充值状态
            $update_data = [
                'recharge_status' => 1,
                'trade_no' => $result['trade_no'],
                'pay_time' => time(),
            ];
            (new UserRechargeLogDao())->updateRechargeLog([['id', '=', $source_id], ['out_trade_no', '=', $result['out_trade_no']]], $update_data);

            // 更新用户余额
            $add_data = array(
                'user_id' => $recharge_log['user_id'],
                'related_id' => $recharge_log['id'],
                'change_type' => UserBalanceEnum::TYPE_RECHARGE,
                'change_mode' => UserBalanceEnum::MODE_INCREASE,
                'change_amount' => $recharge_log['recharge_amount'],
                'change_desc' => '充值成功',
            );
            (new DeshangUserBalanceService())->modifyUserBalance($add_data);
        } else if ($source_type == TradePayEnum::SOURCE_TYPE_ORDER) {
            // 订单

            // 获取订单信息
            $order_info = (new TblOrderDao())->getOrderInfo([['id', '=', $source_id], ['out_trade_no', '=', $result['out_trade_no']]]);
            if (empty($order_info)) {
                throw new CommonException('订单记录不存在');
            }

            // 订单是否是未支付状态
            if ($order_info['order_status'] != TblOrderEnum::ORDER_STATUS_PENDING) {
                throw new PayException('订单状态不是未支付状态');
            }

            // 金额是否匹配
            if ($order_info['pay_amount'] != $result['total_amount']) {
                throw new CommonException('订单金额不匹配');
            }


            // 更新订单状态(不同的 platform 付完款之后的状态都是 一致的) ， 支付成功回调通用。
            $extra = [
                'pay_channel' => $trade_pay_log['pay_channel'],
                'pay_scene' => $trade_pay_log['pay_scene'],
                'trade_no' => $result['trade_no'],
            ];
            (new DeshangTblOrderService())->changeTblOrderReceivePay($order_info, 'system', '0', $extra);
        } else if ($source_type == TradePayEnum::SOURCE_TYPE_ORDER_MERGE) {

            // 获取合并支付信息
            $order_merge = (new TblOrderMergeDao())->getOrderMergeInfo([['id', '=', $source_id],['out_trade_no', '=', $result['out_trade_no']]]);
            if (empty($order_merge)) {
                throw new PayException('合并支付记录不存在');
            }

            // 合并支付的订单状态是否是待支付状态
            if ($order_merge['status'] != TblOrderMergeEnum::ORDER_MERGE_STATUS_PENDING) {
                throw new PayException('合并支付状态不是待支付状态');
            }

            // 获取合并支付的订单列表
            $order_list = (new TblOrderDao())->getOrderList([['order_merge_id', '=', $source_id]]);
            if (empty($order_list)) {
                throw new PayException('合并支付订单列表为空');
            }

            $total_amount = 0;
            // 检测合并支付订单
            foreach ($order_list as $order_info) {
                // 订单是否是未支付状态
                if ($order_info['order_status'] != TblOrderEnum::ORDER_STATUS_PENDING) {
                    throw new PayException('订单状态不是未支付状态');
                }

                $total_amount += $order_info['pay_amount'];
            }

            // 金额是否匹配
            if ($total_amount != $result['total_amount']) {
                throw new PayException('合并支付金额不匹配');
            }

            // 更新合并支付下所有订单
            foreach ($order_list as $order_info) {
                // 更新订单状态(不同的 platform 付完款之后的状态都是 一致的) ， 支付成功回调通用。
                $extra = [
                    'pay_channel' => $trade_pay_log['pay_channel'],
                    'pay_scene' => $trade_pay_log['pay_scene'],
                    'trade_no' => $result['trade_no'],
                ];
                (new DeshangTblOrderService())->changeTblOrderReceivePay($order_info, 'system', '0', $extra);
            }

            // 更新合并支付状态
            $update_data = [
                'status' => TblOrderMergeEnum::ORDER_MERGE_STATUS_PAID,
                'trade_no' => $result['trade_no'],
                'pay_channel' => $trade_pay_log['pay_channel'],
                'pay_scene' => $trade_pay_log['pay_scene'],
            ];
            (new TblOrderMergeDao())->updateOrderMerge([['id', '=', $source_id],['out_trade_no', '=', $result['out_trade_no']]], $update_data);



        }else{
            throw new PayException('支付类型错误');
        }



        // 修改支付记录状态
        $update_data = [
            'pay_status' => TradePayEnum::PAY_STATUS_SUCCESS,
            'pay_time' => time(),
            'trade_no' => $result['trade_no'],
            'buyer_id' => $result['buyer_id'],
            'seller_id' => $result['seller_id'],
        ];
        (new TradePayLogDao())->updateTradePayLog([['id', '=', $trade_pay_log['id']]], $update_data);
    }
}
