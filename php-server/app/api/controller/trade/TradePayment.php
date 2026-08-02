<?php

namespace app\api\controller\trade;

use app\deshang\base\controller\BaseUserController;
use app\deshang\service\trade\DeshangTradePaymentConfigService;

/**
 * @OA\Tag(name="api/trade/TradePayment", description="支付配置接口")
 */
class TradePayment extends BaseUserController
{
    /**
     * 获取商户可用支付列表
     * @OA\Get(
     *     path="/api/trade/payment/list",
     *     tags={"api/trade/TradePayment"},
     *     summary="获取可用支付列表",
     *     description="获取平台可用支付方式列表",
     *     @OA\Parameter(
     *         name="pay_scene",
     *         in="query",
     *         required=false,
     *         description="支付场景 app应用 h5网页 jsapi公众号 native扫码",
     *         @OA\Schema(type="string", enum={"app", "h5", "jsapi", "native"}, example="jsapi")
     *     ),
     *     @OA\Parameter(
     *         name="pay_type",
     *         in="query",
     *         required=false,
     *         description="支付类型 wechat微信 alipay支付宝 balance余额",
     *         @OA\Schema(type="string", enum={"wechat", "alipay", "balance"}, example="wechat")
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
    public function getPaymentList()
    {
        $data = [
            'payment_scene' => input('pay_scene', ''),
            'pay_type' => input('pay_type', ''),
        ];

        $this->validate($data, 'app\api\controller\trade\validate\TradePaymentValidate.paymentList');

        $payment_list = (new DeshangTradePaymentConfigService())->getAvailablePaymentList($data);
        return ds_json_success('操作成功', $payment_list);
    }
}
