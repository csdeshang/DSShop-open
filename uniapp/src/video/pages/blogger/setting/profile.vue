<template>
    <view class="profile-container">
        <uni-nav-bar title="个人资料" left-icon="back" @clickLeft="goToPage({ url: '/video/pages/blogger/setting/index' })" :border="false"></uni-nav-bar>
        <!-- 头像上传 -->
        <view class="upload-image-container">
            <ds-attachment-picker v-model="formData.avatar" type="image" :limit="1" />
        </view>

        
        <!-- 博主昵称 -->
        <view class="ds-field-line">
            <text class="field-name">昵称</text>
            <input class="field-input" v-model="formData.blogger_name" placeholder="请输入博主昵称" maxlength="32" />
        </view>



        <!-- 描述 -->
        <view class="ds-field-line-textarea">
            <text class="field-name">个人简介</text>
            <textarea 
                class="field-textarea" 
                v-model="formData.description" 
                placeholder="请输入个人简介" 
                maxlength="500"
                show-confirm-bar
                auto-height
            />
        </view>

        <view class="ml-20 mt-20 mr-20">
            <button @tap="handleUpdate" class="ds-btn-lg ds-btn-orange">保存</button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import useUserStore from '@/stores/user'
import { updateBloggerInfo, getBloggerInfo } from '@/video/api/blogger/blogger'
import dsAttachmentPicker from '@/components/ds-attachment/picker.vue'
import { goToPage } from '@/utils/util'

const userStore = useUserStore()

// 表单数据
const initialFormData = {
    blogger_name: '',
    avatar: '',
    description: ''
}

const formData = reactive({ ...initialFormData })

const handleUpdate = async () => {
    // 表单验证
    if (!formData.blogger_name) {
        uni.showToast({
            title: '请输入姓名',
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
        const response = await updateBloggerInfo(formData)

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

const fetchBloggerInfo = async () => {
    try {
        const res = await getBloggerInfo()
        if (res.code === 10000) {
            const data = res.data
            formData.blogger_name = data.blogger_name || ''
            formData.avatar = data.avatar || ''
            formData.description = data.description || ''
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


// 页面显示时获取博主信息
onShow(() => {
    fetchBloggerInfo()
})
</script>

<style lang="scss" scoped>
.profile-container {
    background-color: #fff;
    min-height: 100vh;
}

.upload-image-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50rpx 20rpx;
}
</style>