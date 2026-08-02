<template>
  <view :style="containerStyle">
    <rich-text 
      :nodes="formatRichText(config.settings.content)"
      :style="fontStyle"
    />
  </view>
</template>

<script lang="ts" setup>

import { computed } from 'vue';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';
import { formatRichText } from '@/utils/util';

interface Settings {
  content: string;
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


// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);


</script>
