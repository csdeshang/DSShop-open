<template>
    <view>
        <view class="ds-field-line">
            <input class="field-input" v-model="formData.username" placeholder="用户名/邮箱/手机号" type="text"
                maxlength="25" />
        </view>

        <view class="ds-field-line">
            <input class="field-input" type="password" v-model="formData.password" placeholder="请输入密码" />
        </view>

        <button size="default" type="warn" :loading="loading" @click="handleSubmit"
            class="ds-btn-lg mt-20 mb-20">登录</button>


    </view>


</template>

<script lang="ts" setup>
import { reactive, ref, watch } from 'vue';
import useUserStore from '@/stores/user'





import { loginNormal } from '@/shared/api/login/login'
import { onLoad } from '@dcloudio/uni-app';


const userStore = useUserStore()

const loading = ref(false);

const props = defineProps({
    isagree: {
        type: Boolean,
        required: true
    }
});


const formData = reactive({
    username: '',
    password: '',
    login_type: 'user',
    isagree: props.isagree
})


const handleSubmit = async () => {

    if (!formData.isagree) {
        uni.showToast({
            title: '请同意用户协议',
            icon: 'none'
        });
        return;
    }

    loading.value = true

    const res = await loginNormal(formData);

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

}

watch(() => props.isagree, (newVal) => {
    formData.isagree = newVal
})

// 解决默认导出问题
defineOptions({
    name: 'LoginNormal'
})

const redirectUrl = ref('/home/pages/index')
onLoad((options: any) => {
    if (options.redirect) {
        redirectUrl.value = decodeURIComponent(options.redirect);
    }
})

</script>

<style scoped lang='scss'>
/* login-mobile 组件的样式 */
</style>
