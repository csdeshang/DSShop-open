<template>
  <view class="diy-news" :style="containerStyle">
    <view class="news-container">

        <view class="news-left">
          <view class="news-icon" v-if="newsSetting.type === 'image'">
            <image class="icon-image" :src="formatImageUrl(newsSetting.image, ThumbnailPresets.small)" mode="aspectFit"></image>
          </view>
          <view class="news-tag" :style="{ backgroundColor: fontStyle.color }" v-if="newsSetting.title">{{
            newsSetting.title }}</view>
          <view class="news-content">
            <swiper class="news-swiper" vertical :autoplay="true" :interval="3000" :duration="500" :circular="true">
              <swiper-item v-for="item in newsList" :key="item.id" @click="handleNewsClick(item)">
                <view class="news-item">
                  <text class="news-item-title" :style="fontStyle">{{ item.title }}</text>
                </view>
              </swiper-item>
            </swiper>
          </view>
        </view>
        <view class="news-right" v-if="newsSetting.is_show_right_btn === '1'" @click="handleMoreClick">
          <uni-icons type="right" size="12" :color="fontStyle.color"></uni-icons>
        </view>
      
    </view>
  </view>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { goToPage } from '@/utils/util';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';



interface Settings {
  newsList: {
    id: string;
    title: string;
    link: string;
  }[];
  newsSetting: {
    type: 'image' | 'text';
    image: string;
    title: string;
    is_show_right_btn: string;
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



// 获取新闻列表数据
const newsList = computed(() => {
  return props.config.settings?.newsList || [];
});

// 获取新闻设置
const newsSetting = computed(() => {
  return props.config.settings?.newsSetting || {};
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 处理新闻点击
const handleNewsClick = (item) => {
  if (item.link) {
    goToPage({ url: item.link })
  }
};

// 处理更多按钮点击
const handleMoreClick = () => {

};
</script>

<style lang="scss" scoped>
.diy-news {

  box-sizing: border-box;




  .news-container {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 80rpx;
    padding: 0 24rpx;


    .news-left {
      display: flex;
      align-items: center;
      flex: 1;
      height: 100%;
      overflow: hidden;

      .news-tag {
        font-size: 24rpx;
        color: #ffffff;
        padding: 4rpx 14rpx;
        border-radius: 6rpx;
        margin-right: 20rpx;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.2s ease;
      }

      .news-icon {
        width: 30rpx;
        height: 30rpx;
        margin-right: 12rpx;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .icon-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
      }

      .news-content {
        flex: 1;
        height: 100%;
        overflow: hidden;

        .news-swiper {
          width: 100%;
          height: 100%;

          .news-item {
            display: flex;
            align-items: center;
            height: 100%;
            gap: 12rpx;


            .news-item-title {
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
              width: 100%;
              transition: all 0.2s ease;

              &:hover {
                color: var(--color-primary);
              }
            }
          }
        }
      }
    }

    .news-right {
      display: flex;
      align-items: center;
      font-size: 24rpx;
      padding-left: 20rpx;
      border-left: 1px solid #EEEEEE;
      margin-left: 20rpx;
      height: 26rpx;
      line-height: 1;
      transition: all 0.2s ease;


    }


  }
}
</style>
