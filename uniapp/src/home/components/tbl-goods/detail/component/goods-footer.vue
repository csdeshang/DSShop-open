<template>
    <view>
        <view class="footer-bar-placeholder"></view>
        <view class="footer-bar">
            <view class="left">
                <view class="icon-btn" @click="goPlatformIndexPage(platform)">
                    <uni-icons type="shop" size="24"></uni-icons>
                    <text>首页</text>
                </view>
                <view class="icon-btn" @click="goToService">
                    <uni-icons type="headphones" size="24"></uni-icons>
                    <text>客服</text>
                </view>
                <view class="icon-btn" @click="goToPage({ url: `/home/platform/${platform}/pages/cart/index` })">
                    <uni-icons type="cart" size="24"></uni-icons>
                    <text>购物车</text>
                </view>
            </view>
            <view class="right">
                <button class="add-cart-btn" @click="handleAddToCart">
                    加入购物车
                </button>
                <button class="buy-now-btn" @click="handleBuyNow">
                    立即购买
                </button>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { goToPage } from '@/utils/util';
import { goPlatformIndexPage } from '@/utils/platform';

const emit = defineEmits(['addToCart', 'buyNow']);

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    }
});

// 从 goodsDetail 获取平台信息
const platform = computed(() => {
    return props.goodsDetail?.platform || 'mall';
});

// 跳转到客服
const goToService = () => {
    // 实现客服跳转逻辑
}

const handleAddToCart = () => {
    emit('addToCart');
};

const handleBuyNow = () => {
    emit('buyNow');
};
</script>

<style lang="scss" scoped>
.footer-bar-placeholder {
    height: 100rpx;
    width: 100%;
    padding-bottom: env(safe-area-inset-bottom);
}

.footer-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100rpx;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20rpx;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
    padding-bottom: env(safe-area-inset-bottom);

    .left {
        display: flex;
        align-items: center;
        width: 120px;
        justify-content: space-between;

        .icon-btn {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;

            text {
                font-size: 20rpx;
                color: #666;
                margin-top: 4rpx;
            }
        }
    }

    .right {
        display: flex;
        align-items: center;

        .add-cart-btn,
        .buy-now-btn {
            height: 80rpx;
            line-height: 80rpx;
            background: linear-gradient(90deg, #FF8C19, #FF6034);
            /* 使用渐变背景 */
            color: #fff;
            font-size: 28rpx;
            border-radius: 40rpx;
            flex: 1;
            margin: 0 10rpx;
            transition: background 0.3s, transform 0.2s;
            /* 添加过渡效果 */

            &:disabled {
                opacity: 0.6;
                /* 禁用状态 */
            }

            &:hover {
                background: linear-gradient(90deg, #FF6034, #FF8C19);
                /* 悬停时的颜色变化 */
            }

            &:active {
                transform: scale(0.95);
                /* 点击时缩小效果 */
            }
        }
    }
}
</style>