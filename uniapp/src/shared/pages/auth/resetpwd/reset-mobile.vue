<template>
    <view>

        <view class="ds-field-line">
            <input class="field-input" v-model="formData.mobile" placeholder="请输入手机号" type="text" maxlength="11" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="text" v-model="formData.code" placeholder="请输入验证码" />
            <view class="send-msg" :class="{ disabled: !countdown.canSend }" @click="handleSendVerifyCode">
                {{ countdown.text.value }}
            </view>
        </view>

        
        <view class="ds-field-line">
            <input class="field-input" type="password" v-model="formData.password" placeholder="请输入密码" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="password" v-model="formData.confirm_password" placeholder="请确认密码" />
        </view>




        <button size="default" type="warn" :loading="loading" @click="handleSubmit"
            class="ds-btn-lg mt-20 mb-20">注册</button>


    </view>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue';
import useUserStore from '@/stores/user';
import { resetPasswordMobile } from '@/shared/api/login/reset';

import { sendSms } from '@/api/system/sysSms';


import { useCountdown } from '@/hooks/useCountdown';

import { navigateToAuth } from '@/utils/util'

const userStore = useUserStore();
const loading = ref(false);

// 使用优化后的倒计时hook
const countdown = useCountdown({
    initialTime: 60,
    endText: '发送验证码',
    runningFormat: '{time}秒后重发'
});



const formData = reactive({
    password: '',
    confirm_password: '',
    mobile: '',
    code: '',
});

// 处理发送验证码
const handleSendVerifyCode = async () => {
    if (!formData.mobile) {
        uni.showToast({
            title: '请输入手机号',
            icon: 'none'
        });
        return;
    }

    // 使用优化后的sendCode方法
    const result = await countdown.sendCode(async () => {
        // 这里调用API
        return await sendSms({ mobile: formData.mobile, key: 'user_reset_password' });
    });

    if (!result.success) {
        uni.showToast({
            title: result.message,
            icon: 'none'
        });
    } else {
        uni.showToast({
            title: '验证码已发送',
            icon: 'success'
        });
    }
};

const handleSubmit = async () => {
    loading.value = true;

    const res = await resetPasswordMobile(formData);

    if (res.code == 10000) {

        uni.showToast({
            title: res.message,
            icon: 'none'
        })
        //跳转登录页
        navigateToAuth('login')
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }

    loading.value = false

};


</script>

<style scoped lang='scss'>
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