<template>
    <view class="register-container">
        <view class="logo">
            <image :src="websiteConfig?.logo || ''" mode="widthFix" class="logo-image"></image>
        </view>

        <view class="register-title">
            <text>{{ websiteConfig?.name || '重置密码' }}</text>
        </view>


        <!--默认手机找回密码-->

        <!--
        <view class="tab-container">
     
            <view class="tab-item" :class="{ active: resetType === 'email' }" @click="resetType = 'email'">
                <text>邮箱找回</text>
                <view class="tab-line" v-if="resetType === 'email'"></view>
            </view>
            <view class="tab-item" :class="{ active: resetType === 'mobile' }" @click="resetType = 'mobile'">
                <text>手机验证码找回</text>
                <view class="tab-line" v-if="resetType === 'mobile'"></view>
            </view>
        </view>
    -->

        <reset-mobile v-if="resetType === 'mobile'"  />
        <reset-email v-if="resetType === 'email'" />

        <view class="action-links">
            <view class="reset-pwd" @click="navigateToAuth('reset')">
                找回密码
            </view>

            <view class="register" @click="navigateToAuth('login')">
                已经有账号？去登录
            </view>
        </view>

    </view>
</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import ResetMobile from './reset-mobile.vue';
import ResetEmail from './reset-email.vue';
import { navigateToAuth } from '@/utils/util';
import { useConfigStore } from '@/stores/modules/config';

// 找回密码类型：mobile=手机验证码找回，email=邮箱找回
const resetType = ref('mobile');

// 网站配置
const configStore = useConfigStore();
const websiteConfig = ref<Record<string, string>>({});


// 获取网站配置
async function fetchWebsiteConfig() {
    websiteConfig.value = await configStore.fetchConfigsByType('website');
}




// 页面加载时获取配置
onMounted(() => {
    fetchWebsiteConfig();
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


</style>