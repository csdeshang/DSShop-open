<template>
    <view class="profile-container">
        <uni-nav-bar title="个人资料" left-icon="back" @clickLeft="goBack" :border="false"></uni-nav-bar>

        <view class="ds-field-line">
            <text class="field-name">姓名</text>
            <input class="field-input" v-model="formData.name" placeholder="请输入姓名" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">手机号</text>
            <input class="field-input" v-model="formData.mobile" placeholder="请输入手机号" type="number" />
        </view>

        <view class="ml-20 mt-20 mr-20">
            <button @tap="handleUpdate" class="ds-btn-lg ds-btn-blue">提交</button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import useUserStore from '@/stores/user'
import { updateRiderInfo, getRiderInfo } from '@/rider/api/rider'

const userStore = useUserStore()

// 表单数据
const initialFormData = {
    name: '',
    mobile: ''
}

const formData = reactive({ ...initialFormData })

const handleUpdate = async () => {
    // 表单验证
    if (!formData.name) {
        uni.showToast({
            title: '请输入姓名',
            icon: 'none'
        })
        return
    }

    if (!formData.mobile) {
        uni.showToast({
            title: '请输入手机号',
            icon: 'none'
        })
        return
    }

    // 手机号格式验证
    const mobileReg = /^1[3-9]\d{9}$/
    if (!mobileReg.test(formData.mobile)) {
        uni.showToast({
            title: '请输入正确的手机号',
            icon: 'none'
        })
        return
    }

    // 显示加载提示
    uni.showLoading({
        title: '正在提交...'
    })

    try {
        // 调用更新骑手信息的 API
        const response = await updateRiderInfo(formData)

        // 检查响应
        if (response.code === 10000) {
            uni.showToast({
                title: '更新成功',
                icon: 'success'
            })
            // 刷新用户信息
            userStore.getUserInfoWithRoles(true)
        } else {
            uni.showToast({
                title: response.message || '更新失败',
                icon: 'none'
            })
        }
    } catch (error) {
        uni.showToast({
            title: '网络错误，请重试',
            icon: 'none'
        })
    } finally {
        uni.hideLoading()
    }
}

const fetchRiderInfo = async () => {
    try {
        const res = await getRiderInfo()
        if (res.code === 10000) {
            const data = res.data
            formData.name = data.name || ''
            formData.mobile = data.mobile || ''
        } else {
            uni.showToast({
                title: res.message || '获取信息失败',
                icon: 'none'
            })
        }
    } catch (error) {
        uni.showToast({
            title: '获取信息失败',
            icon: 'none'
        })
    }
}

const goBack = () => {
    uni.navigateBack()
}

// 页面显示时获取骑手信息
onShow(() => {
    fetchRiderInfo()
})
</script>

<style lang="scss" scoped>
.profile-container {
    background-color: #fff;
    min-height: 100vh;
}
</style>