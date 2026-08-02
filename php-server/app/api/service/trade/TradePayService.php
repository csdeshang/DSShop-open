<?php



namespace app\api\service\trade;

use app\deshang\base\service\BaseUserService;

use app\common\enum\trade\TradePayEnum;
use app\common\enum\trade\TradePaymentConfigEnum;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderMergeEnum;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\PayException;
use app\deshang\exceptions\SystemException;
use app\deshang\core\ThirdPartyLoader;

use app\deshang\service\order\DeshangTblOrderService;
use app\common\enum\user\UserBalanceEnum;
use app\deshang\service\user\DeshangUserBalanceService;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderMergeDao;
use app\common\dao\trade\TradePayLogDao;
use app\common\dao\user\UserRechargeLogDao;
use app\common\dao\user\UserIdentityDao;
use app\common\dao\user\UserDao;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\LockKeyManager;

use think\facade\Db;



class TradePayService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }


    public function pay($data)
    {
        // 加锁
        $lockKey = sprintf(LockKeyManager::LOCK_API_TRADE_PAY_KEY, '_' . $this->user_id . '_' . md5(serialize($data)));
        $lockValue = KvManager::lock()->acquire($lockKey, 30);
        if (!$lockValue) {
            throw new SystemException('支付正在处理中，请稍后再试');
        }

        try {
            // 处理支付
            $result = $this->handlePay($data);
            return $result;
        } finally {
            // 释放锁
            KvManager::lock()->release($lockValue, $lockKey);
        }
    }


    private function handlePay($data)
    {

        $data['user_id'] = $this->user_id;

        // 验证来源类型
        if (!array_key_exists($data['source_type'], TradePayEnum::getSourceTypeDict())) {
            throw new PayException('来源类型错误');
        }

        // 验证支付场景
        if (!array_key_exists($data['pay_scene'], TradePaymentConfigEnum::getPaymentSceneDict())) {
            throw new PayException('支付场景错误');
        }

        // 验证支付渠道
        if (!array_key_exists($data['pay_channel'], TradePaymentConfigEnum::getPaymentChannelDict())) {
            throw new PayException('支付渠道错误');
        }

        if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_OFFLINE) {
            // 直接跳转 订单详情 ， 相当于不做任何操作
            // 返回数据与余额支付完成一致（待确认）
            throw new PayException('线下付款的支付渠道不能是线下付款');
        }

        // 验证支付金额
        if ($data['pay_amount'] <= 0) {
            throw new PayException('支付金额错误');
        }


        // 每次申请支付，重新生成out_trade_no （ 为了确保支付流程的透明性和可追溯性，每次申请支付会记录 提交参数以及返回参数）
        // 虽说 如果订单未支付，可以使用相同的 out_trade_no 重新发起支付 ，但是如果修改了提交参数也是无效 ，这样可以避免因重复使用 out_trade_no 导致的问题 
        $out_trade_no = generateOutTradeNo($data['pay_channel'], $data['user_id']);

        // 如果是充值
        if ($data['source_type'] == TradePayEnum::SOURCE_TYPE_RECHARGE) {
            // 充值的支付渠道 不能是 余额支付
            if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_BALANCE) {
                throw new PayException('充值的支付渠道不能是余额支付');
            }

            $recharge_log = [
                'user_id' => $data['user_id'],
                'out_trade_no' => $out_trade_no,
                'pay_channel' => $data['pay_channel'],
                'pay_scene' => $data['pay_scene'],
                'recharge_amount' => $data['pay_amount'],
                'recharge_status' => 0,
            ];

            // 生成充值记录
            $id = (new UserRechargeLogDao())->createRechargeLog($recharge_log);

            // 需要 取得 source_id  out_trade_no
            $data['source_id'] = $id;
        } else if ($data['source_type'] == TradePayEnum::SOURCE_TYPE_ORDER) {
            // 单店铺订单
            // 订单支付 必须有来源id 和 商户订单号
            if ($data['source_id'] == 0) {
                throw new PayException('订单支付 必须有来源id 和 商户订单号');
            }

            // 检测当前订单 source_id 是否存在 以及 是否属于当前用户 和 订单是否是未付款状态
            $order = (new TblOrderDao())->getOrderInfo([
                ['id', '=', $data['source_id']],
                ['user_id', '=', $data['user_id']]
            ], '*', true);

            if (empty($order)) {
                throw new PayException('支付的订单不存在,订单ID:' . $data['source_id']);
            }
            // 订单是否是未付款状态
            if ($order['order_status'] != TblOrderEnum::ORDER_STATUS_PENDING) {
                throw new PayException('订单状态并不是未付款状态');
            }
            // 订单金额是否匹配
            if ($order['pay_amount'] != $data['pay_amount']) {
                throw new PayException('订单金额不匹配');
            }

            // 如果当前订单是合并订单支付，但是在订单列表发起单个订单支付，则需要修改合并支付的订单状态
            if ($order['order_merge_id'] > 0) {
                (new TblOrderMergeDao())->updateOrderMerge([['id', '=', $order['order_merge_id']]], ['status' => TblOrderMergeEnum::ORDER_MERGE_STATUS_CLOSED]);
            }

            // 如果是余额支付
            if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_BALANCE) {


                // 获取用户信息
                $user_info = (new UserDao())->getUserInfoById($data['user_id']);
                // 对用户余额进行扣款
                if ($user_info['balance'] < $data['pay_amount']) {
                    throw new PayException('用户余额不足');
                }

                // 使用余额支付 ， 扣款， 商户收到货款， 订单状态更新
                $this->payOrderByBalance($order, $data['pay_scene']);

                // 创建支付记录（余额支付成功）
                $trade_pay_log = [
                    'user_id' => $data['user_id'],
                    'source_type' => $data['source_type'],
                    'source_id' => $data['source_id'],
                    'out_trade_no' => $out_trade_no,
                    'pay_channel' => TradePaymentConfigEnum::CHANNEL_BALANCE,
                    'pay_scene' => $data['pay_scene'],
                    'pay_amount' => $data['pay_amount'],
                    'pay_status' => TradePayEnum::PAY_STATUS_SUCCESS, // 余额支付直接成功
                    'pay_time' => time(),
                ];
                $trade_pay_log_id = (new TradePayLogDao())->createTradePayLog($trade_pay_log);

                // 返回统一格式数据
                return [
                    'trade_pay_result' => '余额支付成功',
                    'trade_pay_log_id' => $trade_pay_log_id,
                ];
            } else {
                // 修改订单的 out_trade_no
                (new TblOrderDao())->updateOrder([['id', '=', $data['source_id']]], ['out_trade_no' => $out_trade_no]);
            }
        } else if ($data['source_type'] == TradePayEnum::SOURCE_TYPE_ORDER_MERGE) {
            // 合并支付订单(多店铺一起支付)

            // 合并支付 必须有来源id 和 商户订单号
            if ($data['source_id'] == 0) {
                throw new PayException('合并支付 必须有来源id 和 商户订单号');
            }

            // 获取合并支付的支付信息
            $order_merge = (new TblOrderMergeDao())->getOrderMergeInfo([['id', '=', $data['source_id']], ['user_id', '=', $data['user_id']]]);
            if (!$order_merge) {
                throw new PayException('合并支付订单不存在');
            }

            // 合并支付的订单状态是否是待支付状态
            if ($order_merge['status'] != TblOrderMergeEnum::ORDER_MERGE_STATUS_PENDING) {
                throw new PayException('合并支付状态不是待支付状态');
            }

            // 合并支付的订单金额是否匹配
            if ($order_merge['total_amount'] != $data['pay_amount']) {
                throw new PayException('合并支付金额不匹配');
            }

            // 获取当前合并支付的订单列表
            $order_list = (new TblOrderDao())->getOrderList([['order_merge_id', '=', $data['source_id']], ['user_id', '=', $data['user_id']]]);
            if (empty($order_list)) {
                throw new PayException('合并支付订单列表为空');
            }
            // 检测合并支付的订单
            $total_amount = 0;
            foreach ($order_list as $order) {
                // 检测订单是否是未付款状态
                if ($order['order_status'] != TblOrderEnum::ORDER_STATUS_PENDING) {
                    throw new PayException('订单状态并不是未付款状态');
                }
                $total_amount += $order['pay_amount'];
            }
            // 检测合并支付的订单金额是否匹配
            if ($total_amount != $data['pay_amount']) {
                throw new PayException('合并支付金额不匹配');
            }


            // 如果合并支付  是余额支付
            if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_BALANCE) {
                // 获取用户信息
                $user_info = (new UserDao())->getUserInfoById($data['user_id']);
                // 对用户余额进行扣款
                if ($user_info['balance'] < $data['pay_amount']) {
                    throw new PayException('用户余额不足');
                }




                // 循环处理每个订单 
                foreach ($order_list as $order) {
                    // 使用余额支付 ， 扣款， 商户收到货款， 订单状态更新
                    $this->payOrderByBalance($order, $data['pay_scene']);
                }

                // 修改合并支付的订单状态
                (new TblOrderMergeDao())->updateOrderMerge([['id', '=', $data['source_id']]], ['status' => TblOrderMergeEnum::ORDER_MERGE_STATUS_PAID]);

                // 创建支付记录（余额支付成功）
                $trade_pay_log = [
                    'user_id' => $data['user_id'],
                    'source_type' => $data['source_type'],
                    'source_id' => $data['source_id'],
                    'out_trade_no' => $out_trade_no,
                    'pay_channel' => TradePaymentConfigEnum::CHANNEL_BALANCE,
                    'pay_scene' => $data['pay_scene'],
                    'pay_amount' => $data['pay_amount'],
                    'pay_status' => TradePayEnum::PAY_STATUS_SUCCESS,
                    'pay_time' => time(),
                ];
                $trade_pay_log_id = (new TradePayLogDao())->createTradePayLog($trade_pay_log);

                // 返回统一格式数据
                return [
                    'trade_pay_result' => '余额支付成功[合并支付]',
                    'trade_pay_log_id' => $trade_pay_log_id,
                ];
            } else {
                // 修改合并支付的 out_trade_no
                (new TblOrderMergeDao())->updateOrderMerge([['id', '=', $data['source_id']]], ['out_trade_no' => $out_trade_no]);
                // 修改每一个订单的 out_trade_no
                foreach ($order_list as $order) {
                    (new TblOrderDao())->updateOrder([['id', '=', $order['id']]], ['out_trade_no' => $out_trade_no]);
                }
            }
        } else {
            throw new PayException('来源类型错误');
        }




        //source_id  out_trade_no  pay_channel  payment_scene pay_amount
        // 平台自营：支付配置按平台配置加载（notify 路径仍传 0）

        // 插入支付记录
        $trade_pay_log = [
            'user_id' => $data['user_id'],
            'source_type' => $data['source_type'],
            'source_id' => $data['source_id'],
            'out_trade_no' => $out_trade_no,
            // 'trade_no' => $data['trade_no'],
            'pay_channel' => $data['pay_channel'],
            'pay_scene' => $data['pay_scene'],
            'pay_amount' => $data['pay_amount'],
            'pay_status' => TradePayEnum::PAY_STATUS_PENDING,
        ];
        $trade_pay_log_id = (new TradePayLogDao())->createTradePayLog($trade_pay_log);


        // 调用支付接口

        $return_url = '';
        $quit_url = '';
        $trade = ThirdPartyLoader::trade($data['pay_channel'], $data['pay_scene'], 'pay', $return_url, $quit_url);

        // 支付数据
        $pay_data = [
            'out_trade_no' => $out_trade_no,
            'total_amount' => $data['pay_amount'],
            'subject' => '支付订单',
        ];



        // 不同支付方式 下的不同支付场景 参数不同 需要单独处理

        if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_ALIPAY) {
            // 根据场景调用不同支付接口
            if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_PC) {
                //文档 https://opendocs.alipay.com/open/59da99d0_alipay.trade.page.pay?scene=22&pathHash=e26b497f
                try {
                    $result = $trade->web($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('支付宝支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_H5) {
                // 文档 
                try {
                    $result = $trade->h5($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('支付宝支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_APP) {
                try {
                    $result = $trade->app($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('支付宝支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else {
                throw new PayException('支付宝支付场景错误');
            }
        } else if ($data['pay_channel'] == TradePaymentConfigEnum::CHANNEL_WECHAT) {



            // 文档 https://pay.weixin.qq.com/doc/v3/merchant/4012791856
            if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_WECHAT_OFFICIAL) {

                // 公众号支付 需要 获取用户openid
                $user_identity = (new UserIdentityDao())->getUserIdentityInfo([['user_id', '=', $data['user_id']]]);
                if (empty($user_identity)) {
                    throw new PayException('用户未绑定微信');
                }

                // 公众号支付 需要 获取用户openid
                $pay_data['openid'] = $user_identity['wx_oauth_openid'];
                if (empty($pay_data['openid'])) {
                    throw new PayException('请先绑定微信，再进行支付');
                }

                try {
                    $result = $trade->mp($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('微信支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_WECHAT_MINI) {

                // 小程序支付 需要 获取用户openid
                $user_identity = (new UserIdentityDao())->getUserIdentityInfo([['user_id', '=', $data['user_id']]]);
                if (empty($user_identity)) {
                    throw new PayException('用户未绑定微信');
                }


                // 小程序支付 需要 获取用户openid
                $pay_data['openid'] = $user_identity['wx_mini_openid'];
                if (empty($pay_data['openid'])) {
                    throw new PayException('用户未绑定微信');
                }
                try {
                    $result = $trade->mini($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('微信支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else if ($data['pay_scene'] == TradePaymentConfigEnum::SCENE_PC) {
                // 扫码支付不需要openid
                try {
                    $result = $trade->scan($pay_data);
                } catch (\Exception $e) {
                    throw new PayException('微信支付失败, 错误信息:' . $e->getMessage() . ' 错误码:' . $e->getCode());
                }
            } else {
                throw new PayException('微信支付场景错误');
            }
        } else {
            throw new PayException('支付渠道错误');
        }

        // 包装返回数据：保留原有格式，添加支付记录ID
        return [
            // 第三方支付的原始返回数据（保持原有格式）
            'trade_pay_result' => $result,
            // 支付记录ID
            'trade_pay_log_id' => $trade_pay_log_id,
        ];
    }



    // 用户使用余额支付单个订单  用户扣款， 商户收到货款， 订单状态更新
    private function payOrderByBalance($order, $pay_scene)
    {
        // 事务处理
        Db::startTrans();
        try {
            // 扣款
            $balance_data = [
                'change_mode' => UserBalanceEnum::MODE_DECREASE,
                'change_type' => UserBalanceEnum::TYPE_ORDER,
                'change_amount' => $order['pay_amount'],
                'user_id' => $order['user_id'],
                'related_id' => $order['id'],
                'change_desc' => '余额支付订单' . $order['id'],
            ];
            (new DeshangUserBalanceService())->modifyUserBalance($balance_data);


            // 更新订单状态(不同的 platform 付完款之后的状态都是 一致的) ， 支付成功回调通用。
            $extra = [
                'pay_channel' => TradePaymentConfigEnum::CHANNEL_BALANCE,
                'pay_scene' => $pay_scene,

            ];
            (new DeshangTblOrderService())->changeTblOrderReceivePay($order, 'system', $order['user_id'], $extra);


            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
    }

    /**
     * 根据 trade_pay_log_id 查询支付状态
     * @param array $data 查询参数
     * @return array
     */
    public function getPayStatus($data)
    {
        if (empty($data['trade_pay_log_id'])) {
            throw new PayException('支付记录ID不能为空');
        }

        // 查询支付记录，必须属于当前用户
        $condition = [
            ['id', '=', $data['trade_pay_log_id']],
            ['user_id', '=', $this->user_id],
        ];
        
        $trade_pay_log = (new TradePayLogDao())->getTradePayLogInfo($condition);
        
        if (empty($trade_pay_log)) {
            throw new PayException('支付记录不存在');
        }
        
        return $trade_pay_log;
    }
}
