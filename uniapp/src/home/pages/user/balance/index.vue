<template>
    <view class="balance-page">
        <!-- 资产卡片 -->
        <view class="balance-card">
            <view class="balance-header">
                <text class="balance-title">我的余额</text>
                <view class="balance-refresh" @click="refreshBalance">
                    <uni-icons type="reload" size="18" color="#fff"></uni-icons>
                </view>
            </view>
            
            <view class="balance-amount">
                <text class="amount-symbol">¥</text>
                <text class="amount-value">{{ userInfo.balance || '0.00' }}</text>
            </view>
            
            <view class="balance-stats">
                <view class="stats-item">
                    <text class="stats-label">总收入</text>
                    <text class="stats-value">{{ userInfo.balance_in || '0.00' }}</text>
                </view>
                <view class="stats-divider"></view>
                <view class="stats-item">
                    <text class="stats-label">总支出</text>
                    <text class="stats-value">{{ userInfo.balance_out || '0.00' }}</text>
                </view>
            </view>
        </view>
        
        <!-- 操作按钮 -->
        <view class="action-buttons">
            <view class="action-button recharge" @click="goToPage({ url: '/home/pages/user/recharge/add' })">
                <view class="button-icon">
                    <uni-icons type="plus" size="24" color="#FF4444"></uni-icons>
                </view>
                <text class="button-text">余额充值</text>
            </view>
            <view class="action-button withdraw" @click="goToPage({ url: '/home/pages/user/withdrawal/apply' })">
                <view class="button-icon">
                    <uni-icons type="download" size="24" color="#3399FF"></uni-icons>
                </view>
                <text class="button-text">余额提现</text>
            </view>
        </view>
        
        <!-- 功能列表 -->
        <view class="feature-list">
            <view class="list-header">
                <text class="list-title">交易管理</text>
            </view>
            
            <view class="list-item" @click="goToPage({ url: '/home/pages/user/recharge/logs' })">
                <view class="item-left">
                    <view class="item-icon recharge-icon">
                        <uni-icons type="paperplane" size="20" color="#FF4444"></uni-icons>
                    </view>
                    <text class="item-text">充值记录</text>
                </view>
                <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
            
            <view class="list-item" @click="goToPage({ url: '/home/pages/user/balance/logs' })">
                <view class="item-left">
                    <view class="item-icon balance-icon">
                        <uni-icons type="list" size="20" color="#3399FF"></uni-icons>
                    </view>
                    <text class="item-text">余额明细</text>
                </view>
                <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
            
            <view class="list-item" @click="goToPage({ url: '/home/pages/user/withdrawal/logs' })">
                <view class="item-left">
                    <view class="item-icon withdraw-icon">
                        <uni-icons type="wallet" size="20" color="#FF9900"></uni-icons>
                    </view>
                    <text class="item-text">提现记录</text>
                </view>
                <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
            <view class="list-item" @click="goToPage({ url: '/home/pages/user/withdrawal/accountList' })">
                <view class="item-left">
                    <view class="item-icon withdraw-icon">
                        <uni-icons type="wallet" size="20" color="#FF9900"></uni-icons>
                    </view>
                    <text class="item-text">提现账户</text>
                </view>
                <uni-icons type="right" size="16" color="#ccc"></uni-icons>
            </view>
        </view>
        
        <!-- 提示信息 -->
        <view class="balance-tips">
            <text class="tips-text">余额可用于商城消费，如有疑问请联系客服</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia'
import useUserStore from '@/stores/user'
import { onShow } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { fetchRemoteIconUrl } from '@/utils/image'

const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)

// 刷新余额信息
const refreshBalance = () => {
    userStore.getUserInfoWithRoles(true)
    uni.showToast({
        title: '余额已更新',
        icon: 'success'
    })
}

onShow(() => {
    userStore.getUserInfoWithRoles(true)
})
</script>

<style lang="scss" scoped>
.balance-page {
    min-height: 100vh;
    background-color: #f8f8f8;
    padding: 20rpx;
}

.balance-card {
    background: linear-gradient(135deg, #FF4444, #FF7777);
    border-radius: 20rpx;
    padding: 40rpx 30rpx;
    color: #fff;
    box-shadow: 0 10rpx 20rpx rgba(255, 68, 68, 0.2);
    margin-bottom: 30rpx;
    
    .balance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30rpx;
        
        .balance-title {
            font-size: 32rpx;
            font-weight: 500;
        }
        
        .balance-refresh {
            padding: 6rpx;
        }
    }
    
    .balance-amount {
        margin-bottom: 40rpx;
        
        .amount-symbol {
            font-size: 40rpx;
            font-weight: 500;
            margin-right: 10rpx;
        }
        
        .amount-value {
            font-size: 70rpx;
            font-weight: bold;
        }
    }
    
    .balance-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12rpx;
        padding: 20rpx 30rpx;
        
        .stats-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            
            .stats-label {
                font-size: 26rpx;
                opacity: 0.8;
                margin-bottom: 10rpx;
            }
            
            .stats-value {
                font-size: 32rpx;
                font-weight: 500;
            }
        }
        
        .stats-divider {
            width: 2rpx;
            height: 60rpx;
            background: rgba(255, 255, 255, 0.2);
        }
    }
}

.action-buttons {
    display: flex;
    margin-bottom: 30rpx;
    
    .action-button {
        flex: 1;
        background: #fff;
        border-radius: 16rpx;
        padding: 30rpx 20rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 5rpx 15rpx rgba(0, 0, 0, 0.05);
        
        &.recharge {
            margin-right: 15rpx;
        }
        
        &.withdraw {
            margin-left: 15rpx;
        }
        
        .button-icon {
            width: 80rpx;
            height: 80rpx;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15rpx;
        }
        
        .button-text {
            font-size: 28rpx;
            color: #333;
            font-weight: 500;
        }
    }
}

.feature-list {
    background: #fff;
    border-radius: 16rpx;
    overflow: hidden;
    margin-bottom: 30rpx;
    box-shadow: 0 5rpx 15rpx rgba(0, 0, 0, 0.05);
    
    .list-header {
        padding: 30rpx;
        border-bottom: 1rpx solid #f5f5f5;
        
        .list-title {
            font-size: 32rpx;
            font-weight: bold;
            color: #333;
        }
    }
    
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30rpx;
        border-bottom: 1rpx solid #f5f5f5;
        
        &:last-child {
            border-bottom: none;
        }
        
        .item-left {
            display: flex;
            align-items: center;
            
            .item-icon {
                width: 60rpx;
                height: 60rpx;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-right: 20rpx;
                
                &.recharge-icon {
                    background: rgba(255, 68, 68, 0.1);
                }
                
                &.balance-icon {
                    background: rgba(51, 153, 255, 0.1);
                }
                
                &.withdraw-icon {
                    background: rgba(255, 153, 0, 0.1);
                }
            }
            
            .item-text {
                font-size: 30rpx;
                color: #333;
            }
        }
    }
}

.balance-tips {
    padding: 20rpx;
    text-align: center;
    
    .tips-text {
        font-size: 24rpx;
        color: #999;
    }
}
</style>