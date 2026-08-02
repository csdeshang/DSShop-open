<template>
    <uni-popup ref="popup" type="bottom" background-color="#f8f8f8">
        <view class="account-edit-container">
            <!-- 弹窗标题 -->
            <view class="popup-header">
                <text class="popup-title">{{ popTitle }}</text>
                <view class="popup-close" @click="closeDialog">
                    <uni-icons type="closeempty" size="24" color="#999"></uni-icons>
                </view>
            </view>
            
            <!-- 账户表单 -->
            <view class="account-form">
                <!-- 账户类型选择 -->
                <view class="form-item">
                    <text class="item-label">账户类型</text>
                    <view class="account-types">
                        <view 
                            class="type-item" 
                            v-for="(type, index) in accountTypes" 
                            :key="index"
                            :class="{ active: formData.account_type === type.value }"
                            @click="formData.account_type = type.value"
                        >
                            <text class="type-name">{{ type.name }}</text>
                        </view>
                    </view>
                </view>
                
                <!-- 账户名称 -->
                <view class="form-item">
                    <text class="item-label">账户名称(选填)</text>
                    <input 
                        class="item-input" 
                        v-model="formData.account_name" 
                        placeholder="如：工商银行、支付宝、微信"
                    />
                </view>
                
                <!-- 持卡人姓名 -->
                <view class="form-item">
                    <text class="item-label">持卡人姓名</text>
                    <input 
                        class="item-input" 
                        v-model="formData.account_holder" 
                        placeholder="请输入真实姓名"
                    />
                </view>
                
                <!-- 银行卡号 -->
                <view class="form-item" v-if="formData.account_type === 'bank'">
                    <text class="item-label">银行卡号</text>
                    <input 
                        class="item-input" 
                        v-model="formData.account_number" 
                        placeholder="请输入银行卡号"
                        type="number"
                        maxlength="19"
                    />
                </view>
                
                <!-- 支付宝账号 -->
                <view class="form-item" v-if="formData.account_type === 'alipay'">
                    <text class="item-label">支付宝账号</text>
                    <input 
                        class="item-input" 
                        v-model="formData.account_number" 
                        placeholder="请输入支付宝账号"
                    />
                </view>
                
                <!-- 微信账号 -->
                <view class="form-item" v-if="formData.account_type === 'wechat'">
                    <text class="item-label">微信账号</text>
                    <input 
                        class="item-input" 
                        v-model="formData.account_number" 
                        placeholder="请输入微信账号"
                    />
                </view>
                
                <!-- 设为默认账户 -->
                <view class="form-item switch-item">
                    <text class="item-label">设为默认提现账户</text>
                    <switch 
                        color="#FF4444" 
                        :checked="formData.is_default === 1" 
                        @change="handleDefaultChange"
                    />
                </view>
            </view>
            
            <!-- 操作按钮 -->
            <view class="form-actions">
                <button 
                    class="save-button" 
                    :class="{ disabled: !isValid }"
                    :disabled="!isValid"
                    @click="saveAccount"
                >
                    保存
                </button>
            </view>
        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { getUserWithdrawalAccountInfo, updateUserWithdrawalAccount, createUserWithdrawalAccount } from '@/home/api/user/userWithdrawal';



const emit = defineEmits(['success']);
const popup = ref<any>(null);
const loading = ref(false)
const popTitle = ref('')

/**

* 表单数据
*/
const initialFormData = {
    id: 0,
    account_type: 'bank',
    account_name: '',
    account_number: '',
    account_holder: '',
    is_default: 0
}
const formData: Record<string, any> = reactive({ ...initialFormData })



// 账户类型
const accountTypes = [
    { 
        name: '银行卡', 
        value: 'bank',
        icon: '/static/icon-bank.png'
    },
    { 
        name: '支付宝', 
        value: 'alipay',
        icon: '/static/icon-alipay.png'
    },
    { 
        name: '微信', 
        value: 'wechat',
        icon: '/static/icon-wechat.png'
    }
];



// 表单验证
const isValid = computed(() => {
    // 基本验证
    if (!formData.account_name || !formData.account_holder || !formData.account_number) {
        return false;
    }
    
    // 根据账户类型验证账号格式
    if (formData.account_type === 'bank') {
        // 简单验证银行卡号是否为数字且长度合理
        return /^\d{10,19}$/.test(formData.account_number);
    }
    
    return true;
});

// 处理默认账户开关
const handleDefaultChange = (e: any) => {
    formData.is_default = e.detail.value ? 1 : 0;
};

// 设置弹窗数据
const setDialogData = async (row: any = null) => {
    loading.value = true
    popTitle.value = '添加提现账户'
    if (row) {
        const data = await (await getUserWithdrawalAccountInfo(row.id)).data
        Object.keys(formData).forEach((key: string) => {
            if (data[key] != undefined) formData[key] = data[key]
        })
        popTitle.value = '编辑提现账户'
    }
    loading.value = false
}

// 打开弹窗
const openDialog = () => {
    popup.value?.open('bottom');
};

// 关闭弹窗
const closeDialog = () => {
    popup.value?.close();
};


// 保存账户信息
const saveAccount = async () => {
    if (!isValid.value) {
        uni.showToast({
            title: '请完善账户信息',
            icon: 'none'
        });
        return;
    }
    
    try {
        let result;
        
        if (formData.id == 0) {
            // 添加账户
            result = await createUserWithdrawalAccount(formData);
        } else {
            // 更新账户
            result = await updateUserWithdrawalAccount(formData);
        }
        
        if (result.code === 10000) {
            uni.showToast({
                title: '操作成功',
                icon: 'success'
            });
            
            closeDialog();
            emit('success');
        } else {
            uni.showToast({
                title: result.message || '操作失败',
                icon: 'none'
            });
        }
    } catch (error) {
        console.error('保存账户信息失败:', error);
        uni.showToast({
            title: '操作失败，请重试',
            icon: 'none'
        });
    }
};

// 暴露方法给父组件
defineExpose({
    openDialog,
    closeDialog,
    setDialogData
});
</script>

<style scoped lang="scss">
.account-edit-container {

    max-height: 90vh;
    border-radius: 24rpx 24rpx 0 0;
    background-color: #fff;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30rpx;
    border-bottom: 1rpx solid #f5f5f5;
    
    .popup-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
    }
    
    .popup-close {
        padding: 10rpx;
    }
}

.account-form {
    flex: 1;
    padding: 30rpx;
    overflow-y: auto;
    
    .form-item {
        margin-bottom: 30rpx;
        
        .item-label {
            font-size: 28rpx;
            color: #666;
            margin-bottom: 16rpx;
            display: block;
        }
        
        .item-input {
            height: 90rpx;
            border: 1rpx solid #eee;
            border-radius: 8rpx;
            padding: 0 20rpx;
            font-size: 28rpx;
            background: #f9f9f9;
        }
        
        &.switch-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            
            .item-label {
                margin-bottom: 0;
            }
        }
    }
    
    .account-types {
        display: flex;
        margin: 0 -10rpx;
        
        .type-item {
            flex: 1;
            margin: 0 10rpx;
            border: 1rpx solid #eee;
            border-radius: 8rpx;
            padding: 20rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            
            &.active {
                border-color: #FF4444;
                background: rgba(255, 68, 68, 0.05);
                
                .type-name {
                    color: #FF4444;
                    font-weight: 600;
                }
            }
            
            .type-name {
                font-size: 28rpx;
                color: #666;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    }
}

.form-actions {
    padding: 30rpx;
    border-top: 1rpx solid #f5f5f5;
    
    .save-button {
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
