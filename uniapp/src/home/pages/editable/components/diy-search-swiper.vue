<template>
  <view class="container" :style="containerStyle">
    <!-- 背景轮播图 -->
    <view class="bg-swiper-container">
      <swiper class="bg-swiper" :current="currentIndex" :autoplay="false" :circular="true" :indicator-dots="false">
        <swiper-item v-for="(item) in config.settings.imagesList" :key="item.id">
          <image :src="formatImageUrl(item.image, ThumbnailPresets.large)" mode="aspectFill" class="bg-swiper-image" />
        </swiper-item>
      </swiper>
      <!-- 使用渐变模糊覆盖层代替单一模糊效果 -->
      <view class="bg-gradient-overlay"></view>
    </view>
    
    <!-- 搜索区域 -->
    <view class="search-container">
      <DsLbsPicker v-if="showLbs" :color="fontStyle.color" class="lbs-picker" />
      <image v-if="showLogo && logoImage" class="search-logo" :src="formatImageUrl(logoImage, ThumbnailPresets.compact)" mode="aspectFit" />
      <uni-search-bar v-model="searchQuery" :placeholder="placeholderText" cancelButton="none" radius="20" :focus="false" @confirm="navigateToSearch" class="search-bar" />
    </view>
    
    <!-- 前景轮播图 -->
    <view class="swiper-container">
      <swiper class="swiper" 
        :style="{ height: config.settings.swiperSetting.height + 'rpx' }"
        :indicator-dots="config.settings.swiperSetting.indicator_dots" 
        :autoplay="config.settings.swiperSetting.autoplay" 
        :interval="config.settings.swiperSetting.interval" 
        :duration="500" 
        :circular="true"
        @change="onSwiperChange"
        indicator-active-color="#ffffff" 
        indicator-color="rgba(255, 255, 255, 0.5)">
        <swiper-item v-for="(item) in config.settings.imagesList" :key="item.id">
          <view class="swiper-item" @click="navigateToLink(item.link)">
            <image :src="formatImageUrl(item.image, ThumbnailPresets.large)" mode="aspectFill" class="swiper-image" />
          </view>
        </swiper-item>
      </swiper>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

import DsLbsPicker from '@/components/ds-lbs-picker/index.vue';

// 当前轮播图索引
const currentIndex = ref(0);

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

// 添加链接导航方法
const navigateToLink = (link: string) => {
  if (!link) return;
  
  // 根据链接类型处理导航逻辑
  if (link.startsWith('http')) {
    // 外部链接
    uni.navigateTo({
      url: `/pages/webview/index?url=${encodeURIComponent(link)}`
    });
  } else {
    // 内部页面
    uni.navigateTo({
      url: link
    });
  }
};

// 轮播图变化时同步背景
const onSwiperChange = (e: any) => {
  currentIndex.value = e.detail.current;
};
</script>

<style lang="scss" scoped>
.container {
  width: 100%;
  box-sizing: border-box;
  padding: 20rpx 30rpx 30rpx;
  position: relative;
  
  // 背景轮播图样式
  .bg-swiper-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    
    .bg-swiper {
      width: 100%;
      height: 100%;
      
      .bg-swiper-image {
        width: 100%;
        height: 100%;
        filter: blur(5rpx); // 减少整体模糊度，由渐变覆盖层控制
        transform: scale(1.1); // 避免模糊后边缘透明
        transition: all 0.5s ease-in-out;
      }
    }
    
    // 使用渐变覆盖层代替单一模糊效果
    .bg-gradient-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      // 创建一个从上到下逐渐增加模糊和透明度的渐变遮罩
      background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0.4) 0%, // 顶部较透明
        rgba(255, 255, 255, 0.6) 30%, // 中上部分
        rgba(255, 255, 255, 0.7) 60%, // 中下部分
        rgba(255, 255, 255, 0.85) 100% // 底部较不透明
      );
      // 使用多层背景实现渐变模糊效果
      backdrop-filter: blur(0rpx); // 顶部无模糊
      mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        rgba(0, 0, 0, 0.3) 30%,
        rgba(0, 0, 0, 0.7) 60%,
        rgba(0, 0, 0, 1) 100%
      );
      -webkit-mask-image: linear-gradient(
        to bottom,
        transparent 0%,
        rgba(0, 0, 0, 0.3) 30%,
        rgba(0, 0, 0, 0.7) 60%,
        rgba(0, 0, 0, 1) 100%
      );
      z-index: 2;
      
      // 添加额外的模糊层
      &::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        backdrop-filter: blur(15rpx);
        opacity: 0;
        background: transparent;
        // 从上到下模糊度渐变
        mask-image: linear-gradient(
          to bottom,
          transparent 0%,
          rgba(0, 0, 0, 0.3) 30%,
          rgba(0, 0, 0, 0.7) 60%,
          rgba(0, 0, 0, 1) 100%
        );
        -webkit-mask-image: linear-gradient(
          to bottom,
          transparent 0%,
          rgba(0, 0, 0, 0.3) 30%,
          rgba(0, 0, 0, 0.7) 60%,
          rgba(0, 0, 0, 1) 100%
        );
      }
    }
  }

  // 搜索区域样式
  .search-container {
    display: flex;
    align-items: center;
    width: 100%;
    position: relative;
    z-index: 10;

    .lbs-picker {
      border-radius: 30rpx;
      padding: 0 2rpx;
      margin-left: 8rpx;
    }

    .search-logo {
      width: 60rpx;
      height: 60rpx;
      margin-left: 20rpx;
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
        background-color: rgba(255, 255, 255, 0.8);
        border: 1rpx solid rgba(255, 255, 255, 0.9);
      }

      :deep(.uni-searchbar__text-input) {
        font-size: 28rpx;
      }
    }
  }

  // 轮播图区域样式
  .swiper-container {
    margin-left: 20rpx;
    margin-right: 20rpx;
    position: relative;
    overflow: hidden;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
    z-index: 5;
    margin-top: 10rpx;

    .swiper {
      width: 100%;
      height: 400rpx;

      .swiper-item {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;

        .swiper-image {
          width: 100%;
          height: 100%;
          border-radius: 16rpx;
        }
      }
    }

    // 指示点样式调整
    :deep(.wx-swiper-dots) {
      bottom: 20rpx;
    }

    :deep(.wx-swiper-dot) {
      width: 10rpx;
      height: 10rpx;
    }

    :deep(.wx-swiper-dot-active) {
      width: 20rpx;
      border-radius: 5rpx;
    }
  }
}
</style>