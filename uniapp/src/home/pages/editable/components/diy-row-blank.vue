<template>
    <view :style="containerStyle">
        <view class="blank-item" :style="{ height: blankSetting.blankHeight + 'px' }"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';


import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';


interface Settings {
    blankSetting: {
        // 行间距
        blankHeight: number;
    };
    baseStyles: BaseStyles;
}

// 扩展基础配置接口
interface BaseConfig extends BaseDiyConfig {
    settings: Settings;
}

// 接收父组件传递的配置
const props = defineProps<{
    config: BaseConfig;
}>();


// 获取行间距设置
const blankSetting = computed(() => {
    return props.config.settings?.blankSetting || {};
});



// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);


</script>

<style lang="scss" scoped></style>