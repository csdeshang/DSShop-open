<?php

namespace app\api\service\order;

use think\facade\Db;
use app\deshang\base\service\BaseUserService;
use app\deshang\service\order\DeshangOrderCheckoutService;
use app\common\dao\cart\TblCartDao;
use app\deshang\exceptions\CommonException;
use app\deshang\service\cart\DeshangTblCartService;


/**
 * 平台通用订单结算服务基类
 */
abstract class BaseTblOrderCheckoutService extends BaseUserService
{
    protected $platform;
    public function __construct($platform)
    {
        parent::__construct();
        $this->platform = $platform;
    }


    /**
     * 检查订单结算信息
     * @param array $data 请求数据
     * @return array 订单信息
     */
    public function checkOrderCheckout(array $data): array
    {
        // 设置平台和用户信息
        $data['user_id'] = $this->user_id;
        $data['platform'] = $this->platform;

        // 选中的购物车id
        $cart_ids = $data['cart_ids'];

        // 获取购物车商品列表【最新促销活动价格】
        $cart_list = (new DeshangTblCartService())->getTblCartList($data['user_id'], $data['platform'], $cart_ids);

        // 调用核心获取下单信息
        $result = (new DeshangOrderCheckoutService())->checkTblOrderCheckout($data, $cart_list);

        // 调用钩子方法，处理平台特定的逻辑
        $this->afterCheckOrderCheckout($result, $data);

        return $result;
    }

    /**
     * 生成订单
     * @param array $data 请求数据
     * @param array $orderInfo 订单信息，如果为null则会调用checkOrderCheckout获取
     * @return array 订单结果
     */
    public function generateOrder(array $data, $orderInfo = null)
    {
        if (empty($orderInfo)) {
            throw new CommonException('订单信息不能为空');
        }

        // 调用核心生成订单
        $result = (new DeshangOrderCheckoutService())->generateTblOrder($orderInfo, $data);

        // 删除购物车商品
        (new TblCartDao())->deleteCart([
            ['id', 'in', $data['cart_ids']],
            ['user_id', '=', $this->user_id]
        ]);

        // 调用钩子方法，处理平台特定的逻辑
        $this->afterGenerateOrder($result, $orderInfo, $data);

        return $result;
    }


    /**
     * 获取下单信息后的钩子方法，供子类重写
     * @param array &$result 订单结果
     * @param array $data 请求数据
     */
    protected function afterCheckOrderCheckout(&$result, $data) {}

    /**
     * 生成订单后的钩子方法，供子类重写
     * @param array &$result 订单结果
     * @param array $order 订单信息
     * @param array $data 请求数据
     */
    protected function afterGenerateOrder(&$result, $order, $data) {}
}
