<template>
    <view class="recharge-container">
        <!-- 顶部金额输入区域 -->
        <view class="amount-input-area">
            <view class="amount-symbol">￥</view>
            <uni-easyinput
                v-model="pay_amount"
                type="digit"
                placeholder="请输入充值金额"
                class="amount-input"
                :inputBorder="false"
            />
        </view>

        <!-- 快速选择金额 -->
        <view class="quick-amount-section">
            <view class="section-title">快速充值金额</view>
            <view class="amount-grid">
                <view
                    v-for="(item, index) in presetAmounts"
                    :key="index"
                    class="amount-item"
                    :class="{ active: pay_amount == item.value }"
                    @click="selectAmount(item.value)"
                >
                    <view class="amount-value">{{ item.value }}元</view>
                    <view v-if="item.desc" class="amount-desc">{{ item.desc }}</view>
                </view>
            </view>
        </view>

        <!-- 充值按钮 -->
        <view class="btn-section">
            <button @click="handleAdd" type="primary" >确认充值</button>
        </view>

        <!-- 充值说明 -->
        <view class="recharge-info">
            <view class="info-title">充值说明</view>
            <view class="info-item">1. 充值金额将直接进入您的账户余额</view>
            <view class="info-item">2. 充值成功后可用于平台消费</view>
            <view class="info-item">3. 如有疑问请联系客服</view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';

// 充值金额
const pay_amount = ref(10);

// 预设金额列表
const presetAmounts = reactive([
    { value: 10, desc: '' },
    { value: 30, desc: '' },
    { value: 50, desc: '' },
    { value: 100, desc: '' },
    { value: 200, desc: '' },
    { value: 500, desc: '' }
]);

// 选择金额
const selectAmount = (amount) => {
    pay_amount.value = amount;
};

// 充值处理
const handleAdd = () => {
    if (pay_amount.value <= 0) {
        uni.showToast({
            title: '请输入充值金额',
            icon: 'none'
        });
        return;
    }

    let params = {
        source_type: 'recharge',
        source_id: '',
        pay_amount: pay_amount.value,
        pay_info: '充值',
        uni_redirect_url: '/home/pages/user/balance/index'
    };
    
    // 转换参数为查询字符串
    const queryString = Object.keys(params)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
        .join('&');
    
    uni.navigateTo({
        url: '/home/pages/user/pay/index?' + queryString
    });
};
</script>

<style lang="scss" scoped>
.recharge-container {
    padding: 30rpx;
    background-color: #f5f5f5;
    min-height: 100vh;
    box-sizing: border-box;
}

// 顶部金额输入区域
.amount-input-area {
    display: flex;
    align-items: center;
    background-color: #ffffff;
    padding: 40rpx 30rpx;
    border-radius: 12rpx;
    margin-bottom: 30rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.amount-symbol {
    font-size: 50rpx;
    font-weight: bold;
    color: #333;
    margin-right: 20rpx;
}

.amount-input {
    flex: 1;
    font-size: 48rpx;
    font-weight: bold;
}

:deep(.uni-easyinput__content) {
    height: 80rpx;
    background-color: transparent !important;
}

:deep(.uni-easyinput__content-input) {
    font-size: 48rpx !important;
    font-weight: bold;
}

// 快速选择金额区域
.quick-amount-section {
    background-color: #ffffff;
    border-radius: 12rpx;
    padding: 30rpx;
    margin-bottom: 30rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 28rpx;
    color: #666;
    margin-bottom: 20rpx;
}

.amount-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}

.amount-item {
    width: 30%;
    height: 120rpx;
    background-color: #f8f8f8;
    border-radius: 8rpx;
    margin-bottom: 20rpx;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    border: 1px solid #eee;
    
    &.active {
        background-color: rgba(64, 158, 255, 0.1);
        border-color: #409eff;
        .amount-value {
            color: #409eff;
        }
    }
}

.amount-value {
    font-size: 32rpx;
    font-weight: bold;
    color: #333;
}

.amount-desc {
    font-size: 22rpx;
    color: #ff5722;
    margin-top: 5rpx;
}

// 充值按钮区域
.btn-section {
    margin: 40rpx 0;
}



// 充值说明
.recharge-info {
    background-color: #ffffff;
    border-radius: 12rpx;
    padding: 30rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.info-title {
    font-size: 28rpx;
    color: #666;
    margin-bottom: 20rpx;
    font-weight: bold;
}

.info-item {
    font-size: 24rpx;
    color: #999;
    line-height: 1.8;
    padding-left: 10rpx;
}
</style>
