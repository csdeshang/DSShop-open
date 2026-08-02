<template>
    <view class="rider-apply" v-if="userInfo.rider.length == 0 || showReapply">
        <view class="rider-apply-header">
            <text class="title">{{ showReapply ? '重新申请骑手' : '申请成为骑手' }}</text>
            <text class="sub-title">{{ showReapply ? '请根据审核意见修改申请信息' : '加入我们的配送团队，灵活工作，稳定收入' }}</text>
        </view>

        <view class="rider-apply-form">
            <view class="ds-field-line">
                <text class="field-name">姓名</text>
                <input class="field-input" v-model="formData.name" placeholder="请输入真实姓名" />
            </view>

            <view class="ds-field-line">
                <text class="field-name">手机号码</text>
                <input class="field-input" type="number" v-model="formData.mobile" placeholder="请输入手机号码"
                    maxlength="11" />
            </view>

            <view class="ds-field-line-textarea">
                <text class="field-name">申请说明</text>
                <textarea class="field-textarea" v-model="formData.apply_remark" placeholder="请简要描述您的工作经历和可工作时间"
                    maxlength="200" />
            </view>

            <view class="ml-20 mt-20 mr-20">
                <button @tap="submitForm" type="button" class="ds-btn-lg ds-btn-blue mt-30">
                    {{ showReapply ? '重新提交申请' : '提交申请' }}
                </button>
                
                <!-- 取消重新申请按钮 -->
                <button v-if="showReapply" @tap="cancelReapply" type="button" class="ds-btn-lg ds-btn-white mt-20">
                    取消
                </button>
            </view>
        </view>

        <view class="apply-tips">
            <view class="tips-title">申请须知：</view>
            <view class="tips-item">1. 请确保提供的信息真实有效</view>
            <view class="tips-item">2. 提交申请后，我们将在1-3个工作日内审核</view>
            <view class="tips-item">3. 审核结果将通过短信通知您</view>
        </view>
    </view>

    <view class="rider-apply-result" v-if="userInfo.rider && userInfo.rider.apply_status === 0">
        <view class="rider-apply-result-header">
            <view class="status-icon-container pending">
                <uni-icons type="refresh" size="60" class="status-icon"></uni-icons>
            </view>
            <text class="title">申请审核中</text>
            <text class="sub-title">感谢您的申请，我们将尽快审核</text>

            <view class="result-actions">
                <view class="ds-btn-md ds-btn-white" @click="goBack">
                    返回首页
                </view>
            </view>
        </view>
    </view>

    <view class="rider-apply-result" v-if="userInfo.rider && userInfo.rider.apply_status === 1">
        <view class="rider-apply-result-header">
            <view class="status-icon-container success">
                <uni-icons type="checkmarkempty" size="60" class="status-icon"></uni-icons>
            </view>
            <text class="title">申请已通过</text>
            <text class="sub-title">您的申请已通过审核，请等待系统分配订单</text>

            <view class="result-actions">
                <view class="ds-btn-md ds-btn-blue" @click="goToPage({url:'/rider/pages/index/index'})">
                    骑手中心
                </view>
            </view>
        </view>
    </view>

    <view class="rider-apply-result" v-if="userInfo.rider && userInfo.rider.apply_status === 2 && !showReapply">
        <view class="rider-apply-result-header">
            <view class="status-icon-container rejected">
                <uni-icons type="closeempty" size="60" class="status-icon"></uni-icons>
            </view>
            <text class="title">申请未通过</text>
            <text class="sub-title">您的申请未通过审核，请根据审核意见进行修改后重新提交</text>

            <view class="result-actions">
                <view class="ds-btn-md ds-btn-white" @click="goBack">
                    返回首页
                </view>
                <view class="ds-btn-md ds-btn-blue" @click="reapply">
                    重新申请
                </view>
            </view>
        </view>
    </view>

</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { userApplyRider } from '@/shared/api/apply'
import { goToPage } from '@/utils/util'

import useUserStore from '@/stores/user'

import { storeToRefs } from 'pinia'


const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

// 控制重新申请表单显示
const showReapply = ref(false)

// 初始表单数据
const initialFormData = {
    name: '',
    mobile: '',
    apply_remark: '',
}

// 表单数据
const formData: Record<string, any> = reactive({ ...initialFormData })

// 重新申请
const reapply = () => {
    // 预填充用户之前的申请信息
    if (userInfo.value.rider) {
        const data = userInfo.value.rider
        
        // 使用循环方式赋值
        Object.keys(formData).forEach((key: string) => {
            if (data[key] !== undefined) {
                formData[key] = data[key]
            }
        })
    }

    // 显示重新申请表单
    showReapply.value = true
}

// 取消重新申请
const cancelReapply = () => {
    showReapply.value = false
    
    // 重置表单数据
    Object.keys(formData).forEach((key: string) => {
        formData[key] = initialFormData[key as keyof typeof initialFormData]
    })
}

// 返回上一页
const goBack = () => {
    uni.navigateBack();
};

const submitForm = async () => {
    // 表单验证
    if (!formData.name) {
        uni.showToast({
            title: '请输入姓名',
            icon: 'none'
        });
        return;
    }

    if (!formData.mobile) {
        uni.showToast({
            title: '请输入手机号码',
            icon: 'none'
        });
        return;
    }

    if (!/^1\d{10}$/.test(formData.mobile)) {
        uni.showToast({
            title: '请输入正确的手机号码',
            icon: 'none'
        });
        return;
    }

    if (!formData.apply_remark) {
        uni.showToast({
            title: '请输入申请说明',
            icon: 'none'
        });
        return;
    }

    // 显示加载提示
    uni.showLoading({
        title: '正在提交...',
    });

    try {
        // 调用申请API
        const response = await userApplyRider(formData);

        // 关闭加载提示
        uni.hideLoading();

        // 检查响应
        if (response.code == 10000) {
            uni.showToast({
                title: showReapply.value ? '重新申请提交成功' : '申请提交成功',
                icon: 'success'
            });

            // 重置重新申请状态
            showReapply.value = false

            // 更新用户信息，获取最新状态
			await userStore.getUserInfoWithRoles(true);
        } else {
            uni.showToast({
                title: response.message || '申请提交失败',
                icon: 'none'
            });
        }
    } catch (error) {
        // 关闭加载提示
        uni.hideLoading();

        uni.showToast({
            title: '网络异常，请稍后重试',
            icon: 'none'
        });
        console.error('提交申请失败:', error);
    }
};

onShow(() => {
    userStore.getUserInfoWithRoles(true)
})
</script>

<style lang="scss" scoped>
.rider-apply,
.rider-apply-result {
    padding: 30rpx;
    background-color: #f8f8f8;
    min-height: 100vh;

    .rider-apply-header,
    .rider-apply-result-header {
        text-align: center;
        padding: 40rpx 0 60rpx;

        .title {
            display: block;
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
            margin-bottom: 20rpx;
        }

        .sub-title {
            font-size: 26rpx;
            color: #666;
            line-height: 1.5;
        }
    }

    .rider-apply-form {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 20rpx 30rpx;
        margin-bottom: 30rpx;
        box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
    }

    .apply-tips {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
        margin-bottom: 30rpx;

        .tips-title {
            font-size: 28rpx;
            font-weight: 600;
            color: #333;
            margin-bottom: 20rpx;
            position: relative;
            padding-left: 16rpx;

            &::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 4rpx;
                height: 20rpx;
                background-color: #3d75ff;
                border-radius: 2rpx;
            }
        }

        .tips-item {
            font-size: 24rpx;
            color: #666;
            line-height: 1.6;
            margin-bottom: 12rpx;
            padding-left: 10rpx;
        }
    }
}

// 申请结果样式简化
.rider-apply-result {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    
    .rider-apply-result-header {
        width: 90%;
        padding: 40rpx 30rpx;
        background-color: #fff;
        border-radius: 16rpx;
        box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.04);
        text-align: center;
    }

    .status-icon-container {
        width: 100rpx;
        height: 100rpx;
        border-radius: 50rpx;
        margin: 0 auto 30rpx;
        display: flex;
        align-items: center;
        justify-content: center;

        &.pending {
            background-color: #f0f7ff;

            .status-icon {
                color: #3d75ff;
            }
        }

        &.success {
            background-color: #f0fff0;

            .status-icon {
                color: #4caf50;
            }
        }

        &.rejected {
            background-color: #fff0f0;

            .status-icon {
                color: #ff4d4f;
            }
        }
    }

    .result-actions {
        margin-top: 30rpx;
        display: flex;
        justify-content: center;
        width: 100%;
        gap: 20rpx;
    }
}
</style>
