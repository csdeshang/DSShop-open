<template>
    <!-- 优惠券按钮 -->
    <view class="kms-lesson-container" @click="goToPage({ url: `/home/platform/kms/pages/lesson/index?goods_id=${goodsDetail.id}` })">
        <view class="kms-lesson-btn">
            <view class="kms-lesson-content">
                <text class="kms-lesson-text">课时</text>
                <text class="kms-lesson-desc">查看课时</text>
            </view>
            <view class="kms-lesson-arrow">
                <uni-icons type="right" size="16" color="#999"></uni-icons>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { goToPage } from '@/utils/util';

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    }
});




</script>


<style lang="scss" scoped>
.kms-lesson-container {
    margin: 20rpx;
    background-color: #fff;
    border-radius: 16rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}

.kms-lesson-btn {
    display: flex;
    align-items: center;
    padding: 30rpx;
    position: relative;
    
    &:active {
        opacity: 0.8;
    }
}


.kms-lesson-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.kms-lesson-text {
    font-size: 28rpx;
    font-weight: 500;
    color: #333;
    margin-bottom: 4rpx;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.kms-lesson-desc {
    color: #FF6034;
    font-size: 24rpx;
}

.kms-lesson-arrow {
    margin-left: 20rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}


</style>