<template>
    <view class="diy-row-line" :style="containerStyle">
        <view class="line-item" :style="lineStyles"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';


interface Settings {
    lineSetting: {
        // 实线 虚线 点线
        style: 'solid' | 'dashed' | 'dotted';
        //高度
        height: number;
        //颜色
        color: string;
    }
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
const lineSetting = computed(() => {
    return props.config.settings?.lineSetting || {};
});

// 计算线条样式
const lineStyles = computed(() => {
    const style = lineSetting.value.style || 'solid';
    const height = lineSetting.value.height || 1;
    const color = lineSetting.value.color || '#f0f0f0';
    
    if (style === 'solid') {
        return {
            height: `${height}px`,
            backgroundColor: color,
            width: '100%'
        };
    } else {
        return {
            height: '0',
            borderTopWidth: `${height}px`,
            borderTopStyle: style,
            borderTopColor: color,
            width: '100%'
        };
    }
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);


</script>

<style lang="scss" scoped>
.diy-row-line {
    width: 100%;
    box-sizing: border-box;
    padding: 0;
}

.line-item {
    width: 100%;
    box-sizing: border-box;
}
</style>