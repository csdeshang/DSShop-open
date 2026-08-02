<template>
    <view class="goods-container">
        <view v-for="(item, index) in items" :key="index" class="goods-item">
            <image class="goods-image" :src="formatImageUrl(item.goodsSku.sku_image || item.goods.cover_image, ThumbnailPresets.medium, 'goods')"
                mode="aspectFill" />
            <view class="goods-info">
                <text class="goods-name">
                    <text v-if="item.promotion_type" class="promotion-type">{{ item.promotion_type_desc }}</text>{{ item.goods.goods_name }}
                </text>
                <text class="goods-sku">{{ item.goodsSku.sku_name }}</text>
                <view class="goods-price-row">
                    <view class="price-group">
                        <text class="goods-price promotion-price">¥{{ item.pay_price }}</text>
                        <text v-if="item.pay_price !== item.goodsSku.sku_price" class="goods-original-price">¥{{ item.goodsSku.sku_price }}</text>
                    </view>
                    <view class="quantity-control">
                        <view class="quantity-btn minus" @click.stop="updateQuantity(item, -1)">
                            <uni-icons type="minus" size="24" color="#999"></uni-icons>
                        </view>
                        <text class="goods-quantity">{{ item.quantity }}</text>
                        <view class="quantity-btn plus" @click.stop="updateQuantity(item, 1)">
                            <uni-icons type="plus" size="24" color="#FF4141"></uni-icons>
                        </view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import { updateTblCart } from '@/home/api/tbl-cart/tblCart';

const props = defineProps<{
    items: any[];
    platform: string;
}>();

const emit = defineEmits(['updateCart']);

// 更新商品数量 item 是购物车商品对象
const updateQuantity = async (item: any, change: number) => {

    const newQuantity = item.quantity + change;
    
    // 数量不能小于1
    if (newQuantity < 1) {
        uni.showToast({
            title: '商品数量不能小于1',
            icon: 'none'
        });
        return;
    }
    
    try {
        // 调用更新接口
        const res = await updateTblCart({
            cart_id: item.id,
            quantity: newQuantity,
        }, props.platform);
        
        if (res.code === 10000) {
            // 通知父组件 重新获取购物车数据，获取最新价格
            emit('updateCart');

            // 更新本地数据
            item.quantity = newQuantity;

            uni.showToast({
                title: '更新数量成功',
                icon: 'success'
            });
        } else {
            uni.showToast({
                title: res.message || '更新数量失败',
                icon: 'none'
            });
        }
    } catch (error) {
        console.error('更新商品数量失败', error);
        uni.showToast({
            title: '更新数量失败',
            icon: 'none'
        });
    }
};
</script>

<style lang="scss" scoped>
.goods-container {
    background-color: #fff;
    border-radius: 12rpx;
    padding: 20rpx;
}

.goods-item {
    display: flex;
    flex-direction: row;
    margin-bottom: 30rpx;
    padding-bottom: 20rpx;
    border-bottom: 1px solid #f5f5f5;
    
    &:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
}

.goods-image {
    width: 160rpx;
    height: 160rpx;
    border-radius: 8rpx;
    object-fit: cover;
}

.goods-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding-left: 10rpx;

    .goods-name {
        font-size: 28rpx;
        line-height: 38rpx;
        color: #333;
        font-weight: 500;
        margin-bottom: 6rpx;
    }

    .goods-sku {
        font-size: 24rpx;
        line-height: 32rpx;
        color: #666;
        margin-bottom: 6rpx;
    }

    .goods-price-row {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        line-height: 60rpx;
        margin-top: 10rpx;
        
        .price-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 12rpx;
        }
        
        .goods-price{
            font-size: 28rpx;
            color: #FF4141;
        }
        
        .promotion-price {
            font-weight: bold;
        }
        
        .goods-original-price {
            font-size: 24rpx;
            color: #999;
            text-decoration: line-through;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            height: 52rpx;
            
            .quantity-btn {
                width: 52rpx;
                height: 52rpx;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                transition: all 0.2s;
                
                &:active {
                    opacity: 0.7;
                    transform: scale(0.95);
                }
                
                &.minus {
                    color: #999;
                }
                
                &.plus {
                    color: #FF4141;
                }
            }
            
            .goods-quantity {
                width: 60rpx;
                text-align: center;
                font-size: 28rpx;
                color: #333;
                font-weight: 500;
                margin: 0 2rpx;
            }
        }
    }
}
</style>
