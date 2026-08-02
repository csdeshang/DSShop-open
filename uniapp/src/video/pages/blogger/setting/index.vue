<template>
    <view>
        <uni-nav-bar title="设置" left-icon="back" @clickLeft="goToPage({ url: '/video/pages/user/index' })"
            :border="false"></uni-nav-bar>

        <uni-list>
            <uni-list-item class="pt-20 pb-20" :title="userInfo.blogger?.blogger_name || '未设置'" link to="/video/pages/blogger/setting/profile"
                :note="'账户名：' + userInfo.username" :thumb="formatImageUrl(userInfo.blogger?.avatar, ThumbnailPresets.small, 'avatar')" thumb-size="lg"></uni-list-item>
        </uni-list>

        <uni-list class="mt-10">
            <uni-list-item title="博主协议" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'blogger_service' } })"></uni-list-item>
            <uni-list-item title="内容创作规范" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'blogger_content' } })"></uni-list-item>
            <uni-list-item title="版权声明协议" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'blogger_copyright' } })"></uni-list-item>
        </uni-list>

        <view class="pt-10 pb-10 ml-10 mr-10">
            <button @click="logout" class="ds-btn-lg ds-btn-blue">退出登录</button>
        </view>
    </view>
</template>

<script setup lang="ts">
import useUserStore from '@/stores/user'
import { onShow } from '@dcloudio/uni-app'
import { computed } from 'vue'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)

// 退出登录
const logout = () => {
    uni.showModal({
        title: '退出登录',
        content: '确定要退出登录吗？',
        success: (res) => {
            if (res.confirm) {
                userStore.logout()
                goToPage({ url: '/video/pages/index', type: 'reLaunch' })
            }
        }
    })
}

onShow(() => {
    userStore.getUserInfoWithRoles()
})
</script>

<style scoped lang="scss">
// 如果需要自定义样式，可以在这里添加
</style>