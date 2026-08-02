<template>
    <view class="withdrawal-page">
        <!-- 提现金额输入区域 -->
        <view class="withdrawal-card">
            <view class="available-balance">
                <text class="balance-label">可用余额：</text>
                <text class="balance-value">¥{{ userInfo.balance || '0.00' }}</text>
            </view>

            <view class="input-amount">
                <text class="amount-symbol">¥</text>
                <input class="amount-input" type="digit" v-model="amount" placeholder="请输入提现金额" maxlength="10" />
            </view>

            <view class="amount-actions">
                <text class="all-amount" @click="useAllBalance">全部提现</text>
                <text class="min-tip">最低提现金额 {{ withdrawalConfig.withdrawal_min_amount }} 元</text>
            </view>
        </view>

        <!-- 选择提现账户 -->
        <view class="withdrawal-account">
            <view class="section-title">提现账户</view>

            <view class="account-selector" v-if="accountList.length > 0">
                <view class="account-item" v-for="(account, index) in accountList" :key="account.id"
                    :class="{ active: selectedAccountId === account.id }" @click="selectAccount(account.id)">
                    <view class="account-left">
                        <image class="account-icon" :src="getAccountIcon(account.account_type)" mode="aspectFit">
                        </image>
                        <view class="account-info">
                            <view class="account-name">
                                <text class="name">{{ account.account_name }}</text>
                                <text class="default-tag" v-if="account.is_default === 1">默认</text>
                            </view>
                            <text class="account-number">{{ formatAccountNumber(account) }}</text>
                        </view>
                    </view>
                    <view class="account-radio">
                        <view class="radio-inner" v-if="selectedAccountId === account.id"></view>
                    </view>
                </view>
            </view>

            <view class="empty-account" v-else>
                <text class="empty-text">暂无提现账户</text>
                <button class="add-account-btn" @click="goToAccountSettings">添加提现账户</button>
            </view>
        </view>

        <!-- 提现说明 -->
        <view class="withdrawal-notice">
            <view class="notice-title">
                <uni-icons type="info" size="16" color="#999"></uni-icons>
                <text class="title-text">提现说明</text>
            </view>
            <view class="notice-items">
                <text class="notice-item">1. 提现申请将在1-3个工作日内处理</text>
                <text class="notice-item">2. 单笔提现金额不得低于{{ withdrawalConfig.withdrawal_min_amount }}元</text>
                <text class="notice-item">3. 提现手续费{{ withdrawalConfig.withdrawal_fee_rate }}%</text>
                <text class="notice-item">4. 请确保提现账户信息准确无误</text>
                <text class="notice-item">5. 如有疑问请联系客服</text>
            </view>
        </view>

        <!-- 提交按钮 -->
        <view class="submit-section">
            <button class="submit-btn" :class="{ disabled: !isValid }" :disabled="!isValid" @click="submitWithdrawal">
                提交申请
            </button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import useUserStore from '@/stores/user';
import { getUserWithdrawalAccountList, applyUserWithdrawal } from '@/home/api/user/userWithdrawal';
import { useConfigStore } from '@/stores/modules/config';


const userStore = useUserStore();
const { userInfo } = storeToRefs(userStore);

// 提现申请配置
const configStore = useConfigStore();
const withdrawalConfig = ref<Record<string, string>>({});
async function fetchWithdrawalConfig() {
    withdrawalConfig.value = await configStore.fetchConfigsByType('user_withdrawal');
}



// 提现数据
const amount = ref('');
const accountList = ref<any[]>([]);
const selectedAccountId = ref<number | null>(null);

// 验证提现表单
const isValid = computed(() => {
    // 检查金额
    const amountValue = parseFloat(amount.value);
    if (isNaN(amountValue) || amountValue < withdrawalConfig.value.withdrawal_min_amount || amountValue > parseFloat(userInfo.value.balance || '0')) {
        return false;
    }

    // 检查是否选择了提现账户
    if (selectedAccountId.value === null) {
        return false;
    }

    return true;
});

// 获取账户图标
const getAccountIcon = (type: string) => {
    switch (type) {
        case 'alipay':
            return '/static/icon-alipay.png';
        case 'wechat':
            return '/static/icon-wechat.png';
        case 'bank':
            return '/static/icon-bank.png';
        default:
            return '/static/icon-bank.png';
    }
};

// 格式化账户号码（保护隐私）
const formatAccountNumber = (account: any) => {
    if (!account.account_number) return '';

    if (account.account_type === 'bank') {
        // 银行卡号格式：**** **** **** 1234
        const lastFour = account.account_number.slice(-4);
        return `**** **** **** ${lastFour}`;
    } else if (account.account_type === 'alipay') {
        // 支付宝账号保留前一位和后两位
        if (account.account_number.includes('@')) {
            // 如果是邮箱格式
            const [username, domain] = account.account_number.split('@');
            const maskedUsername = username.slice(0, 1) + '*'.repeat(username.length - 3) + username.slice(-2);
            return `${maskedUsername}@${domain}`;
        } else {
            // 如果是手机号格式
            if (account.account_number.length === 11) {
                return account.account_number.slice(0, 3) + '****' + account.account_number.slice(-4);
            }
        }
    }

    // 其他格式，只显示前1位和后2位
    const len = account.account_number.length;
    if (len <= 3) return account.account_number;
    return account.account_number.slice(0, 1) + '*'.repeat(len - 3) + account.account_number.slice(-2);
};

// 使用全部余额
const useAllBalance = () => {
    amount.value = userInfo.value.balance || '0';
};

// 选择提现账户
const selectAccount = (accountId: number) => {
    selectedAccountId.value = accountId;
};

// 去账户设置页面
const goToAccountSettings = () => {
    uni.navigateTo({
        url: '/home/pages/user/withdrawal/accountList'
    });
};

// 加载提现账户列表
const loadAccountList = async () => {
    try {
        const res = await getUserWithdrawalAccountList();
        if (res.code === 10000 && res.data) {
            accountList.value = res.data;

            // 如果有默认账户，自动选中
            const defaultAccount = accountList.value.find(account => account.is_default === 1);
            if (defaultAccount) {
                selectedAccountId.value = defaultAccount.id;
            } else if (accountList.value.length > 0) {
                // 否则选择第一个账户
                selectedAccountId.value = accountList.value[0].id;
            }
        }
    } catch (error) {
        console.error('获取提现账户失败:', error);
        uni.showToast({
            title: '获取账户列表失败',
            icon: 'none'
        });
    }
};

// 提交提现申请
const submitWithdrawal = async () => {
    if (!isValid.value) {
        return;
    }

    // 构建提现数据
    const withdrawalData = {
        amount: parseFloat(amount.value),
        account_id: selectedAccountId.value
    };

    try {
        const res = await applyUserWithdrawal(withdrawalData);
        if (res.code === 10000) {
            uni.showToast({
                title: '提现申请已提交',
                icon: 'success'
            });

            // 提交成功后返回上一页
            setTimeout(() => {
                uni.navigateBack();
            }, 1500);
        } else {
            uni.showToast({
                title: res.message || '提现失败',
                icon: 'none'
            });
        }
    } catch (error) {
        console.error('提现失败:', error);
        uni.showToast({
            title: '提现失败，请重试',
            icon: 'none'
        });
    }

};




onShow(() => {
    userStore.getUserInfoWithRoles(true);
    loadAccountList();
    fetchWithdrawalConfig();
});
</script>

<style lang="scss" scoped>
.withdrawal-page {
    min-height: 100vh;
    background-color: #f8f8f8;
    padding: 30rpx;
}

.withdrawal-card {
    background: #fff;
    border-radius: 16rpx;
    padding: 30rpx;
    margin-bottom: 30rpx;

    .available-balance {
        display: flex;
        align-items: center;
        margin-bottom: 40rpx;

        .balance-label {
            font-size: 28rpx;
            color: #666;
        }

        .balance-value {
            font-size: 28rpx;
            color: #333;
            font-weight: bold;
        }
    }

    .input-amount {
        display: flex;
        align-items: center;
        padding-bottom: 30rpx;
        border-bottom: 1rpx solid #f5f5f5;

        .amount-symbol {
            font-size: 50rpx;
            font-weight: bold;
            color: #333;
            margin-right: 20rpx;
        }

        .amount-input {
            flex: 1;
            font-size: 50rpx;
            font-weight: bold;
            color: #333;
            height: 100rpx;
        }
    }

    .amount-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20rpx;

        .all-amount {
            font-size: 28rpx;
            color: #FF4444;
        }

        .min-tip {
            font-size: 24rpx;
            color: #999;
        }
    }
}

.withdrawal-account {
    background: #fff;
    border-radius: 16rpx;
    padding: 30rpx;
    margin-bottom: 30rpx;

    .section-title {
        font-size: 30rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 30rpx;
    }

    .account-selector {
        .account-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24rpx 0;
            border-bottom: 1rpx solid #f5f5f5;

            &:last-child {
                border-bottom: none;
            }

            &.active {
                .account-name .name {
                    color: #FF4444;
                }
            }

            .account-left {
                display: flex;
                align-items: center;

                .account-icon {
                    width: 70rpx;
                    height: 70rpx;
                    margin-right: 20rpx;
                }

                .account-info {
                    .account-name {
                        display: flex;
                        align-items: center;
                        margin-bottom: 8rpx;

                        .name {
                            font-size: 30rpx;
                            color: #333;
                            margin-right: 10rpx;
                        }

                        .default-tag {
                            font-size: 22rpx;
                            color: #fff;
                            background: #FF4444;
                            padding: 4rpx 10rpx;
                            border-radius: 4rpx;
                        }
                    }

                    .account-number {
                        font-size: 26rpx;
                        color: #999;
                    }
                }
            }

            .account-radio {
                width: 40rpx;
                height: 40rpx;
                border-radius: 50%;
                border: 2rpx solid #ddd;
                display: flex;
                justify-content: center;
                align-items: center;

                .radio-inner {
                    width: 24rpx;
                    height: 24rpx;
                    border-radius: 50%;
                    background: #FF4444;
                }
            }
        }
    }

    .empty-account {
        padding: 40rpx 0;
        display: flex;
        flex-direction: column;
        align-items: center;

        .empty-text {
            font-size: 28rpx;
            color: #999;
            margin-bottom: 20rpx;
        }

        .add-account-btn {
            font-size: 28rpx;
            color: #fff;
            background: #FF4444;
            border-radius: 45rpx;
            padding: 15rpx 40rpx;
        }
    }
}

.withdrawal-notice {
    background: #fff;
    border-radius: 16rpx;
    padding: 30rpx;
    margin-bottom: 30rpx;

    .notice-title {
        display: flex;
        align-items: center;
        margin-bottom: 20rpx;

        .title-text {
            font-size: 28rpx;
            color: #666;
            margin-left: 10rpx;
        }
    }

    .notice-items {
        display: flex;
        flex-direction: column;

        .notice-item {
            font-size: 26rpx;
            color: #999;
            margin-bottom: 10rpx;

            &:last-child {
                margin-bottom: 0;
            }
        }
    }
}

.submit-section {
    margin-top: 60rpx;
    margin-bottom: 40rpx;

    .submit-btn {
        height: 90rpx;
        line-height: 90rpx;
        background: #FF4444;
        color: #fff;
        font-size: 32rpx;
        font-weight: bold;
        border-radius: 45rpx;

        &.disabled {
            background: #ccc;
        }
    }
}
</style>
