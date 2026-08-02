<template>
  <view class="diy-swiper-container" :style="containerStyle">
    <swiper 
      class="swiper" 
      :style="{ height: config.settings.swiperSetting.height + 'rpx' }"
      :indicator-dots="config.settings.swiperSetting.indicator_dots" 
      :autoplay="config.settings.swiperSetting.autoplay" 
      :interval="config.settings.swiperSetting.interval" 
      :duration="500"
      :circular="true"
      indicator-active-color="#ffffff"
      indicator-color="rgba(255, 255, 255, 0.5)"
    >
      <swiper-item v-for="item in config.settings.imagesList" :key="item.id" @click="handleItemClick(item)">
        <view class="swiper-item">
          <image :src="formatImageUrl(item.image, ThumbnailPresets.large)" mode="aspectFill" class="swiper-image" />
        </view>
      </swiper-item>
    </swiper>
  </view>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

interface Settings {
  swiperSetting: {
    // 高度
    height: number,
    // 是否自动播放
    autoplay: boolean,
    // 自动播放间隔
    interval: number,
    // 是否显示指示点
    indicator_dots: boolean,
  },
  imagesList: {
    id: string;
    image: string;
    title: string;
    link: string;
  }[];
  baseStyles: BaseStyles;
}

interface BaseConfig extends BaseDiyConfig {
  settings: Settings;
}

const props = defineProps<{
  config: BaseConfig;
}>();

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 处理轮播图点击
const handleItemClick = (item: {link?: string}) => {
  if (item.link) {
    // 根据链接类型处理导航逻辑
    if (item.link.startsWith('http')) {
      // 外部链接
      uni.navigateTo({
        url: `/pages/webview/index?url=${encodeURIComponent(item.link)}`
      });
    } else {
      // 内部页面
      uni.navigateTo({ url: item.link });
    }
  }
};
</script>

<style scoped lang="scss">
.diy-swiper-container {
  width: 100%;
  box-sizing: border-box;

  overflow: hidden;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
}

.swiper {
  width: 100%;
  
}

.swiper-item {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.swiper-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;

}

// 触摸效果（小程序和H5环境）
.swiper-item:hover .swiper-image {
  transform: scale(1.02);
}

// 指示点样式优化
:deep(.wx-swiper-dots) {
  bottom: 20rpx;
}

:deep(.wx-swiper-dot) {
  width: 10rpx;
  height: 10rpx;
  transition: all 0.3s;
}

:deep(.wx-swiper-dot-active) {
  width: 20rpx;
  border-radius: 5rpx;
}
</style>