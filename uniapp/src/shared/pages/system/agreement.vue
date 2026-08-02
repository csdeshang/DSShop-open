<template>
    <view class="agreement-container">
        <!-- 内容区域 -->
        <view class="agreement-content">
            <template v-if="agreement.id">
                <view class="agreement-title">{{ agreement.title }}</view>
                <rich-text class="agreement-body" :nodes="formatRichText(agreement.content)"></rich-text>
            </template>

            <template v-else>
                <DsEmpty />
            </template>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { getAgreementByCode } from '@/api/system/sysAgreement';
import { formatRichText } from '@/utils/util';

import DsEmpty from '@/components/ds-empty/index.vue';


// 定义协议数据结构
interface Agreement {
    id?: number;
    code?: string;
    title?: string;
    content?: string;
    sort?: number;
    is_show?: number;
    create_at?: number;
    update_at?: number;
}

// 状态管理
const agreement = ref<Agreement>({});
const code = ref('');

// 加载协议内容
const loadAgreement = async () => {
    if (!code.value) {
        return;
    }



    try {
        const res = await getAgreementByCode(code.value);
        if (res.code === 10000 && res.data) {
            agreement.value = res.data;
        } else {
            agreement.value = {};
        }
    } catch (e) {
        console.error('加载协议错误', e);
        agreement.value = {};
    }
};




// 页面加载时获取参数并请求数据
onLoad((options) => {
    if (options.code) {
        code.value = options.code;
        loadAgreement();
    } else {
    }
});
</script>

<style lang="scss" scoped>
.agreement-container {
    min-height: 100vh;
    background-color: #fff;
    display: flex;
    flex-direction: column;
}




.agreement-content {
    flex: 1;
    padding: 30rpx;
    margin-top: 90rpx;

    /* #ifdef APP-PLUS || MP */
    margin-top: calc(90rpx + var(--status-bar-height));
    /* #endif */
}

.agreement-title {
    font-size: 40rpx;
    font-weight: bold;
    color: #333;
    text-align: center;
    margin-bottom: 40rpx;
}

.agreement-body {
    font-size: 28rpx;
    color: #333;
    line-height: 1.8;
}
</style>