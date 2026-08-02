<template>
    <view class="points-goods-info">
        <view class="goods-name">{{ goodsDetail.goods_name }}</view>
        <view class="goods-advword" v-if="goodsDetail.goods_advword">{{ goodsDetail.goods_advword }}</view>
        
        <view class="price-section">
            <view class="points-price">
                <text class="points-value">{{ goodsDetail.points_price }}</text>
                <text class="points-text">积分</text>
            </view>
            <view class="market-price" v-if="goodsDetail.market_price && Number(goodsDetail.market_price) > 0">
                <text>市场价：¥{{ goodsDetail.market_price }}</text>
            </view>
        </view>
        
        <view class="goods-stats">
            <view class="stat-item">
                <text class="stat-label">已兑换</text>
                <text class="stat-value">{{ goodsDetail.exchange_num || 0 }}件</text>
            </view>
            <view class="stat-item">
                <text class="stat-label">库存</text>
                <text class="stat-value">{{ goodsDetail.stock_num || 0 }}件</text>
            </view>
            <view class="stat-item">
                <text class="stat-label">点击量</text>
                <text class="stat-value">{{ goodsDetail.click_num || 0 }}次</text>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
defineProps<{
    goodsDetail: any
}>()
</script>

<style lang="scss" scoped>
.points-goods-info {
    background-color: #fff;
    padding: 30rpx;
    margin-bottom: 20rpx;
    
    .goods-name {
        font-size: 36rpx;
        font-weight: 600;
        color: #333;
        line-height: 1.4;
        margin-bottom: 16rpx;
    }
    
    .goods-advword {
        font-size: 28rpx;
        color: #666;
        line-height: 1.4;
        margin-bottom: 24rpx;
    }
    
    .price-section {
        display: flex;
        align-items: center;
        margin-bottom: 24rpx;
        
        .points-price {
            display: flex;
            align-items: baseline;
            
            .points-value {
                font-size: 48rpx;
                font-weight: 700;
                color: #E1251B;
            }
            
            .points-text {
                font-size: 28rpx;
                color: #E1251B;
                margin-left: 8rpx;
            }
        }
        
        .market-price {
            font-size: 24rpx;
            color: #999;
            text-decoration: line-through;
            margin-left: 20rpx;
        }
    }
    
    .goods-stats {
        display: flex;
        justify-content: space-around;
        padding: 20rpx 0;
        border-top: 1rpx solid #f0f0f0;
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            
            .stat-label {
                font-size: 24rpx;
                color: #999;
                margin-bottom: 8rpx;
            }
            
            .stat-value {
                font-size: 28rpx;
                font-weight: 600;
                color: #333;
            }
        }
    }
}
</style>
