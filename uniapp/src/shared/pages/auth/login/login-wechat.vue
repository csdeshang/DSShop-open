<template>
    <view class="wechat-login-container">
        <uni-icons type="weixin" size="30" color="green" @click="handleWechatLogin"
            v-if="scene === 'wechat_mini' || scene === 'wechat_official'"></uni-icons>
    </view>
</template>


<script setup lang="ts">
import { onLoad } from '@dcloudio/uni-app';
import wechatAuthUtil from '@/utils/wechatAuthUtil';
import { getScene } from '@/utils/util';
import { ref } from 'vue';

const scene = getScene();


// 处理微信登录
const handleWechatLogin = () => {

    // 默认跳转地址
    let redirect_url = '/home/pages/user/index/index'

    if (scene === 'wechat_official') {
        redirect_url = window.location.href
    }


    // 使用工具类进行微信授权登录
    wechatAuthUtil.authorize({
        mode: 'login',
        scene: scene,
        redirect_url: redirect_url
    });
};

// 处理页面参数
const redirectUrl = ref('/home/pages/index')
onLoad(async (options: any) => {

    // 获取跳转地址
    if (options.redirect) {
        redirectUrl.value = decodeURIComponent(options.redirect);
    }
    // 处理从微信授权页面返回的情况
    if (options.code) {

        uni.showLoading({
            title: '登录中...',
        });

        // 处理微信授权回调（只针对微信授权页面）
        const res =  await wechatAuthUtil.handleOfficialCallback({
            code: options.code,
            mode: 'login',
        })

        // 隐藏加载中
        uni.hideLoading();

        if (res.code == 10000) {
            // 跳转指定页面
            uni.reLaunch({
                url: redirectUrl.value
            });
        }

    }

});
</script>

<style scoped>
.wechat-login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}
</style>
