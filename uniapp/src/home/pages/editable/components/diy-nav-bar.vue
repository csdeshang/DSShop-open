<template>
  <view :style="containerStyle">
    <view class="nav-items" :style="navGridStyle">
      <view 
        v-for="(item, index) in navList" 
        :key="item.id" 
        class="nav-item"
        @click="handleNavClick(item)"
      >
        <image 
          class="nav-image" 
          :src="formatImageUrl(item.image, ThumbnailPresets.small)" 
          mode="aspectFill"
        />
        <text class="nav-title" :style="fontStyle">{{ item.title }}</text>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

// 接收父组件传递的配置
const props = defineProps({
  config: {
    type: Object,
    required: true
  }
});


// 导航列表
const navList = computed(() => {
  return props.config.settings?.navList || [];
});



// 计算导航网格样式
const navGridStyle = computed(() => {
  const itemsPerRow = props.config.settings?.navSettings?.itemsPerRow || 4;
  
  return {
    gridTemplateColumns: `repeat(${itemsPerRow}, 1fr)`
  };
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);
// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);


// 处理导航点击
const handleNavClick = (item) => {
  // console.log(item);return;
  if (item.link) {
    uni.navigateTo({ url: item.link });
  }
};
</script>

<style scoped lang="scss">

.nav-items{
  padding: 10px 0;
  display: grid;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

}

.nav-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;


}

.nav-title {
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
  font-size: 13px;
  color: #333;
  line-height:50rpx;
}

</style>
