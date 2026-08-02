<template>
    <view>



        <uni-nav-bar title="设置" left-icon="back" @clickLeft="goToPage({ url: '/home/pages/user/index/index' })"
            :border="false"></uni-nav-bar>


        <uni-list>
            <uni-list-item class="pt-20 pb-20" :title="userInfo.nickname" link to="/home/pages/user/setting/profile"
                :note="'账户名：' + userInfo.username" :thumb="formatImageUrl(userInfo.avatar, ThumbnailPresets.small, 'avatar')"
                thumb-size="lg"></uni-list-item>
            <uni-list-item title="我的收货地址" link to="/home/pages/user/address/index"></uni-list-item>
            <uni-list-item title="微信绑定" link v-if="scene === 'wechat_mini' || scene === 'wechat_official'"
                @click="handleWechatBind" :rightText="wechatBindStatus ? '已绑定' : '未绑定'"></uni-list-item>
            <uni-list-item title="手机绑定" link to="/home/pages/user/setting/bind_mobile"
                :rightText="userInfo.mobile ? '已绑定' : '未绑定'"></uni-list-item>
            <uni-list-item title="修改密码" link to="/home/pages/user/setting/password"></uni-list-item>
            <uni-list-item title="实名认证" link to="/home/pages/user/setting/idcard"
                :rightText="idcardStatusText"></uni-list-item>
        </uni-list>


        <uni-list class="mt-10">
            <uni-list-item title="用户协议" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'user_service' } })"></uni-list-item>
            <uni-list-item title="隐私政策" link
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'user_privacy' } })"></uni-list-item>
        </uni-list>

        <view class="pt-10 pb-10 ml-10 mr-10">
            <button size="default" type="warn" @click="logout"
                class="ds-btn-lg mt-10 mb-10">退出登录</button>
        </view>
    </view>

</template>





<script setup lang="ts">

import useUserStore from '@/stores/user'
import { onLoad, onShow } from '@dcloudio/uni-app';
import { computed, ref } from 'vue'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getScene } from '@/utils/util';
import { getUserBindWechatStatus, unbindUserWechat } from '@/home/api/user/userBindWechat';
import wechatAuthUtil from '@/utils/wechatAuthUtil';

const scene = getScene();

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)

const idcardStatusText = computed(() => {
  const status = (userInfo.value as any)?.idcard_status
  if (status == null) return ''
  if (status === 0) return '未认证'
  if (status === 1) return '审核中'
  if (status === 2) return '未通过'
  if (status === 3) return '已认证'
  return ''
})



// 获取当前用户的微信绑定状态
const wechatBindStatus = ref(false)
const getWechatBindStatus = async () => {
    const res = await getUserBindWechatStatus()
    wechatBindStatus.value = res.data
}



const handleWechatBind = async () => {
    if (wechatBindStatus.value) {
        // 弹出确认框询问用户是否解绑
        uni.showModal({
            title: '解绑提示',
            content: '确定解绑微信吗？',
            success: async (res) => {
                if (res.confirm) {
                    const res = await unbindUserWechat()
                    if (res.code === 10000) {
                        wechatBindStatus.value = false
                        uni.showToast({
                            title: '解绑成功',
                            icon: 'success'
                        });
                    } else {
                        uni.showToast({
                            title: '解绑失败',
                            icon: 'none'
                        });
                    }
                } else {
                    uni.showToast({
                        title: '您取消了解绑',
                        icon: 'none'
                    });
                }
            }
        })


    } else {

        // 弹出确认框询问用户是否绑定微信
        uni.showModal({
            title: '绑定提示',
            content: '确定绑定微信吗？',
            success: (res) => {
                if (res.confirm) {
                    let redirect_url = ''
                    if (scene === 'wechat_official') {
                        // 清理URL参数
                        const url = new URL(window.location.href)
                        // 清除微信授权相关参数
                        url.searchParams.delete('code')
                        url.searchParams.delete('state')
                        redirect_url = url.toString()
                    }
                    // 用户同意，使用无感授权
                    wechatAuthUtil.authorize({
                        redirect_url: redirect_url,
                        scene: scene,
                        mode: 'bind',
                    });

                } else {
                    uni.showToast({
                        title: '您取消了绑定',
                        icon: 'none'
                    });
                }
            }
        });
    }
}

// 退出登录
const logout = () => {
    uni.showModal({
        title: '退出登录',
        content: '确定要退出登录吗？',
        success: (res) => {
            if (res.confirm) {
                userStore.logout()
                goToPage({ url: '/home/pages/user/index/index', type: 'reLaunch' })
            }
        }
    })
}

onShow(() => {
    userStore.getUserInfoWithRoles(true)
    getWechatBindStatus()

    // 获取当前用户绑定的微信信息


})

// 处理页面参数
onLoad(async (options: any) => {
    // 处理从微信授权页面返回的情况
    if (options.code) {
        uni.showLoading({
            title: '绑定中...',
        });
        // 处理微信授权回调（只针对微信授权页面）
        const res = await wechatAuthUtil.handleOfficialCallback({
            code: options.code,
            mode: 'bind',
        })
        uni.hideLoading();
        if (res.code == 10000) {
            wechatBindStatus.value = true
        }
    }
});

</script>