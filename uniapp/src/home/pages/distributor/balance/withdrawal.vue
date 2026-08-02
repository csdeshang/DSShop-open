<template>
    <uni-popup ref="popupRef" type="bottom">
        <view class="withdrawal-popup">
            <view class="ds-popup-header">
                <view class="cancel" @click="close">取消</view>
                <view class="title">佣金转入余额</view>
                <view class="confirm" @click="transferAll">全部转入</view>
            </view>

            <view class="withdrawal-content">
                <view class="balance-info">
                    <view class="balance-label">可用佣金</view>
                    <view class="balance-value">¥{{ distributorBalance }}</view>
                </view>

                <view class="amount-input-wrapper">
                    <view class="amount-prefix">¥</view>
                    <input class="amount-input" type="digit" v-model="amount" placeholder="请输入转入金额" :maxlength="10"
                        @input="validateAmount" />
                </view>

                <view class="tips">
                    <text>提示：转入的佣金将直接进入您的钱包余额，可用于消费和提现</text>
                </view>

                <button class="transfer-btn" :disabled="!canTransfer" :class="{ 'btn-disabled': !canTransfer }"
                    @click="confirmTransfer">
                    确认转入
                </button>
            </view>
        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { distributorBalanceToUser } from '@/home/api/distributor/distributor';
import useUserStore from '@/stores/user';

const userStore = useUserStore();
const popupRef = ref();
const loading = ref(false);
const amount = ref('');
const distributorBalance = computed(() => userStore.userInfo?.distributor_balance || 0);

// 是否能够转账
const canTransfer = computed(() => {
    const numAmount = Number(amount.value);
    return numAmount > 0 && numAmount <= distributorBalance.value;
});

// 验证输入金额
const validateAmount = () => {
    // 移除非数字和小数点
    amount.value = amount.value.replace(/[^\d.]/g, '');

    // 确保只有一个小数点
    if (amount.value.split('.').length > 2) {
        amount.value = amount.value.replace(/\.+$/, '');
    }

    // 限制小数点后两位
    if (amount.value.includes('.')) {
        const parts = amount.value.split('.');
        amount.value = parts[0] + '.' + parts[1].slice(0, 2);
    }

    // 确保金额不超过可用佣金
    const numAmount = Number(amount.value);
    if (numAmount > distributorBalance.value) {
        amount.value = String(distributorBalance.value);
    }
};

// 全部转入
const transferAll = () => {
    amount.value = String(distributorBalance.value);
};

// 确认转入
const confirmTransfer = async () => {
    if (!canTransfer.value || loading.value) return;

    try {
        loading.value = true;

        await distributorBalanceToUser({
            change_amount: Number(amount.value)
        });

        uni.showToast({
            title: '转入成功',
            icon: 'success'
        });

        // 刷新用户信息(强制刷新)
        userStore.getUserInfoWithRoles(true);

        // 关闭弹窗
        close();

        // 重置表单
        amount.value = '';

    } catch (error) {
        console.error('转入失败:', error);
        uni.showToast({
            title: '转入失败，请重试',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

const open = () => {
    popupRef.value.open();
};

const close = () => {
    popupRef.value.close();
    amount.value = '';
};

defineExpose({
    open,
    close
});
</script>

<style lang="scss" scoped>
.withdrawal-popup {
    border-top-left-radius: 20rpx;
    border-top-right-radius: 20rpx;
    padding-bottom: 50rpx;

    .ds-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30rpx 30rpx 20rpx;
        border-bottom: 1rpx solid #f5f5f5;

        .title {
            font-size: 32rpx;
            font-weight: bold;
            color: #333;
        }

        .cancel,
        .confirm {
            font-size: 28rpx;
            color: #666;
            padding: 10rpx;
        }

        .confirm {
            color: #FF4141;
        }
    }
}

.withdrawal-content {
    padding: 30rpx;

    .balance-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40rpx;

        .balance-label {
            font-size: 28rpx;
            color: #666;
        }

        .balance-value {
            font-size: 32rpx;
            color: #333;
            font-weight: bold;
        }
    }

    .amount-input-wrapper {
        display: flex;
        align-items: center;
        padding: 20rpx 0;
        border-bottom: 1rpx solid #e5e5e5;
        margin-bottom: 20rpx;

        .amount-prefix {
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
            margin-right: 10rpx;
        }

        .amount-input {
            flex: 1;
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
            height: 80rpx;
        }
    }

    .tips {
        font-size: 24rpx;
        color: #999;
        line-height: 1.5;
        margin-bottom: 60rpx;
    }

    .transfer-btn {
        width: 100%;
        height: 90rpx;
        background: linear-gradient(135deg, #FF4141 0%, #FF6F6F 100%);
        color: #fff;
        font-size: 32rpx;
        font-weight: bold;
        border-radius: 45rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 20rpx;

        &.btn-disabled {
            background: #cccccc;
            color: #ffffff;
        }
    }
}
</style>