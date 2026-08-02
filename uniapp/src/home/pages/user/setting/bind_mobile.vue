<template>
    <view class="bind-mobile-container">
        <uni-nav-bar title="手机绑定" left-icon="back" @clickLeft="goToPage({ url: '/home/pages/user/setting/index' })"
            :border="false"></uni-nav-bar>

        <!-- 当前绑定状态 -->
        <view class="current-status ds-card">
            <view class="status-item">
                <text class="status-label">当前手机号：</text>
                <text class="status-value">{{ currentMobile || '未绑定' }}</text>
            </view>
        </view>

        <!-- 未绑定状态：显示绑定表单 -->
        <view class="form-container" v-if="!currentMobile">
            <text class="form-title">绑定手机号</text>

            <view class="ds-field-line">
                <input class="field-input" v-model="bindForm.mobile" placeholder="请输入手机号" type="text" maxlength="11" />
            </view>

            <view class="ds-field-line">
                <input class="field-input" type="text" v-model="bindForm.code" placeholder="请输入验证码" />
                <view class="send-msg" :class="{ disabled: !bindCountdown.canSend }" @click="handleSendBindCode">
                    {{ bindCountdown.text.value }}
                </view>
            </view>

            <button :loading="bindLoading" @click="handleBind"
                class="ds-btn-lg ds-btn-orange mt-20 mb-20">
                绑定手机号
            </button>
        </view>

        <!-- 已绑定状态：显示操作按钮 -->
        <view class="action-container" v-else>
            <button class="ds-btn-lg ds-btn-orange mb-20" @click="showChangeModal">
                更换手机号
            </button>
            <button class="ds-btn-lg ds-btn-white" @click="showUnbindForm">
                解绑手机号
            </button>
        </view>

        <!-- 解绑手机号表单 -->
        <view class="form-container" v-if="showUnbind">
            <text class="form-title">解绑手机号</text>
            <view class="warning-tip">
                <text>解绑后将无法使用手机号登录，确定要解绑吗？</text>
            </view>
            <view class="current-mobile-tip">
                <text>当前手机号：{{ currentMobile }}</text>
            </view>

            <view class="ds-field-line">
                <input class="field-input" type="text" v-model="unbindForm.code" placeholder="请输入验证码" />
                <view class="send-msg" :class="{ disabled: !unbindCountdown.canSend }" @click="handleSendUnbindCode">
                    {{ unbindCountdown.text.value }}
                </view>
            </view>

            <button :loading="unbindLoading" @click="handleUnbind"
                class="ds-btn-lg ds-btn-orange mt-20 mb-20">
                确认解绑
            </button>

            <button class="ds-btn-lg ds-btn-white mt-10" @click="cancelUnbind">
                取消
            </button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import useUserStore from '@/stores/user'
import { sendSms } from '@/api/system/sysSms'
import { bindMobile, unbindMobile } from '@/home/api/user/userBindMobile'
import { useCountdown } from '@/hooks/useCountdown'
import { goToPage } from '@/utils/util'

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)
const currentMobile = computed(() => userInfo.value.mobile || '')

// 表单状态
const showUnbind = ref(false)

// 加载状态
const bindLoading = ref(false)
const unbindLoading = ref(false)

// 绑定表单
const bindForm = reactive({
    mobile: '',
    code: ''
})

// 解绑表单
const unbindForm = reactive({
    code: ''
})

// 倒计时Hook
const bindCountdown = useCountdown({
    initialTime: 60,
    endText: '发送验证码',
    runningFormat: '{time}秒后重发'
})

const unbindCountdown = useCountdown({
    initialTime: 60,
    endText: '发送验证码',
    runningFormat: '{time}秒后重发'
})

// 发送验证码方法
const handleSendBindCode = async () => {
    if (!bindForm.mobile) {
        uni.showToast({
            title: '请输入手机号',
            icon: 'none'
        })
        return
    }

    const result = await bindCountdown.sendCode(async () => {
        return await sendSms({ mobile: bindForm.mobile, key: 'user_bind_mobile' })
    })

    if (!result.success) {
        uni.showToast({
            title: result.message,
            icon: 'none'
        })
    } else {
        uni.showToast({
            title: '验证码已发送',
            icon: 'success'
        })
    }
}

const handleSendUnbindCode = async () => {
    const result = await unbindCountdown.sendCode(async () => {
        return await sendSms({ mobile: currentMobile.value, key: 'user_unbind_mobile' })
    })

    if (!result.success) {
        uni.showToast({
            title: result.message,
            icon: 'none'
        })
    } else {
        uni.showToast({
            title: '验证码已发送',
            icon: 'success'
        })
    }
}

// 绑定手机号
const handleBind = async () => {
    if (!bindForm.mobile || !bindForm.code) {
        uni.showToast({
            title: '请填写完整信息',
            icon: 'none'
        })
        return
    }

    bindLoading.value = true

    try {
        const res = await bindMobile({
            mobile: bindForm.mobile,
            code: bindForm.code
        })

        if (res.code === 10000) {
            uni.showToast({
                title: '绑定成功',
                icon: 'success'
            })
            await userStore.getUserInfoWithRoles(true) // 刷新用户信息
            // 重置表单
            bindForm.mobile = ''
            bindForm.code = ''
        } else {
            uni.showToast({
                title: res.message || '绑定失败',
                icon: 'none'
            })
        }
    } catch (error: any) {
        uni.showToast({
            title: error.message || '绑定失败',
            icon: 'none'
        })
    } finally {
        bindLoading.value = false
    }
}

// 显示更换手机号提示
const showChangeModal = () => {
    uni.showModal({
        title: '更换手机号',
        content: '更换手机号需要先解绑当前手机号，然后重新绑定新手机号。是否继续？',
        confirmText: '继续',
        cancelText: '取消',
        success: (res) => {
            if (res.confirm) {
                showUnbindForm()
            }
        }
    })
}

// 解绑手机号相关
const showUnbindForm = () => {
    showUnbind.value = true
    unbindForm.code = ''
}

const handleUnbind = async () => {
    if (!unbindForm.code) {
        uni.showToast({
            title: '请输入验证码',
            icon: 'none'
        })
        return
    }

    unbindLoading.value = true

    try {
        const res = await unbindMobile({
            code: unbindForm.code
        })

        if (res.code === 10000) {
            uni.showToast({
                title: '解绑成功，现在可以绑定新手机号',
                icon: 'success'
            })
            await userStore.getUserInfoWithRoles(true)
            cancelUnbind()
        } else {
            uni.showToast({
                title: res.message || '解绑失败',
                icon: 'none'
            })
        }
    } catch (error: any) {
        uni.showToast({
            title: error.message || '解绑失败',
            icon: 'none'
        })
    } finally {
        unbindLoading.value = false
    }
}

const cancelUnbind = () => {
    showUnbind.value = false
    unbindForm.code = ''
}



onShow(() => {
    userStore.getUserInfoWithRoles(true)
})
</script>

<style lang="scss" scoped>
.bind-mobile-container {
    min-height: 100vh;
    background: #f8f8f8;
}

.current-status {
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;

        .status-label {
            font-size: 32rpx;
            color: #333;
        }

        .status-value {
            font-size: 32rpx;
            color: #666;
        }
    }
}

.form-container {
    padding: 40rpx 20rpx;
    margin: 20rpx;
    background: white;
    border-radius: 12rpx;

    .form-title {
        display: block;
        font-size: 36rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 30rpx;
    }

    .current-mobile-tip {
        padding: 20rpx 0;
        font-size: 28rpx;
        color: #666;
    }

    .warning-tip {
        padding: 20rpx 0;
        font-size: 28rpx;
        color: #f56c6c;
        margin-bottom: 20rpx;
    }
}

.action-container {
    padding: 40rpx 20rpx;

    button {
        width: 100%;
        margin-bottom: 20rpx;
    }
}

.send-msg {
    text-align: center;
    color: $ds-primary-color;
    min-width: 80px;
    height: 60rpx;
    line-height: 60rpx;
    padding: 0 14px;
    font-size: 28rpx;
    cursor: pointer;

    &.disabled {
        color: #999;
        opacity: 0.8;
        cursor: not-allowed;
    }
}
</style>
