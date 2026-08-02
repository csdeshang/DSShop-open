<?php

namespace app\deshang\service\order;

use app\deshang\service\BaseDeshangService;
use app\common\dao\user\UserAddressDao;
use app\common\dao\user\UserDao;
use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderAddressDao;
use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\order\TblOrderLogDao;
use app\common\dao\order\TblOrderDeliveryDao;
use app\common\dao\technician\TechnicianDao;

use app\deshang\exceptions\CommonException;
use app\common\enum\order\TblOrderEnum;
use app\common\enum\order\TblOrderInvoiceEnum;
use app\common\enum\goods\TblGoodsEnum;
use app\common\enum\order\TblOrderDeliveryEnum;
use app\common\enum\technician\TechnicianEnum;

use app\deshang\service\distributor\DeshangDistributorOrderService;
use app\deshang\service\rider\DeshangRiderFeeRuleService;
use app\deshang\service\store\DeshangTblStoreCouponService;
use app\deshang\service\order\DeshangMallExpressService;
use app\deshang\service\system\DeshangPlatformProfileService;

// 下单服务（平台自营单店铺）
class DeshangOrderCheckoutService extends BaseDeshangService
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取订单结算信息（单店铺扁平结构）
     */
    public function checkTblOrderCheckout(array $data, array $cartItems)
    {
        if ($data['address_id'] > 0) {
            $address = (new UserAddressDao())->getAddressInfo([['user_id', '=', $data['user_id']], ['id', '=', $data['address_id']]]);
        } else {
            $address = (new UserAddressDao())->getAddressInfo([['user_id', '=', $data['user_id']], ['is_default', '=', 1]]);
        }

        $user = (new UserDao())->getUserInfoById($data['user_id'], '*');
        unset($user['password']);
        unset($user['pay_password']);

        $platform = $data['platform'] ?? 'mall';
        $goods_list = array_values($cartItems);
        $ship_from = (new DeshangPlatformProfileService())->getShipFromInfo();

        $total_goods_num = 0;
        foreach ($goods_list as $item) {
            $total_goods_num += $item['quantity'];
        }

        // 运费 / 交付信息
        $shipping_amount = 0;
        $order_delivery_info = [];
        switch ($platform) {
            case 'food':
                $order_delivery_info['insert_data'] = (new DeshangRiderFeeRuleService())->calculateRiderFee($ship_from, $address, 0);
                $order_delivery_info['insert_data']['delivery_method'] = TblOrderEnum::DELIVERY_RIDER;
                $order_delivery_info['insert_data']['delivery_status'] = TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_STORE_CONFIRMATION;
                $shipping_amount = $order_delivery_info['insert_data']['rider_total_fee'];
                break;
            case 'mall':
                $expressResult = (new DeshangMallExpressService())->calculateMallExpressFee([], $goods_list, $address);
                $shipping_amount = $expressResult['shipping_amount'];
                $order_delivery_info['mall_express'] = $expressResult;
                break;
            case 'kms':
                $shipping_amount = 0;
                break;
            case 'house':
                $order_delivery_info['insert_data']['delivery_method'] = TblOrderEnum::DELIVERY_TECHNICIAN;
                $technician_id = $data['technician_id'] ?? 0;
                if ($technician_id > 0) {
                    $technician_info = (new TechnicianDao())->getTechnicianInfo([['id', '=', $technician_id]]);
                    if (empty($technician_info)) {
                        throw new CommonException('师傅ID错误');
                    }
                    if ($technician_info['technician_status'] != TechnicianEnum::TECHNICIAN_STATUS_AVAILABLE) {
                        throw new CommonException('师傅不在线');
                    }
                    if ($technician_info['is_enabled'] != 1) {
                        throw new CommonException('师傅已禁用');
                    }
                    if ($technician_info['apply_status'] != TechnicianEnum::APPLY_STATUS_APPROVED) {
                        throw new CommonException('师傅审核未通过');
                    }
                    $order_delivery_info['house']['technician_info'] = $technician_info;
                    $order_delivery_info['insert_data']['technician_id'] = $technician_info['id'];
                    $order_delivery_info['insert_data']['delivery_status'] = TblOrderDeliveryEnum::DELIVERY_STATUS_COURIER_ACCEPTANCE;
                } else {
                    $order_delivery_info['insert_data']['technician_id'] = 0;
                    $order_delivery_info['insert_data']['delivery_status'] = TblOrderDeliveryEnum::DELIVERY_STATUS_PENDING_STORE_CONFIRMATION;
                }
                $shipping_amount = 0;
                break;
            default:
                $shipping_amount = 0;
                break;
        }

        // 商品金额
        $original_amount = 0;
        $goods_amount = 0;
        foreach ($goods_list as $item) {
            $original_amount += $item['goodsSku']['sku_price'] * $item['quantity'];
            $goods_amount += $item['promotion_price'] * $item['quantity'];
        }

        // 可用优惠券
        $available_coupons = (new DeshangTblStoreCouponService())->getAvailableStoreCoupons($data['user_id'], $goods_amount);

        $coupon_discount_amount = 0;
        $coupon_info = null;
        $selected_coupon_id = (int) ($data['coupon_id'] ?? 0);
        if ($selected_coupon_id > 0) {
            foreach ($available_coupons as $coupon) {
                if ($coupon['id'] == $selected_coupon_id) {
                    switch ($coupon['storeCoupon']['coupon_type']) {
                        case 1: // 满减券
                            $coupon_discount_amount = min($coupon['storeCoupon']['discount_value'], $goods_amount);
                            break;
                        case 2: // 折扣券
                            $discount_rate = $coupon['storeCoupon']['discount_value'] / 10;
                            $coupon_discount_amount = ds_commerce_money($goods_amount * (1 - $discount_rate), 2);
                            break;
                        case 3: // 直减券
                            $coupon_discount_amount = min($coupon['storeCoupon']['discount_value'], $goods_amount);
                            break;
                    }
                    $coupon_info = $coupon;
                    break;
                }
            }
        }

        $goods_list = $this->calculateCouponDiscount($goods_list, $coupon_discount_amount);
        foreach ($goods_list as $key => $item) {
            $goods_list[$key]['discount_price'] = $item['promotion_price'] - $item['pay_price'];
        }

        $discount_amount = $coupon_discount_amount;
        $order_amount = $goods_amount + $shipping_amount - $discount_amount;
        $pay_amount = $order_amount;

        $original_amount = ds_commerce_money($original_amount, 2);
        $goods_amount = ds_commerce_money($goods_amount, 2);
        $shipping_amount = ds_commerce_money($shipping_amount, 2);
        $discount_amount = ds_commerce_money($discount_amount, 2);
        $order_amount = ds_commerce_money($order_amount, 2);
        $pay_amount = ds_commerce_money($pay_amount, 2);

        return [
            'platform' => $platform,
            'goods_list' => $goods_list,
            'address' => $address,
            'user' => $user,
            'shipping_amount' => $shipping_amount,
            'discount_amount' => $discount_amount,
            'original_amount' => $original_amount,
            'goods_amount' => $goods_amount,
            'order_amount' => $order_amount,
            'pay_amount' => $pay_amount,
            'coupon_info' => $coupon_info,
            'available_coupons' => $available_coupons,
            'order_delivery_info' => $order_delivery_info,
            'total_goods_num' => $total_goods_num,
            'total_original_amount' => $original_amount,
            'total_goods_amount' => $goods_amount,
            'total_shipping_amount' => $shipping_amount,
            'total_discount_amount' => $discount_amount,
            'total_order_amount' => $order_amount,
            'total_pay_amount' => $pay_amount,
        ];
    }

    /**
     * 生成订单（单店铺）
     */
    public function generateTblOrder($order, $data)
    {
        $referrer_id = $data['referrer_id'] ?? 0;
        $referrer_info = [];
        if ($referrer_id > 0) {
            $referrer_info = (new UserDao())->getUserInfoById($referrer_id);
        }

        $address = $order['address'];
        $user = $order['user'];
        $platform = $order['platform'] ?? ($data['platform'] ?? 'mall');
        $total_pay_amount = $order['total_pay_amount'];
        $ship_from = (new DeshangPlatformProfileService())->getShipFromInfo();

        $order_data = [
            'platform' => $platform,
            'order_merge_id' => 0,
            'pay_channel' => '',
            'pay_scene' => '',
            'order_sn' => generateOrderNo($platform, $user['id']),
            'order_status' => TblOrderEnum::ORDER_STATUS_PENDING,
            'order_referrer_id' => $referrer_info['id'] ?? 0,
            'out_trade_no' => null,
            'trade_no' => null,
            'user_id' => $user['id'],
            'delivery_method' => $order['order_delivery_info']['insert_data']['delivery_method'] ?? '',
            'original_amount' => $order['original_amount'],
            'goods_amount' => $order['goods_amount'],
            'shipping_amount' => $order['shipping_amount'],
            'discount_amount' => $order['discount_amount'],
            'order_amount' => $order['order_amount'],
            'pay_amount' => $order['pay_amount'],
            'service_amount' => 0,
            'invoice_status' => TblOrderInvoiceEnum::STATUS_NOT_SUBMITTED,
            'refund_status' => 0,
            'refund_amount' => 0.00,
            'user_remark' => is_array($data['user_remark'] ?? '') ? '' : (string) ($data['user_remark'] ?? ''),
            'store_remark' => '',
            'add_time' => TIMESTAMP,
            'payment_time' => 0,
            'delivery_time' => 0,
            'shipping_time' => 0,
            'finnshed_time' => 0,
            'close_time' => 0,
        ];

        $order_id = (new TblOrderDao())->createOrder($order_data);

        if (!empty($order['order_delivery_info']['insert_data']) && ($order['order_delivery_info']['insert_data']['delivery_method'] ?? '') == TblOrderEnum::DELIVERY_RIDER) {
            $order['order_delivery_info']['insert_data']['order_id'] = $order_id;
            (new TblOrderDeliveryDao())->createOrderDelivery($order['order_delivery_info']['insert_data']);
        }

        if (!empty($order['order_delivery_info']['insert_data']) && ($order['order_delivery_info']['insert_data']['delivery_method'] ?? '') == TblOrderEnum::DELIVERY_TECHNICIAN) {
            $order['order_delivery_info']['insert_data']['order_id'] = $order_id;
            $order['order_delivery_info']['insert_data']['technician_appt_time'] = $data['appointment_time'] ?? 0;
            (new TblOrderDeliveryDao())->createOrderDelivery($order['order_delivery_info']['insert_data']);
        }

        $address_data = [
            'order_id' => $order_id,
            'reciver_name' => $address['real_name'] ?? '',
            'reciver_mobile' => $address['mob_phone'] ?? '',
            'reciver_address' => ($address['area_info'] ?? '') . ($address['address_detail'] ?? ''),
            'reciver_longitude' => $address['longitude'] ?? '',
            'reciver_latitude' => $address['latitude'] ?? '',
            'shipper_name' => $ship_from['store_name'] ?? '平台商城',
            'shipper_mobile' => $ship_from['contact_phone'] ?? '',
            'shipper_address' => $ship_from['address'] ?? '',
            'shipper_longitude' => $ship_from['store_longitude'] ?? '',
            'shipper_latitude' => $ship_from['store_latitude'] ?? '',
        ];
        (new TblOrderAddressDao())->createOrderAddress($address_data);

        $order_goods_data = [];
        foreach ($order['goods_list'] as $item) {
            if ($item['goods']['goods_status'] != TblGoodsEnum::STATUS_SHELVED) {
                throw new CommonException($item['goods']['goods_name'] . '商品状态为下架');
            }
            if ($item['goods']['sys_status'] != TblGoodsEnum::SYS_STATUS_NORMAL) {
                throw new CommonException($item['goods']['goods_name'] . '商品状态为系统审核未通过');
            }
            if ($item['goodsSku']['sku_stock'] < $item['quantity']) {
                throw new CommonException($item['goods']['goods_name'] . '商品库存不足');
            }

            $order_goods_data[] = [
                'order_id' => $order_id,
                'goods_id' => $item['goods_id'],
                'goods_name' => $item['goods']['goods_name'],
                'sku_id' => $item['goodsSku']['id'],
                'sku_name' => $item['goodsSku']['sku_name'] ?? '默认规格',
                'promotion_platform' => $item['promotion_platform'],
                'promotion_type' => $item['promotion_type'],
                'promotion_related_id' => $item['promotion_related_id'],
                'promotion_price' => $item['promotion_price'],
                'sku_price' => $item['goodsSku']['sku_price'],
                'pay_price' => $item['pay_price'],
                'goods_num' => $item['quantity'],
                'goods_image' => empty($item['goodsSku']['sku_image']) ? $item['goods']['cover_image'] : $item['goodsSku']['sku_image'],
                'user_id' => $user['id'],
                'commis_rate' => '',
            ];
        }

        (new TblOrderGoodsDao())->createOrderGoodsAll($order_goods_data);

        $order_goods_list = (new TblOrderGoodsDao())->getOrderGoodsList([['order_id', '=', $order_id]]);
        foreach ($order_goods_list as $order_goods) {
            (new DeshangDistributorOrderService())->generateDistributorOrder($order_data, $order_goods, $user, $referrer_info);
        }

        if (!empty($order['coupon_info'])) {
            (new DeshangTblStoreCouponService())->isUsedStoreCoupon($order['coupon_info']);
        }

        (new TblOrderLogDao())->createOrderLog([
            'order_id' => $order_id,
            'order_status' => TblOrderEnum::ORDER_STATUS_PENDING,
            'message' => '下单',
            'create_role' => 'user',
            'create_by' => $user['username'],
        ]);

        event('OrderGenerateListener', [
            'order_goods_list' => $order_goods_list,
            'store_info' => $ship_from,
            'user_info' => $user,
        ]);

        return [
            'source_type' => 'order',
            'source_id' => $order_id,
            'pay_amount' => $total_pay_amount,
            'pay_info' => '单个订单支付',
        ];
    }

    /**
     * 按金额比例分配优惠券优惠到商品 pay_price
     */
    private function calculateCouponDiscount($goods_list, $discount_amount)
    {
        if ($discount_amount <= 0) {
            foreach ($goods_list as $key => $item) {
                $goods_list[$key]['pay_price'] = $item['promotion_price'];
            }
            return $goods_list;
        }

        $goods_amount = 0;
        foreach ($goods_list as $item) {
            $goods_amount += $item['promotion_price'] * $item['quantity'];
        }

        $applied_discount = 0;
        $goods_count = count($goods_list);

        foreach ($goods_list as $key => $item) {
            $item_total = $item['promotion_price'] * $item['quantity'];

            if ($key == $goods_count - 1) {
                $item_discount = $discount_amount - $applied_discount;
            } else {
                $item_discount = ds_commerce_money(($item_total / $goods_amount) * $discount_amount, 2);
                $applied_discount += $item_discount;
            }

            $item_discount_per_unit = ds_commerce_money($item_discount / $item['quantity'], 2);
            $goods_list[$key]['pay_price'] = ds_commerce_money($item['promotion_price'] - $item_discount_per_unit, 2);
        }

        return $goods_list;
    }
}
