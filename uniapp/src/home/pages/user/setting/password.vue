<template>
    <!-- 修改密码页面 -->
    <view class="password-page">

        <!-- 温馨提示卡片 -->
        <view class="tips-card">
            <view class="tips-icon">
                <text class="icon">💡</text>
            </view>
            <view class="tips-content">
                <text class="tips-text">新密码长度必须在6位以上，修改成功后将自动退出登录，请重新登录</text>
            </view>
        </view>

        <!-- 修改密码表单区域 -->
        <view class="ds-card">
            <!-- 原密码输入框 -->
            <view class="ds-field-line">
                <text class="field-name">原密码</text>
                <input class="field-input" v-model="formData.old_password" type="password" placeholder="请输入原密码"
                    @input="clearFormErrors" />
            </view>

            <!-- 新密码输入框 -->
            <view class="ds-field-line">
                <text class="field-name">新密码</text>
                <input class="field-input" v-model="formData.password" type="password" placeholder="请输入新密码（6位以上）"
                    @input="clearFormErrors" />
            </view>

            <!-- 确认密码输入框 -->
            <view class="ds-field-line">
                <text class="field-name">确认密码</text>
                <input class="field-input" v-model="formData.confirm_password" type="password" placeholder="请再次输入新密码"
                    @input="clearFormErrors" />
            </view>

            <!-- 表单验证错误提示 -->
            <view class="form-errors" v-if="formErrors.length > 0">
                <text class="error-text" v-for="error in formErrors" :key="error">{{ error }}</text>
            </view>
        </view>

        <!-- 提交按钮区域 -->
        <view class="p-30">
            <button class="ds-btn-lg ds-btn-orange" :loading="loading" @click="handleSubmit">
                确认修改
            </button>
        </view>
    </view>
</template>

<script lang="ts" setup>
// 1. 导入 Vue 相关
import { ref, reactive } from 'vue'

// 2. 导入业务API
// @ts-ignore
import { editUserPassword } from '@/home/api/user/user'

// 3. 导入Store
// @ts-ignore
import useUserStore from '@/stores/user'

// 4. 类型定义
interface FormData {
    old_password: string
    password: string
    confirm_password: string
}

// 5. 响应式数据
const loading = ref(false)
const formErrors = ref<string[]>([])
const formData = reactive<FormData>({
    old_password: '',
    password: '',
    confirm_password: ''
})

// 6. Store实例
const userStore = useUserStore()

// 7. 方法定义
/**
 * 表单验证方法
 * @returns {boolean} 验证是否通过
 */
const validateForm = (): boolean => {
    const errors: string[] = []

    // 验证原密码
    if (!formData.old_password) {
        errors.push('请输入原密码')
    }

    // 验证新密码
    if (!formData.password) {
        errors.push('请输入新密码')
    } else if (formData.password.length < 6) {
        errors.push('新密码长度不能少于6位')
    } else if (formData.password.length > 32) {
        errors.push('新密码长度不能超过32位')
    }

    // 验证确认密码
    if (!formData.confirm_password) {
        errors.push('请确认新密码')
    } else if (formData.confirm_password !== formData.password) {
        errors.push('两次输入的密码不一致')
    }

    formErrors.value = errors
    return errors.length === 0
}

/**
 * 处理提交表单
 */
const handleSubmit = async (): Promise<void> => {
    if (loading.value) return

    try {
        // 表单验证
        if (!validateForm()) {
            return
        }

        loading.value = true

        // 调用修改密码API
        const result = await editUserPassword({
            old_password: formData.old_password,
            password: formData.password,
            confirm_password: formData.confirm_password
        })

        if (result.code === 10000) {
            // 成功提示
            uni.showToast({
                title: '密码修改成功',
                icon: 'success'
            })

            // 延迟后退出登录
            setTimeout(() => {
                showLogoutModal()
            }, 1500)
        } else {
            uni.showToast({
                title: result.message || '修改密码失败',
                icon: 'none'
            })
        }

    } catch (error: any) {
        console.error('修改密码失败:', error)
        uni.showToast({
            title: error.message || '网络错误，请重试',
            icon: 'none'
        })
    } finally {
        loading.value = false
    }
}

/**
 * 显示退出登录确认框
 */
const showLogoutModal = (): void => {
    uni.showModal({
        title: '提示',
        content: '密码修改成功，请重新登录',
        showCancel: false,
        confirmText: '确定',
        success: () => {
            // 退出登录
            userStore.logout()
            // 返回登录页面
            uni.reLaunch({
                url: '/shared/pages/auth/login/index'
            })
        }
    })
}

/**
 * 清除表单错误
 */
const clearFormErrors = (): void => {
    formErrors.value = []
}

// 8. defineExpose（如需要）
defineExpose({
    clearFormErrors
})
</script>

<style lang="scss" scoped>
// 页面基础样式
.password-page {
    background-color: #f5f5f5;
    min-height: 100vh;
}



// 温馨提示卡片
.tips-card {
    background-color: #fff3cd;
    border: 1rpx solid #ffeaa7;
    border-radius: 12rpx;
    padding: 24rpx;
    margin: 20rpx 20rpx;
    display: flex;
    align-items: flex-start;

    .tips-icon {
        margin-right: 16rpx;
        margin-top: 4rpx;

        .icon {
            font-size: 28rpx;
        }
    }

    .tips-content {
        flex: 1;

        .tips-text {
            font-size: 26rpx;
            color: #856404;
            line-height: 1.5;
        }
    }
}

// 表单错误提示
.form-errors {
    margin-top: 20rpx;

    .error-text {
        display: block;
        color: #f56c6c;
        font-size: 24rpx;
        line-height: 1.5;
        margin-bottom: 8rpx;

        &:last-child {
            margin-bottom: 0;
        }
    }
}
</style>