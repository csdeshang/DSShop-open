<template>

    <view>
        <uni-nav-bar title="设置" left-icon="back" @clickLeft="goToPage({ url: '/technician/pages/index/index' })"
            :border="false"></uni-nav-bar>


        <uni-list>
            <uni-list-item class="pt-20 pb-20" :title="userInfo.technician.name" link
                to="/technician/pages/setting/profile" :note="'账户名：' + userInfo.username"
                :thumb="formatImageUrl(userInfo.technician.avatar, ThumbnailPresets.small, 'avatar')" thumb-size="lg"></uni-list-item>
            <uni-list-item title="我的轨迹" link to="/technician/pages/track/index"></uni-list-item>
        </uni-list>

        <uni-list class="mt-10">
            <uni-list-item title="师傅协议" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'technician_service' } })"></uni-list-item>
            <uni-list-item title="服务质量标准" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'technician_quality' } })"></uni-list-item>
            <uni-list-item title="联系我们" link to="/shared/pages/contact_us"></uni-list-item>
        </uni-list>

        <uni-list class="mt-10">
            <uni-list-item title="切换为普通用户" link
                @click="goToPage({ url: '/home/pages/user/index/index' , type: 'reLaunch' })"></uni-list-item>
        </uni-list>

        <view class="pt-10 pb-10 ml-10 mr-10">
            <button size="default" type="warn" @click="logout"
                class="ds-btn-lg mt-10 mb-10">退出登录</button>
        </view>
    </view>
</template>





<script setup lang="ts">

import useUserStore from '@/stores/user'
import { onShow } from '@dcloudio/uni-app';
import { computed, ref } from 'vue'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getScene } from '@/utils/util';

const scene = getScene();

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
                goToPage({ url: '/technician/pages/index/index', type: 'reLaunch' })
            }
        }
    })
}

onShow(() => {
    userStore.getUserInfoWithRoles()
})



</script>