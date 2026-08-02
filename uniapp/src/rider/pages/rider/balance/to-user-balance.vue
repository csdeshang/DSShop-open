<template>
    <view class="balance-page">
        <view class="balance-header">
            <text class="balance-title">转至用户余额</text>
            <view class="balance-info">
                <text class="label">当前骑手余额</text>
                <text class="value">{{ userInfo?.rider?.balance || '0.00' }}</text>
            </view>
        </view>

        <view class="form-container">
            <view class="form-item">
                <view class="form-label">转入金额</view>
                <view class="form-input">
                    <input 
                        type="digit" 
                        v-model="amount" 
                        placeholder="请输入转入金额" 
                        class="input"
                        @input="validateAmount"
                    />
                </view>
                <view class="error-tip" v-if="errorMessage">{{ errorMessage }}</view>
            </view>

            <view class="tips">
                <view class="tip-item">1. 转入的金额将从您的骑手余额中扣除</view>
                <view class="tip-item">2. 转入后将立即到达您的个人账户余额</view>
                <view class="tip-item">3. 余额转入操作不可撤销，请谨慎填写金额</view>
                <view class="tip-item">4. 个人账户的余额可以提现，以及可用于消费</view>
            </view>

            <view class="btn-container">
                <button 
                    class="submit-btn" 
                    :disabled="!isValid || isSubmitting" 
                    :loading="isSubmitting"
                    @click="submitTransfer"
                >确认转入</button>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { riderBalanceToUser } from '@/rider/api/riderBalance'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { onShow } from '@dcloudio/uni-app'

const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

// 表单数据
const amount = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)

// 余额信息
const availableBalance = computed(() => {
    return userInfo.value?.rider?.balance || 0
})

// 表单验证
const isValid = computed(() => {
    return !errorMessage.value && amount.value !== '' && parseFloat(amount.value) > 0
})

// 金额验证方法
const validateAmount = () => {
    // 清除先前的错误消息
    errorMessage.value = ''
    
    // 检查是否为有效数字
    const amountValue = parseFloat(amount.value)
    if (isNaN(amountValue)) {
        errorMessage.value = '请输入有效的金额'
        return
    }
    
    // 检查是否大于0
    if (amountValue <= 0) {
        errorMessage.value = '转入金额必须大于0'
        return
    }
    
    // 检查小数位数不超过2位
    if (amount.value.includes('.') && amount.value.split('.')[1].length > 2) {
        errorMessage.value = '金额最多保留两位小数'
        return
    }
    
    // 检查是否超过可用余额
    if (amountValue > availableBalance.value) {
        errorMessage.value = '转入金额不能超过可用余额'
        return
    }
}

// 提交转账
const submitTransfer = async () => {
    // 再次验证
    validateAmount()
    if (!isValid.value) return
    
    try {
        isSubmitting.value = true
        
        const result = await riderBalanceToUser({
            change_amount: parseFloat(amount.value)
        })
        
        if (result.code === 10000) {
            // 转账成功
            uni.showToast({
                title: '转入成功',
                icon: 'success'
            })
            
            // 刷新用户信息
            await userStore.getUserInfoWithRoles(true)
            
            // 返回上一页
            setTimeout(() => {
                uni.navigateBack()
            }, 1500)
        } else {
            uni.showToast({
                title: result.message || '转入失败',
                icon: 'none'
            })
        }
    } catch (error) {
        uni.showToast({
            title: '操作失败，请稍后重试',
            icon: 'none'
        })
        console.error('转入用户余额失败:', error)
    } finally {
        isSubmitting.value = false
    }
}

// 页面显示时刷新用户信息
onShow(() => {
    userStore.getUserInfoWithRoles(true)
})
</script>

<style lang="scss" scoped>
.balance-page {
    min-height: 100vh;
    background-color: #f8f8f8;
}

.balance-header {
    background: linear-gradient(135deg, #2979FF 0%, #56CCF2 100%);
    padding: 40rpx 30rpx;
    color: #fff;
    
    .balance-title {
        font-size: 36rpx;
        font-weight: bold;
        margin-bottom: 20rpx;
        display: block;
    }
    
    .balance-info {
        margin-top: 20rpx;
        
        .label {
            font-size: 26rpx;
            opacity: 0.8;
            margin-right: 10rpx;
        }
        
        .value {
            font-size: 46rpx;
            font-weight: bold;
        }
    }
}

.form-container {
    padding: 30rpx;
    
    .form-item {
        background-color: #fff;
        border-radius: 12rpx;
        padding: 30rpx;
        margin-bottom: 30rpx;
        
        .form-label {
            font-size: 30rpx;
            color: #333;
            margin-bottom: 20rpx;
            font-weight: 500;
        }
        
        .form-input {
            position: relative;
            
            .input {
                height: 90rpx;
                border-bottom: 1px solid #eee;
                font-size: 36rpx;
                font-weight: bold;
                color: #333;
            }
        }
        
        .error-tip {
            font-size: 24rpx;
            color: #ff4d4f;
            margin-top: 10rpx;
        }
    }
    
    .tips {
        padding: 20rpx 0;
        
        .tip-item {
            font-size: 24rpx;
            color: #999;
            margin-bottom: 10rpx;
            padding-left: 20rpx;
            position: relative;
            
            &::before {
                content: '';
                position: absolute;
                left: 0;
                top: 14rpx;
                width: 8rpx;
                height: 8rpx;
                background-color: #2979FF;
                border-radius: 50%;
            }
        }
    }
    
    .btn-container {
        margin-top: 60rpx;
        
        .submit-btn {
            background: linear-gradient(135deg, #2979FF 0%, #56CCF2 100%);
            color: #fff;
            height: 90rpx;
            line-height: 90rpx;
            border-radius: 45rpx;
            font-size: 32rpx;
            font-weight: 500;
            box-shadow: 0 10rpx 20rpx rgba(41, 121, 255, 0.2);
            
            &:disabled {
                opacity: 0.6;
                background: linear-gradient(135deg, #a0a0a0 0%, #c0c0c0 100%);
                box-shadow: none;
            }
        }
    }
}
</style>