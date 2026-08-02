<template>
    <view>
        <view class="localtion" @click="openAddressSelector">
            <uni-icons type="location" size="20" :style="{ color: color }"></uni-icons><text class="city"
                :style="{ color: color }">{{ lbsStore.name }}</text>
            <uni-icons type="right" size="16" :style="{ color: color }"></uni-icons>
        </view>
        <SelectAddress ref="addressSelectorRef" />
    </view>
</template>

<script setup lang="ts">

import { onMounted, ref, watch } from 'vue';

import { useConfigStore } from '@/stores/modules/config';
import { useLbsStore } from '@/stores/modules/lbs';
import SelectAddress from './select-address.vue';

const props = defineProps({
    color: {
        type: String,
        default: '#333333'
    }
})


const lbsStore = useLbsStore();
const configStore = useConfigStore();

// 是否开启定位
const lbs_is_enabled = ref('0');
async function fetchLbsConfig() {
    lbs_is_enabled.value = await configStore.fetchConfig('lbs:lbs_is_enabled');
}

// 地址选择器
const addressSelectorRef = ref();
const openAddressSelector = () => {
    addressSelectorRef.value.open();
};


// 监听lbs_is_enabled ， 为1时 获取 地址信息
watch(lbs_is_enabled, (newVal) => {
    if (newVal == '1') {
        lbsStore.getLocation();
    }
})

// 打开地址选择器


// 组件挂载时加载数据
onMounted(() => {
    fetchLbsConfig();
})


</script>

<style lang="scss" scoped>
.localtion {
    margin-left:8rpx;
    display: flex;
    align-items: center;
    justify-content: space-between;

    .city {
        font-size: 26rpx;
        color: #333;
        max-width: 130rpx;
        /* 限制最大宽度 */
        overflow: hidden;
        /* 超出隐藏 */
        text-overflow: ellipsis;
        /* 超出显示省略号 */
        white-space: nowrap;
        /* 不换行 */
        line-height: 100%;
    }
}
</style>
