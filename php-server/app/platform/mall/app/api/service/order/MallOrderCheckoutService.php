<?php

namespace app\platform\mall\app\api\service\order;

use app\api\service\order\BaseTblOrderCheckoutService;
use app\platform\mall\app\api\service\cart\MallCartService;

use think\facade\Db;
use app\deshang\exceptions\CommonException;

class MallOrderCheckoutService extends BaseTblOrderCheckoutService
{
    public function __construct()
    {
        parent::__construct('mall');
    }

    /**
     * 检查平台订单结算信息
     * @param array $data 请求数据
     * @return array 订单信息
     */
    public function checkMallOrderCheckout($data)
    {
        // 调用基类的通用方法
        return $this->checkOrderCheckout($data);
    }

    // 生成订单 [ 直接使用获取订单的数据 插入数据]
    public function generateMallOrder($data)
    {
        // 事务处理
        Db::startTrans();
        try {
            // 先获取订单信息（包括更新购物车价格）
            $orderInfo = $this->checkMallOrderCheckout($data);

            // 然后调用基类的通用方法，并传入已获取的订单信息
            $result = $this->generateOrder($data, $orderInfo);

            Db::commit();
            return $result;
        } catch (\Exception $e) {
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }
    }

    /**
     * 平台特定的下单后处理逻辑
     * @param array &$result 订单结果
     * @param array $data 请求数据
     */
    protected function afterCheckOrderCheckout(&$result, $data) {}

    protected function afterGenerateOrder(&$result, $order, $data) {}
}
