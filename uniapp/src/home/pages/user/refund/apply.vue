<template>
    <view>
        <!-- 商品信息 -->
        <view v-for="(goods, index) in orderDetail.orderGoodsList" :key="goods.id">
            <view class="goods-card" v-if="formData.order_goods_id === 0 || formData.order_goods_id === goods.id">
                <image class="goods-image" :src="formatImageUrl(goods.goods_image, ThumbnailPresets.small, 'goods')" mode="aspectFill"></image>
                <view class="goods-info">
                    <text class="goods-name">{{ goods.goods_name }}</text>
                    <text class="goods-spec">{{ goods.sku_name }}</text>
                    <text class="goods-price">¥{{ goods.pay_price }}</text>
                </view>
            </view>
        </view>

        <!-- 退款表单 -->
        <view class="ds-card">
            <view class="ds-field-line" v-if="formData.order_goods_id === 0">
                <text class="field-name">退款商品</text>
                <text class="field-value">全部商品</text>
            </view>
            <!-- 退款类型 -->
            <view class="ds-field-line">
                <text class="field-name">退款类型</text>
                <picker class="field-input" mode="selector" :range="refundTypes" range-key="label"
                    @change="onRefundTypeChange" :value="refundTypeIndex">
                    <text>{{ refundTypes[refundTypeIndex].label }}</text>
                </picker>
                <uni-icons type="right" size="13"></uni-icons>
            </view>

            <!-- 退款金额 -->
            <view class="ds-field-line">
                <text class="field-name">退款金额</text>
                <input class="field-input" type="digit" v-model="formData.apply_amount" placeholder="请输入退款金额"
                    @input="onApplyAmountInput" />
                <text class="field-unit">元</text>
            </view>

            <!-- 退款原因 -->
            <view class="ds-field-line">
                <text class="field-name">退款原因</text>
                <textarea class="field-textarea" v-model="formData.refund_explain" placeholder="请输入退款原因" />
            </view>

            <!-- 图片上传 -->
            <view class="upload-section">
                <text class="field-name">上传凭证</text>
                <DsUploadImage v-model="formData.refund_images" :maxCount="3" :maxSize="2" buttonText="上传图片" />
                <text class="upload-tips">最多上传3张图片</text>
            </view>


        </view>

        <!-- 提交按钮 -->
         <view class="ml-20 mr-20">
            <button class="ds-btn-lg" type="submit" @click="submitRefund">提交申请</button>
         </view>
        

    </view>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

import { onLoad } from '@dcloudio/uni-app'
import { applyRefund } from '@/home/api/tbl-order/tblOrderReturn'
import {
    getTblOrderInfo,
} from '@/home/api/tbl-order/tblOrder';
import * as DsUploadImageModule from '@/components/ds-attachment/upload-image.vue'
const DsUploadImage = DsUploadImageModule.default || DsUploadImageModule


// 订单详情
interface OrderGoods {
    id: number;
    pay_price: number;
    goods_name: string;
    goods_image: string;
    sku_name: string;
}

interface OrderDetail {
    id: number;
    order_sn: string;
    order_status: number;
    order_status_desc: string;
    delivery_method_desc: string;
    order_amount: string;
    goods_amount: string;
    shipping_amount: string;
    discount_amount: string;
    pay_amount: string;
    pay_channel: string;
    pay_channel_desc: string;
    add_time: number;
    payment_time: number;
    delivery_time: number;
    shipping_time: number;
    finnshed_time: number;
    user_remark: string;
    orderGoodsList: OrderGoods[];
    orderAddress: Record<string, any>;
    orderLogList: any[];
    user_available_actions: string[];
}

const orderDetail = reactive<OrderDetail>({
    id: 0,
    order_sn: '',
    order_status: 0,
    order_status_desc: '',
    delivery_method_desc: '',
    order_amount: '0.00',
    goods_amount: '0.00',
    shipping_amount: '0.00',
    discount_amount: '0.00',
    pay_amount: '0.00',
    pay_channel: '',
    pay_channel_desc: '',
    add_time: 0,
    payment_time: 0,
    delivery_time: 0,
    shipping_time: 0,
    finnshed_time: 0,
    user_remark: '',
    orderGoodsList: [],
    orderAddress: {},
    orderLogList: [],
    user_available_actions: []
});

// 表单数据
const formData = reactive({
    order_id: 0,
    order_goods_id: 0,
    refund_type: 1 as 1 | 2,
    refund_method: 1 as 1 | 2 | 3,
    apply_amount: 0,
    refund_explain: '',
    refund_images: [] as string[],
})



// 退款类型选项
const refundTypes = ref([
    { label: '仅退款', value: 1 },
    { label: '退货退款', value: 2 }
])
const refundTypeIndex = ref(0)


// 退款类型变更
const onRefundTypeChange = (e: any) => {
    refundTypeIndex.value = e.detail.value
    formData.refund_type = refundTypes.value[refundTypeIndex.value].value as 1 | 2
}

// max退款金额
const maxApplyAmount = computed(() => {
    if (formData.order_goods_id === 0) {
        return Number(orderDetail.order_amount) || 0;
    } else {
        const goods = orderDetail.orderGoodsList.find(item => item.id === formData.order_goods_id);
        return goods ? Number(goods.pay_price) || 0 : 0;
    }
})

// 退款金额输入
import { debounce } from '@/utils/util';
const onApplyAmountInput = debounce((event: any) => {
    const newValue = event.detail.value?.trim();

    if (newValue > maxApplyAmount.value) {
        uni.showToast({ title: '退款金额不能大于商品金额', icon: 'none' })
        formData.apply_amount = maxApplyAmount.value
    }

}, 500);

// 提交退款申请
const submitRefund = async () => {
    try {
        // 将图片数组转换为逗号分隔的字符串
        const submitData = {
            ...formData,
            refund_images: formData.refund_images.join(',')
        }
        
        const res = await applyRefund(submitData)
        if (res.code === 10000) {
            uni.showToast({ title: '退款申请提交成功' })
            setTimeout(() => {
                uni.navigateBack()
            }, 1500)
        } else {
            uni.showToast({ title: res.message, icon: 'none' })
        }
    } catch (error) {
        uni.showToast({ title: '提交失败，请重试', icon: 'none' })
    }
}

// 加载订单详情
const loadOrderDetail = async (order_id: number) => {
    try {
        uni.showLoading({
            title: '加载中...'
        });

        const response = await getTblOrderInfo(String(order_id));

        // 更新订单详情
        Object.assign(orderDetail, response.data);
        formData.apply_amount = maxApplyAmount.value

        uni.hideLoading();
    } catch (error) {
        console.error('获取订单详情失败:', error);
        uni.hideLoading();
        uni.showToast({
            title: '获取订单详情失败',
            icon: 'none'
        });
    }
};

// 页面加载
onLoad((options: any) => {
    if (options?.order_id) {
        const orderId = Number(options.order_id);
        formData.order_id = orderId;
        // 获取订单详情
        loadOrderDetail(orderId);
    }
    if (options?.order_goods_id) {
        formData.order_goods_id = Number(options.order_goods_id);
    } else {
        // 未传商品ID，则默认为全部商品退款
        formData.order_goods_id = 0;
    }
});
</script>

<style lang="scss" scoped>
.goods-card {
    display: flex;
    padding: 20rpx;
    background-color: #fff;
    border-radius: 12rpx;
    margin-bottom: 20rpx;
}

.goods-image {
    width: 160rpx;
    height: 160rpx;
    border-radius: 8rpx;
}

.goods-info {
    flex: 1;
    margin-left: 20rpx;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.goods-name {
    font-size: 28rpx;
    color: #333;
}

.goods-spec {
    font-size: 24rpx;
    color: #999;
}

.goods-price {
    font-size: 28rpx;
    color: #f44;
}

.upload-section {
    padding: 20rpx 0;
    border-bottom: 1rpx solid #f0f0f0;

    .field-name {
        font-size: 28rpx;
        color: #333;
        margin-bottom: 20rpx;
        display: block;
    }

    .upload-tips {
        font-size: 24rpx;
        color: #999;
        margin-top: 10rpx;
        display: block;
    }
}



</style>