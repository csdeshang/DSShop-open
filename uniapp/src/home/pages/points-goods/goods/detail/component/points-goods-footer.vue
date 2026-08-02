<template>
    <view class="points-goods-footer">
        <view class="footer-content">
            <view class="price-info">
                <view class="points-price">
                    <text class="points-value">{{ goodsDetail.points_price }}</text>
                    <text class="points-text">积分</text>
                </view>
            </view>
            
            <view class="action-buttons">
                <button 
                    class="exchange-btn" 
                    :class="{ disabled: !canExchange }"
                    :disabled="!canExchange"
                    @click="handleExchange"
                >
                    {{ exchangeBtnText }}
                </button>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

const props = defineProps<{
    goodsDetail: any
}>()

const emit = defineEmits<{
    exchange: []
}>()

// 是否可以兑换
const canExchange = computed(() => {
    return props.goodsDetail?.goods_status === 1 && 
           props.goodsDetail?.stock_num > 0
})

// 兑换按钮文本
const exchangeBtnText = computed(() => {
    if (!props.goodsDetail) return '加载中...'
    
    if (props.goodsDetail.goods_status !== 1) {
        return '商品已下架'
    }
    
    if (props.goodsDetail.stock_num <= 0) {
        return '库存不足'
    }
    
    return '立即兑换'
})

// 处理兑换
const handleExchange = () => {
    if (!canExchange.value) {
        uni.showToast({
            title: '当前无法兑换',
            icon: 'none'
        })
        return
    }
    
    emit('exchange')
}
</script>

<style lang="scss" scoped>
.points-goods-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    border-top: 1rpx solid #f0f0f0;
    padding: 20rpx 30rpx;
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
    z-index: 100;
    
    .footer-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        
        .price-info {
            flex: 1;
            
            .points-price {
                display: flex;
                align-items: baseline;
                
                .points-value {
                    font-size: 36rpx;
                    font-weight: 700;
                    color: #E1251B;
                }
                
                .points-text {
                    font-size: 24rpx;
                    color: #E1251B;
                    margin-left: 6rpx;
                }
            }
        }
        
        .action-buttons {
            margin-left: 20rpx;
            
            .exchange-btn {
                background: linear-gradient(135deg, #E1251B 0%, #ff4757 100%);
                color: #fff;
                border: none;
                border-radius: 50rpx;
                padding: 20rpx 40rpx;
                font-size: 28rpx;
                font-weight: 600;
                min-width: 200rpx;
                transition: all 0.3s ease;
                
                &:active {
                    transform: scale(0.98);
                }
                
                &.disabled {
                    background: #ccc;
                    color: #999;
                }
            }
        }
    }
}
</style>
