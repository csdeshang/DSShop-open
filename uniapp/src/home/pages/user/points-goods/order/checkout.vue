<template>
    <view class="points-goods-checkout-page">
        <!-- 收货地址 -->
        <Address :address="orderInfo.address_info" @confirm="handleAddressConfirm" />

        <!-- 商品信息卡片 -->
        <view class="ds-rounded mb-20 p-20" v-if="orderInfo.goods_info">
            <view class="goods-section">
                <view class="goods-item">
                    <image class="goods-image" :src="formatImageUrl(orderInfo.goods_info.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ orderInfo.goods_info.goods_name }}</view>
                        <view class="goods-price">
                            <text class="points-price">{{ orderInfo.goods_info.points_price }}积分</text>
                            <text class="market-price" v-if="orderInfo.goods_info.market_price > 0">市场价：¥{{ orderInfo.goods_info.market_price }}</text>
                        </view>
                        <view class="quantity-info">
                            <text>兑换数量：{{ orderInfo.quantity }}件</text>
                        </view>
                    </view>
                </view>
            </view>

            <!-- 订单备注 -->
            <view class="ds-field-line">
                <view class="field-name">
                    <text>订单备注</text>
                </view>
                <view class="field-input">
                    <input class="flex-1" v-model="userRemark" placeholder="请输入订单备注" />
                </view>
            </view>

            <!-- 订单明细 -->
            <view class="ds-field-line">
                <view class="field-name">
                    <text>积分单价</text>
                </view>
                <view class="field-info">
                    <text>{{ orderInfo.goods_info?.points_price || 0 }}积分</text>
                </view>
            </view>

            <view class="ds-field-line">
                <view class="field-name">
                    <text>兑换数量</text>
                </view>
                <view class="field-info">
                    <text>{{ orderInfo.quantity || 0 }}件</text>
                </view>
            </view>

            <view class="ds-field-line" v-if="orderInfo.goods_info?.market_price > 0">
                <view class="field-name">
                    <text>市场价值</text>
                </view>
                <view class="field-info">
                    <text>¥{{ orderInfo.total_market_price || 0 }}</text>
                </view>
            </view>

            <view class="store-total">
                <text>小计: {{ orderInfo.total_points || 0 }}积分</text>
            </view>
        </view>

        <!-- 结算底部占位 -->
        <view class="order-footer-placeholder"></view>

        <!-- 底部操作栏 -->
        <view class="order-footer">
            <view class="total-price">
                总计: <text>{{ orderInfo.total_points || 0 }}积分</text>
            </view>
            <button 
                class="confirm-order-btn" 
                :class="{ disabled: !canCheckout }"
                :disabled="!canCheckout"
                @click="handleCheckout"
            >
                {{ checkoutBtnText }}
            </button>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { checkPointsGoodsOrderCheckout, generatePointsGoodsOrder } from '@/home/api/user/userPointsGoodsOrderCheckout'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import Address from '@/home/components/tbl-order/checkout/component/address.vue'

const goodsId = ref<number>(0)
const quantity = ref<number>(1)
const userRemark = ref<string>('')
const orderInfo = ref<any>({})
const loading = ref<boolean>(false)

// 是否可以结算
const canCheckout = computed(() => {
    return orderInfo.value.goods_info && 
           orderInfo.value.address_info && 
           orderInfo.value.total_points > 0
})

// 结算按钮文本
const checkoutBtnText = computed(() => {
    if (loading.value) return '处理中...'
    if (!orderInfo.value.goods_info) return '加载中...'
    if (!orderInfo.value.address_info) return '请选择收货地址'
    return '确认兑换'
})

// 加载订单信息
const loadOrderInfo = async () => {
    if (!goodsId.value) return
    
    loading.value = true
    try {
        const res = await checkPointsGoodsOrderCheckout({
            goods_id: goodsId.value,
            quantity: quantity.value
        })
        orderInfo.value = res.data
    } catch (error) {
        console.error('加载订单信息失败:', error)
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        })
    } finally {
        loading.value = false
    }
}

// 确认选择收货地址
const handleAddressConfirm = (address: any) => {
    orderInfo.value.address_info = address
}

// 处理结算
const handleCheckout = async () => {
    if (!canCheckout.value) {
        uni.showToast({
            title: '请完善订单信息',
            icon: 'none'
        })
        return
    }
    
    loading.value = true
    try {
        const res = await generatePointsGoodsOrder({
            goods_id: goodsId.value,
            quantity: quantity.value,
            address_id: orderInfo.value.address_info?.id || 0,
            user_remark: userRemark.value
        })
        
        uni.showToast({
            title: '兑换成功',
            icon: 'success'
        })
        
        // 跳转到订订单列表
        setTimeout(() => {
            uni.navigateTo({
                url: `/home/pages/user/points-goods/order/index`
            })
        }, 1500)
        
    } catch (error) {
        console.error('兑换失败:', error)
        uni.showToast({
            title: '兑换失败',
            icon: 'none'
        })
    } finally {
        loading.value = false
    }
}

onLoad((options: any) => {
    goodsId.value = options.id ? Number(options.id) : 0
    quantity.value = options.quantity ? Number(options.quantity) : 1
    
    if (goodsId.value) {
        loadOrderInfo()
    } else {
        uni.showToast({
            title: '商品ID不能为空',
            icon: 'none'
        })
    }
})
</script>

<style lang="scss" scoped>
.points-goods-checkout-page {
    min-height: 100vh;
    background-color: #f6f7f9;
}

// 商品信息
.goods-section {
    .goods-item {
        display: flex;
        margin-bottom: 20rpx;
        
        .goods-image {
            width: 160rpx;
            height: 160rpx;
            border-radius: 8rpx;
            margin-right: 20rpx;
            flex-shrink: 0;
        }
        
        .goods-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            
            .goods-name {
                font-size: 28rpx;
                font-weight: 600;
                color: #333;
                line-height: 1.4;
                margin-bottom: 8rpx;
            }
            
            
            .goods-price {
                display: flex;
                align-items: center;
                margin-bottom: 8rpx;
                
                .points-price {
                    font-size: 32rpx;
                    font-weight: 700;
                    color: #E1251B;
                    margin-right: 16rpx;
                }
                
                .market-price {
                    font-size: 22rpx;
                    color: #999;
                    text-decoration: line-through;
                }
            }
            
            .quantity-info {
                font-size: 24rpx;
                color: #666;
            }
        }
    }
}



.store-total {
    line-height: 120rpx;
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    font-size: 28rpx;
    font-weight: 600;
    color: #E1251B;
    border-top: 1rpx solid #f0f0f0;
    margin-top: 20rpx;
    padding-top: 20rpx;
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

        text {
            font-size: 28rpx;
            font-weight: bold;
            color: #E1251B;
        }
    }

    .confirm-order-btn {
        background-color: #E1251B;
        color: #fff;
        border: none;
        padding: 2rpx 40rpx;
        border-radius: 40rpx;
        font-size: 28rpx;
        font-weight: bold;
        transition: background 0.3s;

        &:hover {
            background-color: #ff4757;
        }
        
        &.disabled {
            background: #ccc;
            color: #999;
        }
    }
}

.order-footer-placeholder {
    height: 200rpx;
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
