<template>
    <view class="selected-spec" @click="handleClick">
        <view class="left">
            <text class="label">已选规格</text>
            <view class="content">
                <text v-if="selectedSku.sku_name">{{ selectedSku.sku_name }}</text>
                <text v-else>请选择规格</text>
                
                <!-- 显示促销价格信息 -->
                <view class="promotion-info" v-if="hasPromotion">
                    <text class="promotion-tag">{{ selectedSku.promotion_type_desc }}</text>
                    <text class="promotion-price">¥{{ selectedSku.promotion_price }}</text>
                    <text class="original-price">¥{{ selectedSku.sku_price }}</text>
                </view>
            </view>
        </view>
        <uni-icons type="right" size="16" color="#999"></uni-icons>
    </view>
</template>

<script lang="ts" setup>
import { computed } from 'vue';

const props = defineProps({
    goodsDetail: {
        type: Object,
        required: true
    },
    selectedSku: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['click']);

// 是否有促销价格
const hasPromotion = computed(() => {
    return props.selectedSku.promotion_price && 
           props.selectedSku.promotion_price < props.selectedSku.sku_price;
});

const handleClick = () => {
    emit('click');
};
</script>

<style lang="scss" scoped>
.selected-spec {
    margin: 20rpx;
    background: #fff;
    padding: 30rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
    
    &::after {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 8rpx;
        background: linear-gradient(to bottom, #FF8C19, #FF6034);
        border-radius: 4rpx 0 0 4rpx;
    }

    .left {
        flex: 1;
        display: flex;
        align-items: flex-start;
        margin-left: 12rpx;

        .label {
            color: #666;
            font-size: 28rpx;
            margin-right: 20rpx;
            font-weight: 500;
            padding-top: 4rpx;
        }

        .content {
            flex: 1;
            font-size: 28rpx;
            color: #333;
            
            text {
                display: inline-block;
                max-width: 460rpx;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .promotion-info {
                margin-top: 10rpx;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                
                .promotion-tag {
                    height: 32rpx;
                    line-height: 32rpx;
                    padding: 0 8rpx;
                    background-color: #FFF0ED;
                    border: 1rpx solid #FF6034;
                    color: #FF6034;
                    font-size: 20rpx;
                    border-radius: 4rpx;
                    margin-right: 10rpx;
                    margin-bottom: 0;
                    max-width: none;
                }
                
                .promotion-price {
                    color: #FF4444;
                    font-size: 28rpx;
                    font-weight: bold;
                    margin-right: 8rpx;
                    max-width: none;
                }
                
                .original-price {
                    font-size: 24rpx;
                    color: #999;
                    text-decoration: line-through;
                    max-width: none;
                }
            }
        }
    }
    
    &:active {
        background-color: #fafafa;
    }
}
</style> 