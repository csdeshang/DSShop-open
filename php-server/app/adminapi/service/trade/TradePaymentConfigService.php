<?php

namespace app\adminapi\service\trade;

use app\deshang\base\service\BaseAdminService;
use app\deshang\service\trade\DeshangTradePaymentConfigService;

class TradePaymentConfigService extends BaseAdminService
{
    /**
     * 获取平台支付配置列表
     */
    public function getPaymentConfig(): array
    {
        return (new DeshangTradePaymentConfigService())->getPaymentConfig();
    }

    /**
     * 获取单条配置
     */
    public function getPaymentConfigInfoById($id)
    {
        return (new DeshangTradePaymentConfigService())->getPaymentConfigInfoById($id);
    }

    /**
     * 创建平台支付配置
     */
    public function createPaymentConfig($data)
    {
        return (new DeshangTradePaymentConfigService())->createPaymentConfig($data);
    }

    /**
     * 更新平台支付配置
     */
    public function updatePaymentConfig($data)
    {
        return (new DeshangTradePaymentConfigService())->updatePaymentConfig($data);
    }

    /**
     * 删除平台支付配置
     */
    public function deletePaymentConfig($id)
    {
        return (new DeshangTradePaymentConfigService())->deletePaymentConfig($id);
    }
}
