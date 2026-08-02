<template>
    <view class="quick-goods-item">
        <image 
            :src="formatImageUrl(goods.cover_image, ThumbnailPresets.small, 'goods')" 
            class="goods-image" 
            mode="aspectFill"
            @click="showDetail"
        />
        <view class="goods-info" @click="showDetail">
            <text class="goods-name">{{ goods.goods_name }}</text>
            <text class="goods-desc" v-if="goods.goods_advword">{{ goods.goods_advword }}</text>
            <view class="goods-meta">
                <text class="sales-info">月售{{ (goods.sales_num || 0) + (goods.virtual_sales_num || 0) }}份</text>
                <text class="rating-info" v-if="goods.avg_goods_score && goods.avg_goods_score !== '0.00'">
                    {{ goods.avg_goods_score }}分
                </text>
            </view>
            <view class="price-section">
                <view class="price-info">
                    <text class="current-price">¥{{ goods.goodsSku.sku_price }}</text>
                    <text 
                        v-if="goods.goodsSku.market_price && goods.goodsSku.market_price > goods.goodsSku.sku_price" 
                        class="market-price"
                    >
                    
                        ¥{{ goods.goodsSku.market_price }}
                    </text>

                </view>
                <view class="add-cart-section">
                    <!-- 多规格商品：始终显示选规格按钮 -->
                    <view v-if="isMultiSpec" class="spec-selection" @click.stop="handleSpecSelection">
                        <view class="spec-btn">
                            <text class="spec-text">选规格</text>
                            <!-- 如果有购物车数量，绝对定位显示数量 -->
                            <view v-if="cartQuantity > 0" class="quantity-badge">
                                {{ cartQuantity }}
                            </view>
                        </view>
                    </view>
                    
                    <!-- 单规格商品：直接加减操作 -->
                    <view v-else class="single-spec">
                        <view v-if="cartQuantity === 0" class="add-btn" @click.stop="handleAddToCart">
                            <uni-icons type="plus" size="22" color="#ff6600"></uni-icons>
                        </view>
                        <view v-else class="quantity-control">
                            <view class="minus-btn" @click.stop="handleRemoveFromCart">
                                <uni-icons type="minus" size="22" color="#ff6600"></uni-icons>
                            </view>
                            <text class="quantity">{{ cartQuantity }}</text>
                            <view class="plus-btn" @click.stop="handleAddToCart">
                                <uni-icons type="plus" size="22" color="#ff6600"></uni-icons>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

interface GoodsSku {
    id: number
    goods_id: number
    sku_name: string
    sku_image: string
    sku_price: number
    market_price: number
    sku_stock: number
    is_default: number
}

interface Goods {
    id: number
    goods_name: string
    goods_advword: string
    cover_image: string
    sales_num: number
    virtual_sales_num: number
    avg_goods_score: string
    goodsSku: GoodsSku
    goodsSkuList: GoodsSku[]
}

interface Props {
    goods: Goods
    cartQuantity: number
}

interface Emits {
    (e: 'add-to-cart', goods: Goods): void
    (e: 'remove-from-cart', goodsId: number): void
    (e: 'show-spec', goods: Goods): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// 判断是否为多规格商品
const isMultiSpec = computed(() => {
    return props.goods.goodsSkuList && props.goods.goodsSkuList.length > 1
})

const handleAddToCart = () => {
    emit('add-to-cart', props.goods)
}

const handleRemoveFromCart = () => {
    emit('remove-from-cart', props.goods.id)
}

const showDetail = () => {
    // 点击商品区域，如果是多规格则显示规格选择
    if (isMultiSpec.value) {
        emit('show-spec', props.goods)
    }
}

const handleSpecSelection = () => {
    // 点击选规格按钮或多规格商品的加号
    emit('show-spec', props.goods)
}
</script>

<style lang="scss" scoped>
.quick-goods-item {
    display: flex;
    padding: 30rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
    position: relative;
    
    &:last-child {
        border-bottom: none;
    }
}

.goods-image {
    width: 160rpx;
    height: 160rpx;
    border-radius: 12rpx;
    margin-right: 20rpx;
    flex-shrink: 0;
}

.goods-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 160rpx;
}

.goods-name {
    font-size: 30rpx;
    font-weight: bold;
    color: #333;
    line-height: 1.4;
    margin-bottom: 8rpx;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

.goods-desc {
    font-size: 24rpx;
    color: #666;
    line-height: 1.4;
    margin-bottom: 8rpx;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
}

.goods-meta {
    display: flex;
    align-items: center;
    gap: 20rpx;
    margin-bottom: 15rpx;
    
    .sales-info, .rating-info {
        font-size: 22rpx;
        color: #999;
    }
}

.price-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.price-info {
    display: flex;
    align-items: center;
    gap: 10rpx;
    
    .current-price {
        font-size: 32rpx;
        font-weight: bold;
        color: #ff6600;
    }
    
    .market-price {
        font-size: 24rpx;
        color: #999;
        text-decoration: line-through;
    }
}

.add-cart-section {
    .add-btn {
        width: 60rpx;
        height: 60rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        
        uni-icons {
            font-size: 20px !important;
        }
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        
        .minus-btn, .plus-btn {
            width: 60rpx;
            height: 60rpx;
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
    
    // 多规格选择相关样式
    .spec-selection {
        .spec-btn {
            position: relative;
            padding: 12rpx 20rpx;
            background: #fff;
            border: 2rpx solid #ff6600;
            border-radius: 30rpx;
            
            .spec-text {
                font-size: 24rpx;
                color: #ff6600;
                font-weight: bold;
            }
        }
        
        .quantity-badge {
            position: absolute;
            top: -8rpx;
            right: -8rpx;
            background: #ff6600;
            color: #fff;
            border-radius: 50%;
            width: 32rpx;
            height: 32rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22rpx;
            font-weight: bold;
            z-index: 10;
        }
    }
    
    .single-spec {
        // 单规格样式与原有保持一致
    }
}
</style> 