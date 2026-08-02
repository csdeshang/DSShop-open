<?php

namespace app\api\controller\trade\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\trade\TradePaymentConfigEnum;

/**
 * 交易支付配置验证器
 */
class TradePaymentValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'payment_scene' => 'checkPaymentScene',

    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'payment_scene.in' => '支付场景值无效',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'paymentList' => ['payment_scene'],
    ];

    public function checkPaymentScene($value, $rule, $data){
        return array_key_exists($value, TradePaymentConfigEnum::getPaymentSceneDict());
    }


} 