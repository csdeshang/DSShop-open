<template>
    <uni-popup ref="popup" type="bottom" :safe-area="false">
        <view class="cart-drawer" v-if="visible">
            <view class="drawer-header">
                <text class="header-title">购物车</text>
                <view class="header-actions">
                    <uni-icons type="close" size="24" @click="handleClose"></uni-icons>
                </view>
            </view>
            
            <scroll-view class="cart-content" scroll-y>
                <view v-if="cartItemList.length === 0" class="empty-cart">
                    <uni-icons type="cart" size="60" color="#ccc"></uni-icons>
                    <text class="empty-text">购物车是空的</text>
                </view>
                <view v-else class="cart-list">
                    <view 
                        v-for="item in cartItemList" 
                        :key="item.id"
                        class="cart-item"
                    >
                        <image 
                            :src="formatImageUrl(item.goods.cover_image, ThumbnailPresets.small, 'goods')" 
                            class="item-image" 
                            mode="aspectFill"
                        />
                        <view class="item-info">
                            <text class="item-name">{{ item.goods.goods_name }}</text>
                            <text class="item-spec" v-if="item.goodsSku && item.goodsSku.sku_name">{{ item.goodsSku.sku_name }}</text>
                            <view class="item-price-control">
                                <text class="item-price">¥{{ item.promotion_price }}</text>
                                <view class="quantity-control">
                                    <view class="minus-btn" @click="decreaseQuantity(item)">
                                        <uni-icons type="minus" size="22" color="#ff6600"></uni-icons>
                                    </view>
                                    <text class="quantity">{{ item.quantity }}</text>
                                    <view class="plus-btn" @click="increaseQuantity(item)">
                                        <uni-icons type="plus" size="22" color="#ff6600"></uni-icons>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

interface CartItemData {
    id: number
    platform: string
    user_id: number
    goods_id: number
    sku_id: number
    quantity: number
    promotion_platform: string
    promotion_type: string
    promotion_related_id: number
    promotion_price: number
    is_active: number
    create_at: string
    update_at: string
    goods: {
        id: number
        brand_id: number
        goods_name: string
        cover_image: string
        goods_status: number
        sys_status: number
    }
    goodsSku: {
        id: number
        goods_id: number
        sku_name: string
        sku_image: string
        sku_code: string
        sku_price: number
        sku_stock: number
        sku_spec_format: string
        market_price: number
        cost_price: number
        is_default: number
    }
}

interface Props {
    visible: boolean
    cartItems: CartItemData[]
}

interface Emits {
    (e: 'close'): void
    (e: 'update-quantity', goodsId: string, quantity: number): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const popup = ref()

// 将Map转换为数组
const cartItemList = computed(() => {
    return props.cartItems
})

// 监听visible变化，控制弹窗显示/隐藏
watch(() => props.visible, (newVal) => {
    if (newVal) {
        popup.value?.open()
    } else {
        popup.value?.close()
    }
})

const handleClose = () => {
    emit('close')
}

const decreaseQuantity = (item: CartItemData) => {
    const newQuantity = item.quantity - 1
    emit('update-quantity', item.id.toString(), newQuantity)
}

const increaseQuantity = (item: CartItemData) => {
    const newQuantity = item.quantity + 1
    emit('update-quantity', item.id.toString(), newQuantity)
}
</script>

<style lang="scss" scoped>
.cart-drawer {
    background: #fff;
    border-radius: 20rpx 20rpx 0 0;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    padding-bottom: 140rpx;
}

.drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx;
    border-bottom: 1rpx solid #f0f0f0;
    
    .header-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 30rpx;
        
        .clear-btn {
            font-size: 28rpx;
            color: #ff6600;
        }
    }
}

.cart-content {
    flex: 1;
    max-height: 60vh;
}

.empty-cart {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100rpx 0;
    
    .empty-text {
        font-size: 28rpx;
        color: #999;
        margin-top: 20rpx;
    }
}

.cart-list {
    padding: 0 30rpx;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 30rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
    
    &:last-child {
        border-bottom: none;
    }
}

.item-image {
    width: 120rpx;
    height: 120rpx;
    border-radius: 8rpx;
    margin-right: 20rpx;
    flex-shrink: 0;
}

.item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10rpx;
}

.item-name {
    font-size: 28rpx;
    color: #333;
    font-weight: 500;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

.item-spec {
    font-size: 24rpx;
    color: #666;
}

.item-price-control {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 15rpx;
}

.item-price {
    font-size: 30rpx;
    font-weight: bold;
    color: #ff6600;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 20rpx;
    
    .minus-btn, .plus-btn {
        width: 50rpx;
        height: 50rpx;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .minus-btn {
        // 去掉背景和边框
    }
    
    .plus-btn {
        // 去掉背景
    }
    
    .quantity {
        font-size: 28rpx;
        font-weight: bold;
        color: #333;
        min-width: 40rpx;
        text-align: center;
    }
}
</style> 