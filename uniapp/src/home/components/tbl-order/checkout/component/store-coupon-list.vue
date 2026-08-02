<template>
    <view>
        <!-- 优惠券选择入口 -->
        <view class="coupon-selector" @click="openCouponPopup">
            <text class="label">优惠券</text>
            <view class="value">
                <text v-if="selectedCoupon">已选择{{ selectedCoupon.storeCoupon.discount_value }}{{ selectedCoupon.storeCoupon.coupon_type === 1 ? '元' : '折' }}优惠券</text>
                <text v-else>{{ availableCoupons.length > 0 ? '选择优惠券' : '暂无可用优惠券' }}</text>
            </view>
            <uni-icons type="right" size="16" color="#999"></uni-icons>
        </view>

        <!-- 优惠券弹窗 -->
        <uni-popup ref="couponPopup" type="bottom">
            <view class="coupon-popup">
                <!-- 标题 -->
                <view class="popup-header">
                    <text class="title">优惠券</text>
                    <uni-icons type="closeempty" size="24" color="#999" @click="closeCouponPopup" />
                </view>

                <!-- 优惠券列表 -->
                <scroll-view scroll-y class="coupon-list" v-if="availableCoupons.length > 0">
                    <!-- 不使用优惠券选项 -->
                    <view class="coupon-item no-coupon" :class="{ 'selected': selectedCouponId === '' }"
                        @click="selectCoupon('')">
                        <text class="no-coupon-text">不使用优惠券</text>
                        <view class="selected-icon" v-if="selectedCouponId === ''">
                            <uni-icons type="checkmarkempty" size="20" color="#FF6034" />
                        </view>
                    </view>

                    <!-- 优惠券列表 -->
                    <view v-for="(item, index) in availableCoupons" :key="index" class="coupon-item"
                        :class="isCouponSelected(item.id)" @click="selectCoupon(item.id)">
                        <view class="coupon-content">
                            <view class="left">
                                <view class="amount">
                                    <text class="prefix">¥</text>
                                    <text class="value">{{ item.storeCoupon.discount_value }}{{ item.storeCoupon.coupon_type === 1 ? '元' : '折' }}</text>
                                </view>
                                <text class="condition">满{{ item.storeCoupon.min_spend }}元可用</text>
                            </view>
                            <view class="right">
                                <text class="name">{{ item.storeCoupon.coupon_name }}</text>
                                <text class="time">{{ formatTimeRange(item.storeCoupon.start_time,
                                    item.storeCoupon.end_time) }}</text>
                            </view>
                            <view class="selected-icon" v-if="selectedCouponId === item.id">
                                <uni-icons type="checkmarkempty" size="20" color="#FF6034" />
                            </view>
                        </view>
                    </view>
                </scroll-view>

                <!-- 无优惠券提示 -->
                <view v-else class="empty-wrapper">
                    <DsEmpty text="暂无可用优惠券" />
                </view>

                <!-- 底部按钮 -->
                <view class="popup-footer">
                    <button class="btn btn-confirm" @click="confirmSelection">确认选择</button>
                </view>
            </view>
        </uni-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import DsEmpty from '@/components/ds-empty/index.vue';

// 组件接收的属性
const props = defineProps({
    modelValue: {
        type: [String, Number, null],
        default: ''
    },
    store_coupons_list: {
        type: Array,
        default: () => []
    }
});

// 组件事件
const emit = defineEmits(['update:modelValue']);

// 弹窗引用
const couponPopup = ref(null);

// 当前选中的优惠券ID
const selectedCouponId = ref(props.modelValue);

// 当前选中的优惠券对象
const selectedCoupon = computed(() => {
    if (!selectedCouponId.value) return null;
    return props.store_coupons_list.find(item => item.id === selectedCouponId.value);
});

// 可用优惠券列表
const availableCoupons = computed(() => {
    return props.store_coupons_list || [];
});

const isCouponSelected = (id) => {
    return selectedCouponId.value === id ? 'selected' : '';
};

// 监听modelValue变化
watch(() => props.modelValue, (newVal) => {
    selectedCouponId.value = newVal;
});

// 打开优惠券弹窗
const openCouponPopup = () => {
    couponPopup.value.open('bottom');
};

// 关闭优惠券弹窗
const closeCouponPopup = () => {
    couponPopup.value.close();
};

// 选择优惠券
const selectCoupon = (couponId) => {
    selectedCouponId.value = couponId;
};

// 确认选择
const confirmSelection = () => {
    emit('update:modelValue', selectedCouponId.value);
    closeCouponPopup();
};

// 格式化时间范围
const formatTimeRange = (startTime, endTime) => {
    // 简化日期显示，只显示年月日
    const start = startTime.split(' ')[0];
    const end = endTime.split(' ')[0];
    return `${start} - ${end}`;
};
</script>

<style lang="scss" scoped>
.coupon-selector {
    display: flex;
    align-items: center;
    padding: 24rpx;
    background-color: #fff;
    border-radius: 12rpx;
    margin-bottom: 20rpx;

    .label {
        font-size: 28rpx;
        color: #333;
        margin-right: 20rpx;
    }

    .value {
        flex: 1;
        font-size: 28rpx;
        color: #666;
        text-align: right;
        margin-right: 10rpx;
    }
}

.coupon-popup {
    background: #fff;
    border-radius: 24rpx 24rpx 0 0;
    padding: 40rpx;
    max-height: 80vh;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30rpx;

    .title {
        font-size: 36rpx;
        font-weight: bold;
        color: #333;
    }
}

.coupon-list {
    max-height: 60vh;
    margin-bottom: 30rpx;
}

.no-coupon {
    padding: 30rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f8f8;
    margin-bottom: 20rpx;
    border-radius: 16rpx;

    .no-coupon-text {
        font-size: 28rpx;
        color: #666;
    }

    &.selected {
        background: #fff5f0;
        border: 2rpx solid #FF6034;
    }
}

.coupon-item {
    background: linear-gradient(to right, #fff9f9, #fff);
    border-radius: 16rpx;
    box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
    margin-bottom: 24rpx;
    position: relative;
    overflow: hidden;
    border: 2rpx solid transparent;
    transition: all 0.3s;

    &.selected {
        border-color: #FF6034;
        background: linear-gradient(to right, #fff9f0, #fff);
    }

    &::before {
        content: '';
        position: absolute;
        left: 160rpx;
        top: 0;
        height: 100%;
        width: 2rpx;
        background: repeating-linear-gradient(to bottom, #eee 0, #eee 12rpx, transparent 12rpx, transparent 24rpx);
    }
}

.coupon-content {
    display: flex;
    padding: 30rpx;
    position: relative;

    .left {
        width: 160rpx;
        text-align: center;
        position: relative;

        .amount {
            font-weight: bold;
            color: #FF6034;
            display: flex;
            align-items: baseline;
            justify-content: center;

            .prefix {
                font-size: 28rpx;
            }

            .value {
                font-size: 48rpx;
            }
        }

        .condition {
            font-size: 24rpx;
            color: #999;
            display: block;
            margin-top: 8rpx;
        }
    }

    .right {
        flex: 1;
        padding-left: 30rpx;

        .name {
            font-size: 32rpx;
            color: #333;
            font-weight: bold;
            display: block;
            margin-bottom: 16rpx;
        }

        .time {
            font-size: 24rpx;
            color: #999;
            display: block;
        }
    }

    .selected-icon {
        position: absolute;
        right: 30rpx;
        top: 50%;
        transform: translateY(-50%);
    }
}

.empty-wrapper {
    padding: 60rpx 0;
    display: flex;
    justify-content: center;
}

.popup-footer {
    display: flex;
    justify-content: center;
    margin-top: 20rpx;

    .btn {
        flex: 1;
        height: 80rpx;
        line-height: 80rpx;
        border-radius: 40rpx;
        font-size: 28rpx;
        font-weight: 500;

        &.btn-confirm {
            background: #FF6034;
            color: #fff;
        }
    }
}
</style>