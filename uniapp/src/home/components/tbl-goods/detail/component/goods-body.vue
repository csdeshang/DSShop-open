<template>
    <view class="goods-body-container">
        <view class="section-title">商品详情</view>
        <view class="goods-content">
            <rich-text :nodes="formatRichText(goodsDetail.goods_body)"></rich-text>
        </view>
        
    </view>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { formatRichText } from '@/utils/util';

const props = defineProps({
    goodsDetail: {
        type: Object,
        required: true
    }
});
</script>

<style lang="scss" scoped>
.goods-body-container {
    margin: 20rpx;
    padding: 30rpx;
    background-color: #fff;
    border-radius: 16rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
    
    .section-title {
        font-size: 32rpx;

        color: #333;
        padding-bottom: 24rpx;
        border-bottom: 1rpx solid #f5f5f5;
        margin-bottom: 24rpx;
        text-align: center;
    }
    
    .goods-content {
        font-size: 28rpx;
        color: #333;
        line-height: 1.6;

    }
}
</style>