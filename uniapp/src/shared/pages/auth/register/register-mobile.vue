<template>
    <view>


        <view class="ds-field-line">
            <input class="field-input" v-model="formData.username" placeholder="请输入用户名" type="text" maxlength="25" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="password" v-model="formData.password" placeholder="请输入密码" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="password" v-model="formData.confirm_password" placeholder="请确认密码" />
        </view>


        <view class="ds-field-line">
            <input class="field-input" v-model="formData.mobile" placeholder="请输入手机号" type="text" maxlength="11" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="text" v-model="formData.code" placeholder="请输入验证码" />
            <view class="send-msg" :class="{ disabled: !countdown.canSend }" @click="handleSendVerifyCode">
                {{ countdown.text.value }}
            </view>
        </view>


        <button size="default" type="warn" :loading="loading" @click="handleSubmit"
            class="ds-btn-lg mt-20 mb-20">注册</button>


    </view>
</template>

<script lang="ts" setup>
import { reactive, ref, watch } from 'vue';
import useUserStore from '@/stores/user';
import { registerMobile } from '@/shared/api/login/register';

import { sendSms } from '@/api/system/sysSms';


import { useCountdown } from '@/hooks/useCountdown';
import { onLoad } from '@dcloudio/uni-app';

const userStore = useUserStore();
const loading = ref(false);

// 使用优化后的倒计时hook
const countdown = useCountdown({
    initialTime: 60,
    endText: '发送验证码',
    runningFormat: '{time}秒后重发'
});



const props = defineProps({
    isagree: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits<{ agree: [] }>()

const formData = reactive({
    username: '',
    password: '',
    confirm_password: '',
    mobile: '',
    code: '',
    isagree: props.isagree,
    referrer_id: uni.getStorageSync("referrer_id")
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
        return await sendSms({ mobile: formData.mobile, key: 'user_register' });
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
    if (!formData.username?.trim()) {
        uni.showToast({ title: '请输入用户名', icon: 'none' })
        return
    }
    if (!formData.password) {
        uni.showToast({ title: '请输入密码', icon: 'none' })
        return
    }
    if (!formData.confirm_password) {
        uni.showToast({ title: '请确认密码', icon: 'none' })
        return
    }
    if (formData.password !== formData.confirm_password) {
        uni.showToast({ title: '两次密码不一致', icon: 'none' })
        return
    }
    if (!formData.mobile?.trim()) {
        uni.showToast({ title: '请输入手机号', icon: 'none' })
        return
    }
    if (!formData.code?.trim()) {
        uni.showToast({ title: '请输入验证码', icon: 'none' })
        return
    }

    if (!formData.isagree) {
        const { confirm } = await new Promise<UniApp.ShowModalRes>((resolve) => {
            uni.showModal({
                title: '提示',
                content: '请阅读并同意《用户协议》和《隐私政策》',
                confirmText: '同意',
                cancelText: '取消',
                success: resolve,
            })
        })
        if (!confirm) return
        emit('agree')
        formData.isagree = true
        return
    }

    loading.value = true;

    const res = await registerMobile(formData);

    if (res.code == 10000) {
        userStore.loginAfter(res.data);
        //跳转首页
        uni.reLaunch({
            url: redirectUrl.value
        })
    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        })
    }

    loading.value = false

};

watch(() => props.isagree, (newVal) => {
    formData.isagree = newVal
})

const redirectUrl = ref('/home/pages/index')
onLoad((options: any) => {
    if (options.redirect) {
        redirectUrl.value = decodeURIComponent(options.redirect);
    }
})

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