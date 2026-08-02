<?php

namespace app\api\controller\trade;

use app\deshang\base\controller\BaseUserController;
use app\api\service\trade\TradePayService;

use think\ValidateException;

/**
 * @OA\Tag(name="api/trade/TradePay", description="支付接口")
 */
class TradePay extends BaseUserController
{

    /**
     * 发起支付
     * @OA\Post(
     *     path="/api/trade/pay",
     *     tags={"api/trade/TradePay"},
     *     summary="发起支付",
     *     description="发起充值或订单支付",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"source_type", "source_id", "pay_channel", "pay_scene", "pay_amount"},
     *             @OA\Property(property="source_type", type="string", description="来源类型 recharge充值 order订单", example="order"),
     *             @OA\Property(property="source_id", type="integer", description="来源ID", example=1),
     *             @OA\Property(property="pay_channel", type="string", description="支付渠道 wechat微信 alipay支付宝", example="wechat"),
     *             @OA\Property(property="pay_scene", type="string", description="支付场景 app应用 h5网页 jsapi公众号", example="jsapi"),
     *             @OA\Property(property="pay_amount", type="number", format="float", description="支付金额", example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function pay()
    {
        $data = [
            // 来源类型 充值 订单支付
            'source_type' => input('source_type', ''),
            // 来源id
            'source_id' => input('source_id', 0),
            // 支付渠道
            'pay_channel' => input('pay_channel', ''),
            // 支付场景
            'pay_scene' => input('pay_scene', ''),
            // 支付金额
            'pay_amount' => round(input('pay_amount', 0), 2),
        ];

        $this->validate($data, 'app\api\controller\trade\validate\TradePayValidate.pay');

        $result = (new TradePayService())->pay($data);

        return ds_json_success('操作成功', $result);
    }

    /**
     * 查询支付状态
     */
    public function getPayStatus()
    {
        $data = [
            'trade_pay_log_id' => input('trade_pay_log_id', 0),
        ];

        $this->validate($data, 'app\api\controller\trade\validate\TradePayValidate.getPayStatus');
        
        $result = (new TradePayService())->getPayStatus($data);
        
        return ds_json_success('操作成功', $result);
    }




}
