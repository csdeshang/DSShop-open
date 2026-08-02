<template>
    <view class="diy-row-title" :style="containerStyle">
        <view class="title-container" @click="handleTitleClick">
            <view class="title-left">
                <view class="title-indicator"></view>
                <text class="title-text" :style="fontStyle">{{ titleSetting.title }}</text>
            </view>
            <view class="title-right" v-if="titleSetting.link">
                <text class="more-text">更多</text>
                <uni-icons type="right" size="12" :color="fontStyle.color || '#999'"></uni-icons>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';


interface Settings {
    titleSetting: {
        // 标题名称
        title: string;
        // 链接
        link: string;
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


// 获取标题设置
const titleSetting = computed(() => {
    return props.config.settings?.titleSetting || {};
});

// 处理标题点击
const handleTitleClick = () => {
    if (titleSetting.value.link) {
        uni.navigateTo({
            url: titleSetting.value.link,
            fail: (err) => {
                console.error('导航失败:', err);
            }
        });
    }
};

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);


</script>

<style lang="scss" scoped>
.diy-row-title {
    width: 100%;
    box-sizing: border-box;
    background-color: #fff;
}

.title-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    position: relative;
}

.title-left {
    display: flex;
    align-items: center;
}

.title-indicator {
    width: 4px;
    height: 16px;
    background-color: var(--color-primary, #ff6b6b);
    border-radius: 2px;
    margin-right: 8px;
}

.title-text {
    font-size: 16px;
    font-weight: 500;
    line-height: 1.4;
}

.title-right {
    display: flex;
    align-items: center;
    opacity: 0.8;
}

.more-text {
    font-size: 12px;
    color: #999;
    margin-right: 4px;
}
</style>