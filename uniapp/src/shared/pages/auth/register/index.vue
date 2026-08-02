<template>
    <view class="register-container">
        <view class="logo">
            <image :src="websiteConfig?.logo || ''" mode="widthFix" class="logo-image"></image>
        </view>

        <view class="register-title">
            <text>{{ websiteConfig?.name || '欢迎注册' }}</text>
        </view>

        <view class="tab-container">
            <view class="tab-item" :class="{ active: registerType === 'normal' }" @click="registerType = 'normal'">
                <text>账号密码注册</text>
                <view class="tab-line" v-if="registerType === 'normal'"></view>
            </view>
            <view class="tab-item" :class="{ active: registerType === 'mobile' }" @click="registerType = 'mobile'">
                <text>手机验证码注册</text>
                <view class="tab-line" v-if="registerType === 'mobile'"></view>
            </view>
        </view>

        <register-mobile v-if="registerType === 'mobile'" :isagree="isagree" />
        <register-normal v-else :isagree="isagree" />

        <view class="action-links">
            <view class="reset-pwd" @click="navigateToAuth('reset')">
                找回密码
            </view>

            <view class="register" @click="navigateToAuth('login')">
                已经有账号？去登录
            </view>
        </view>

        <view class="agreement mt-10">
            <checkbox @click="selectAgreement" :checked="isagree" style="transform:scale(0.7)" />
            <text @click="selectAgreement">已阅读并同意</text>
            <text class="link"
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'user_service' } })">《用户协议》</text>
            <text>与</text>
            <text class="link"
                @click="goToPage({ url: '/shared/pages/system/agreement', params: { code: 'user_privacy' } })">《隐私政策》</text>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import RegisterMobile from './register-mobile.vue';
import RegisterNormal from './register-normal.vue';
import { goToPage, navigateToAuth } from '@/utils/util';
import { useConfigStore } from '@/stores/modules/config';

// 注册类型：normal=账号密码注册，mobile=手机验证码注册
const registerType = ref('normal');

// 网站配置
const configStore = useConfigStore();

// 获取网站配置
const websiteConfig = ref<Record<string, string>>({});
async function fetchWebsiteConfig() {
    websiteConfig.value = await configStore.fetchConfigsByType('website');
}

// 获取登录注册配置
const loginConfig = ref<Record<string, string>>({});
async function fetchLoginConfig() {
    loginConfig.value = await configStore.fetchConfigsByType('user_login');
}


// 协议同意状态
const isagree = ref(false);
// 切换协议同意状态
const selectAgreement = () => {
    isagree.value = !isagree.value;
};


// 页面加载时获取配置
onMounted(() => {
    fetchWebsiteConfig();
    fetchLoginConfig();
});
</script>

<style scoped lang='scss'>
.register-container {
    min-height: 100vh;
    padding: 40rpx;
    background-color: #fff;
    display: flex;
    flex-direction: column;
}

.logo {
    display: flex;
    justify-content: center;
    padding: 60rpx 0;

    .logo-image {
        width: 180rpx;
        height: auto;
    }
}

.register-title {
    text-align: center;
    margin-bottom: 60rpx;

    text {
        font-size: 40rpx;
        font-weight: bold;
        color: #333;
    }
}

.tab-container {
    display: flex;
    justify-content: center;
    margin-bottom: 50rpx;

    .tab-item {
        position: relative;
        padding: 0 30rpx;
        margin: 0 20rpx;
        font-size: 32rpx;
        color: #666;
        height: 80rpx;
        line-height: 80rpx;

        &.active {
            color: $ds-primary-color;
            font-weight: bold;
        }

        .tab-line {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60rpx;
            height: 4rpx;
            background-color: $ds-primary-color;
            border-radius: 2rpx;
        }
    }
}

.action-links {
    display: flex;
    justify-content: space-between;
    margin: 40rpx 0;
    font-size: 28rpx;

    .reset-pwd {
        color: #666;
    }

    .register {
        color: $ds-primary-color;
    }
}

.agreement {
    padding: 30rpx 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    color: $ds-text-secondary;
    font-size: $ds-font-md;

    .link {
        color: $ds-primary-color;
    }
}
</style>