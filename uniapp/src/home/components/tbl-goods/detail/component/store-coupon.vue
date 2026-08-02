<template>
    <!-- 优惠券按钮 -->
    <view class="coupon-container" @click="openStoreCouponPopup">
        <view class="coupon-btn">
            <view class="coupon-content">
                <text class="coupon-text">领取优惠券</text>
                <text class="coupon-desc">立省多多</text>
            </view>
            <view class="coupon-arrow">
                <uni-icons type="right" size="16" color="#999"></uni-icons>
            </view>
        </view>
    </view>

    <!-- 店铺优惠券弹窗 -->
    <TblStoreCouponPopup ref="storeCouponPopup"/>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import TblStoreCouponPopup from '@/home/components/tbl-store/coupon-popup/index.vue';

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    }
});

// 优惠券弹窗
const storeCouponPopup = ref();
const openStoreCouponPopup = () => {
    storeCouponPopup.value?.open();
}
</script>

<style lang="scss" scoped>
.coupon-container {
    margin: 20rpx;
    background-color: #fff;
    border-radius: 16rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}

.coupon-btn {
    display: flex;
    align-items: center;
    padding: 30rpx;
    position: relative;
    
    &:active {
        opacity: 0.8;
    }
}


.coupon-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.coupon-text {
    font-size: 28rpx;
    font-weight: 500;
    color: #333;
    margin-bottom: 4rpx;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.coupon-desc {
    color: #FF6034;
    font-size: 24rpx;
}

.coupon-arrow {
    margin-left: 20rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}


</style>
