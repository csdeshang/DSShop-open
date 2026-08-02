<template>
  <view class="search-container" :style="containerStyle">
    <view class="search-wrapper">
      <DsLbsPicker v-if="showLbs" :color="fontStyle.color" />
      <image v-if="showLogo && logoImage" class="search-logo" :src="formatImageUrl(logoImage, ThumbnailPresets.compact)" mode="aspectFit" />
      <uni-search-bar v-model="searchQuery" :placeholder="placeholderText" cancelButton="none" radius="20" :focus="false" @confirm="navigateToSearch" class="search-bar" />
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

import DsLbsPicker from '@/components/ds-lbs-picker/index.vue';



interface Settings {
  logoSetting: {
    is_show: boolean;
    image: string;
    name: string;
  };
  lbsSetting: {
    is_show: boolean;
  };
  searchSetting: {
    placeholder: string;
  };
  baseStyles: BaseStyles; // 使用共享接口
}

// 扩展基础配置接口
interface BaseConfig extends BaseDiyConfig {
  settings: Settings;
}

// 接收父组件传递的配置
const props = defineProps<{
  config: BaseConfig;
}>();


// 搜索查询的响应式变量
const searchQuery = ref('');


const showLbs = computed(() => {
  return props.config.settings?.lbsSetting?.is_show || false;
});


// 计算是否显示 Logo
const showLogo = computed(() => {
  return props.config.settings?.logoSetting?.is_show || false;
});

// 计算 Logo 图片
const logoImage = computed(() => {
  return props.config.settings?.logoSetting?.image || '';
});

const logoName = computed(() => {
  return props.config.settings?.logoSetting?.name || '';
});

// 计算占位符文本
const placeholderText = computed(() => {
  return props.config.settings?.searchSetting?.placeholder || '搜索商品...';
});


// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 搜索并导航到结果页面 - 只保留一个必要函数
const navigateToSearch = () => {
  if (!searchQuery.value.trim()) return;

  uni.navigateTo({
    url: `/home/pages/search/goodslist/index?keyword=${encodeURIComponent(searchQuery.value)}`
  });
};
</script>

<style lang="scss" scoped>
.search-container {
  width: 100%;
  box-sizing: border-box;
  padding: 20rpx 30rpx;
  position: relative;
  background-color: #ffffff;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.03);
}

.search-wrapper {
  display: flex;
  align-items: center;
  width: 100%;
  position: relative;
  
  .search-logo {
    width: 60rpx;
    height: 60rpx;
    margin: 0 20rpx;
    flex-shrink: 0;
    border-radius: 50%;
    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.1);
  }
  
  .search-bar {
    flex: 1;
    
    :deep(.uni-searchbar) {
      padding: 0;
    }
    
    :deep(.uni-searchbar__box) {
      height: 70rpx;
      border-radius: 35rpx;
      background-color: #f5f5f5;
      border: 1rpx solid #f0f0f0;
    }
    
    :deep(.uni-searchbar__text-input) {
      font-size: 28rpx;
    }
  }
}
</style>
