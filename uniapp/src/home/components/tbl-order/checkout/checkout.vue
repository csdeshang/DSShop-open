<template>
    <view class="">
        <Address :address="orderData.address" @confirm="handleAddressConfirm" />
        <view class="ds-rounded mb-20 p-20">

            <GoodsList :items="orderData.goods_list" :platform="props.platform"
                @updateCart="loadOrderCheckoutData(checkParams)" />


            <view class="ds-field-line">
                <view class="field-name">
                    <text>订单备注</text>
                </view>
                <view class="field-input">
                    <input class="flex-1" v-model="user_remark" placeholder="请输入订单备注" />
                </view>
            </view>

            <!-- House 类下单  -->
            <view class="ds-field-line"
                v-if="props.platform == 'house' && orderData.order_delivery_info && orderData.order_delivery_info.house && orderData.order_delivery_info.house.technician_info">
                <view class="field-name">
                    <text>服务师傅</text>
                </view>
                <view class="field-info">
                    <text>{{ orderData.order_delivery_info.house.technician_info.name }}</text>
                </view>
            </view>

            <view class="ds-field-line">
                <view class="field-name">
                    <text>运费</text>
                </view>
                <view class="field-info">
                    <text>¥{{ orderData.shipping_amount }}</text>
                </view>
            </view>

            <view class="ds-field-line">
                <view class="field-name">
                    <text>优惠</text>
                </view>
                <view class="field-info">
                    <text>¥{{ orderData.discount_amount }}</text>
                </view>
            </view>

            <!-- 优惠券选择组件 -->
            <StoreCouponList v-if="orderData.available_coupons && orderData.available_coupons.length > 0"
                v-model="checkParams.coupon_id"
                :store_coupons_list="orderData.available_coupons"
                @update:modelValue="loadOrderCheckoutData(checkParams)" />


            <view class="store-total">
                <text>共{{ orderData.goods_list.length }}件 小计: ¥{{ orderData.order_amount }}</text>
            </view>


        </view>

        <TechnicianApptTime v-if="props.platform == 'house'" v-model="checkParams.appointment_time"
            :unavailableTimes="unavailableTimes" />



        <!--结算底部占位-->
        <view class="order-footer-placeholder"></view>

        <view class="order-footer">
            <view class="total-price">
                总价:<text> ¥{{ totalPayPrice }}</text>
            </view>
            <button @click="payOrder" class="confirm-order-btn">立即结算</button>
        </view>



    </view>
</template>

<script lang="ts" setup>
import { useWechatSubscribe } from '@/hooks/useWechatSubscribe'
const { handleSubscribeOrder } = useWechatSubscribe()
import { checkTblOrderCheckout, generateTblOrder } from '@/home/api/tbl-order/tblOrderCheckout'; // 引入获取购物车数据的API
import { goToPage } from '@/utils/util';

import Address from '@/home/components/tbl-order/checkout/component/address.vue';
import GoodsList from '@/home/components/tbl-order/checkout/component/goods-list.vue';
import StoreCouponList from '@/home/components/tbl-order/checkout/component/store-coupon-list.vue';
import TechnicianApptTime from '@/home/components/tbl-order/checkout/component/technician-appt-time.vue';

const unavailableTimes = ref({
    '2025-05-30 09:00': false,
    '2025-05-30 10:00': false
});


import { computed, reactive, ref } from "vue";
import { onLoad } from '@dcloudio/uni-app';

const props = defineProps({
    platform: {
        type: String,
        default: ''
    }
});



const loading = ref(true); // 加载状态

// 基础参数， 用于获取订单的数据， 提交时和其他参数合并
const checkParams = ref({
    cart_ids: '',
    address_id: '',
    referrer_id: uni.getStorageSync("referrer_id"),
    coupon_id: '' as string | number,
    appointment_time: '',
    technician_id: ''
});


const user_remark = ref('');

const orderData = reactive({
    goods_list: [] as any[],
    address: null as any,
    shipping_amount: 0,
    discount_amount: 0,
    order_amount: 0,
    available_coupons: [] as any[],
    order_delivery_info: null as any,
    total_pay_amount: 0,
    total_original_amount: 0,
});
const loadOrderCheckoutData = async (params: any) => {
    loading.value = true;
    try {
        const res = await checkTblOrderCheckout(params, props.platform);
        Object.assign(orderData, res.data);
        if (res.data.address) {
            checkParams.value.address_id = res.data.address.id;
        }
    } catch (error) {
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

// 计算总价
const totalPayPrice = computed(() => {
    return Number(orderData.total_pay_amount || 0).toFixed(2);
});

// 确认选择收货地址
const handleAddressConfirm = (address: any) => {
    checkParams.value.address_id = address.id;
    loadOrderCheckoutData(checkParams.value); // 加载购物车数据
};


/**
 * 表单验证
 */
const validateOrderForm = (): boolean => {
    // 检查是否选择了地址
    if (!checkParams.value.address_id) {
        uni.showToast({
            title: '请先选择收货地址',
            icon: 'none'
        })
        return false
    }

    // 检查是否选择了预约时间
    if (props.platform === 'house' && !checkParams.value.appointment_time) {
        uni.showToast({
            title: '请先选择预约时间',
            icon: 'none'
        })
        return false
    }

    return true
}

const payOrder = async () => {

    // 表单验证
    if (!validateOrderForm()) {
        return
    }

    // 先申请订阅权限
    await handleSubscribeOrder(props.platform)


    const orderDetails = {
        ...checkParams.value,
        user_remark: user_remark.value
    };


    try {
        // 调用生成订单接口
        const res = await generateTblOrder(orderDetails, props.platform);

        if (res.code === 10000) {
            // 申请支付成功 跳转支付
            let params = {
                source_type: res.data.source_type,
                source_id: res.data.source_id,
                pay_amount: res.data.pay_amount,
                pay_info: res.data.pay_info,
                uni_redirect_url: '/home/pages/user/order/index'
            }
            
            goToPage({
                url: '/home/pages/user/pay/index',
                params: params,
                type: 'redirectTo'
            });


        }

        // 这里可以添加下单成功后的逻辑
    } catch (error) {
        uni.showToast({
            title: '下单失败',
            icon: 'none'
        });
    }
};






onLoad((options) => {
    // 购物车ids
    checkParams.value.cart_ids = options?.cart_ids ? String(options.cart_ids) : '';
    // 师傅ID
    checkParams.value.technician_id = options?.technician_id ? String(options.technician_id) : '';
    loadOrderCheckoutData(checkParams.value); // 加载购物车数据
});
</script>

<style lang="scss" scoped>
.store-total {
    line-height: 120rpx;
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
}


.order-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx 40rpx;
    background: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.1);
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));

    .total-price {
        flex: 1;
        font-size: 28rpx;
        font-weight: bold;
        color: #333;
        margin-right: 20rpx;

        span {
            font-size: 28rpx;
            font-weight: bold;
            color: #333;
        }
    }

    .confirm-order-btn {
        background-color: #FF6034;
        color: #fff;
        border: none;
        padding: 2rpx 40rpx;
        border-radius: 40rpx;
        font-size: 28rpx;
        font-weight: bold;
        transition: background 0.3s;

        &:hover {
            background-color: #FF8C19;
        }
    }
}

.order-footer-placeholder {
    height: 200rpx;
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
