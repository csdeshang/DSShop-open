<?php

namespace app\api\service\user;

use think\facade\Db;
use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;
use app\common\dao\pointsGoods\PointsGoodsDao;
use app\common\dao\pointsGoods\PointsGoodsOrderDao;
use app\common\dao\pointsGoods\PointsGoodsOrderLogDao;
use app\common\dao\user\UserDao;
use app\common\dao\user\UserAddressDao;
use app\common\enum\pointsGoods\PointsGoodsOrderEnum;
use app\common\enum\user\UserPointsEnum;
use app\deshang\service\user\DeshangUserPointsService;

/**
 * 积分商品订单结算服务
 */
class UserPointsGoodsOrderCheckoutService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 检查积分商品下单信息
     * @param array $data 请求数据
     * @return array 订单信息
     */
    public function checkPointsGoodsOrderCheckout(array $data): array
    {
        // 验证参数
        if (empty($data['goods_id'])) {
            throw new CommonException('商品ID不能为空');
        }

        if ($data['quantity'] <= 0) {
            throw new CommonException('兑换数量必须大于0');
        }

        // 获取商品信息
        $goodsInfo = (new PointsGoodsDao())->getPointsGoodsInfoById($data['goods_id']);
        if (empty($goodsInfo)) {
            throw new NotFoundException('商品不存在');
        }

        // 检查商品状态
        if ($goodsInfo['goods_status'] != 1) {
            throw new CommonException('商品已下架');
        }

        // 检查库存
        if ($goodsInfo['stock_num'] < $data['quantity']) {
            throw new CommonException('库存不足');
        }

        // 每人限购   `limit_per_user` int(11) NOT NULL DEFAULT '0' COMMENT '每人限购数量 0为不限制',
        if ($goodsInfo['limit_per_user'] > 0) {
            $condition = [
                ['user_id', '=', $this->user_id],
                ['goods_id', '=', $goodsInfo['id']],
                ['order_status', 'not in', [PointsGoodsOrderEnum::ORDER_STATUS_CANCELLED]] // 排除已取消的订单
            ];
            $exchangeNums = (new PointsGoodsOrderDao())->getPointsGoodsOrderColumn($condition, 'exchange_num');
            $userExchangeCount = array_sum($exchangeNums);
            if ($userExchangeCount + $data['quantity'] > $goodsInfo['limit_per_user']) {
                throw new CommonException('超出每人限购数量');
            }
        }

        // 每日限购  `limit_per_day` int(11) NOT NULL DEFAULT '0' COMMENT '每日限购数量 0为不限制',
        if ($goodsInfo['limit_per_day'] > 0) {
            $todayStart = strtotime(date('Y-m-d 00:00:00'));
            $todayEnd = strtotime(date('Y-m-d 23:59:59'));
            $condition = [
                ['user_id', '=', $this->user_id],
                ['goods_id', '=', $goodsInfo['id']],
                ['order_status', 'not in', [PointsGoodsOrderEnum::ORDER_STATUS_CANCELLED]], // 排除已取消的订单
                ['create_at', 'between', [$todayStart, $todayEnd]]
            ];
            $exchangeNums = (new PointsGoodsOrderDao())->getPointsGoodsOrderColumn($condition, 'exchange_num');
            $todayExchangeCount = array_sum($exchangeNums);
            if ($todayExchangeCount + $data['quantity'] > $goodsInfo['limit_per_day']) {
                throw new CommonException('超出每日限购数量');
            }
        }

        // 获取用户信息
        $user = (new UserDao())->getUserInfoById($this->user_id, '*');
        unset($user['password']);
        unset($user['pay_password']);



        // 获取收货地址
        $addressInfo = null;
        if (!empty($data['address_id']) && $data['address_id'] > 0) {
            // 获取用户指定地址
            $addressInfo = (new UserAddressDao())->getAddressInfo([['user_id', '=', $this->user_id], ['id', '=', $data['address_id']]]);
        } else {
            // 获取用户默认地址
            $addressInfo = (new UserAddressDao())->getAddressInfo([['user_id', '=', $this->user_id], ['is_default', '=', 1]]);
        }

        // 计算订单信息
        $orderInfo = [
            'goods_info' => $goodsInfo,
            'quantity' => $data['quantity'],
            'total_points' => $goodsInfo['points_price'] * $data['quantity'],
            'total_market_price' => $goodsInfo['market_price'] * $data['quantity'],
            'address_info' => $addressInfo,
            'user' => $user,
        ];

        return $orderInfo;
    }

    /**
     * 生成积分商品订单
     * @param array $data 请求数据
     * @return array 订单结果
     */
    public function generatePointsGoodsOrder(array $data): array
    {
        // 先检查下单信息
        $orderInfo = $this->checkPointsGoodsOrderCheckout($data);

        // 检查用户积分
        if ($orderInfo['user']['points'] < $orderInfo['total_points']) {
            throw new CommonException('积分不足，需要' . $orderInfo['total_points'] . '积分');
        }




        Db::startTrans();
        try {
            // 生成订单号
            $orderSn = generateOrderNo('PGO', $this->user_id);


            // 创建订单记录
            $orderData = [
                'order_sn' => $orderSn,
                'user_id' => $this->user_id,
                'goods_id' => $orderInfo['goods_info']['id'],
                'goods_name' => $orderInfo['goods_info']['goods_name'],
                'goods_image' => $orderInfo['goods_info']['goods_image'],
                'points_price' => $orderInfo['goods_info']['points_price'],
                'exchange_num' => $orderInfo['quantity'],
                'total_points' => $orderInfo['total_points'],
                'order_status' => PointsGoodsOrderEnum::ORDER_STATUS_PENDING, // 待发货
                'receiver_name' => $orderInfo['address_info']['real_name'] ?? '',
                'receiver_mobile' => $orderInfo['address_info']['mob_phone'] ?? '',
                'receiver_address' => ($orderInfo['address_info']['area_info'] ?? '') . ($orderInfo['address_info']['address_detail'] ?? ''),
                'remark' => $data['user_remark'] ?? '',
            ];

            // 插入订单记录到数据库
            $orderId = (new PointsGoodsOrderDao())->createPointsGoodsOrder($orderData);

            // 创建订单日志
            $logData = [
                'order_id' => $orderId,
                'order_status' => PointsGoodsOrderEnum::ORDER_STATUS_PENDING,
                'message' => '用户创建积分商品订单',
                'create_role' => 'user',
                'create_by' => (string)$this->user_id,
                'create_at' => time(),
                'extra' => json_encode(['order_sn' => $orderSn])
            ];
            (new PointsGoodsOrderLogDao())->createPointsGoodsOrderLog($logData);

            // 扣除用户积分
            $points_data = array(
                'user_id' => $this->user_id,
                'related_id' => $orderId,
                'change_type' => UserPointsEnum::TYPE_POINTS_GOODS_ORDER,
                'change_mode' => UserPointsEnum::MODE_DECREASE,
                'change_num' => $orderInfo['total_points'],
                'change_desc' => '积分商品兑换',
            );
            (new DeshangUserPointsService())->modifyUserPoints($points_data);


            // 减少商品库存
            $points_goods_data = array(
                'id' => $orderInfo['goods_info']['id'],
            );
            (new PointsGoodsDao())->setPointsGoodsDec($points_goods_data, 'stock_num', $orderInfo['quantity']);

            // 增加兑换数量
            $points_goods_data = array(
                'id' => $orderInfo['goods_info']['id'],
            );
            (new PointsGoodsDao())->setPointsGoodsInc($points_goods_data, 'exchange_num', $orderInfo['quantity']);


            Db::commit();

            return [
                'order_id' => $orderId,
                'order_sn' => $orderSn,
                'order_data' => $orderData,
                'message' => '兑换成功'
            ];
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException('兑换失败：' . $e->getMessage());
        }
    }
}
