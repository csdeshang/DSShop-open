<?php

namespace app\api\controller\trade\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\trade\TradePayEnum;
use app\common\enum\trade\TradePaymentConfigEnum;

/**
 * 交易支付验证器
 */
class TradePayValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'source_type' => 'require|checkSourceType',
        'source_id' => 'integer',
        'pay_channel' => 'require|checkPayChannel',
        'pay_scene' => 'require|checkPayScene',
        'pay_amount' => 'require|float|gt:0|max:999999.99',
        'trade_pay_log_id' => 'require|integer|gt:0',
    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'source_type.require' => '来源类型不能为空',
        'source_type.checkSourceType' => '来源类型值无效',
        'source_id.integer' => '来源ID必须为整数',
        'pay_channel.require' => '支付渠道不能为空',
        'pay_channel.checkPayChannel' => '支付渠道值无效',
        'pay_scene.require' => '支付场景不能为空',
        'pay_scene.checkPayScene' => '支付场景值无效',
        'pay_amount.require' => '支付金额不能为空',
        'pay_amount.float' => '支付金额必须为数字',
        'pay_amount.gt' => '支付金额必须大于0',
        'pay_amount.max' => '支付金额不能超过999999.99',
        'trade_pay_log_id.require' => '支付记录ID不能为空',
        'trade_pay_log_id.integer' => '支付记录ID必须为整数',
        'trade_pay_log_id.gt' => '支付记录ID必须大于0',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'pay' => ['source_type', 'source_id', 'pay_channel', 'pay_scene', 'pay_amount'],
        'getPayStatus' => ['trade_pay_log_id'],
    ];

    
    public function checkSourceType($value, $rule, $data)
    {
        return array_key_exists($value, TradePayEnum::getSourceTypeDict());
    }

    public function checkPayChannel($value, $rule, $data){
        return array_key_exists($value, TradePaymentConfigEnum::getPaymentChannelDict());
    }

    public function checkPayScene($value, $rule, $data){
        return array_key_exists($value, TradePaymentConfigEnum::getPaymentSceneDict());
    }

} 