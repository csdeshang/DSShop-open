<template>
    <view class="points-goods-body">
        <view class="section-title">商品详情</view>
        <view class="goods-body-content" v-if="goodsDetail.goods_body">
            <rich-text :nodes="formatRichText(goodsDetail.goods_body)"></rich-text>
        </view>
        <view class="no-content" v-else>
            <uni-icons type="info" size="40" color="#ccc"></uni-icons>
            <text class="no-content-text">暂无详情介绍</text>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { formatRichText } from '@/utils/util';

defineProps<{
    goodsDetail: any
}>()
</script>

<style lang="scss" scoped>
.points-goods-body {
    background-color: #fff;
    margin-bottom: 20rpx;
    
    .section-title {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
        padding: 30rpx 30rpx 20rpx;
        border-bottom: 1rpx solid #f0f0f0;
    }
    
    .goods-body-content {
        padding: 20rpx 30rpx 30rpx;
        
        // 小程序 rich-text 不支持外部 CSS，样式通过 JavaScript 内联处理
    }
    
    .no-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 60rpx 0;
        
        .no-content-text {
            font-size: 26rpx;
            color: #999;
            margin-top: 16rpx;
        }
    }
}
</style>
