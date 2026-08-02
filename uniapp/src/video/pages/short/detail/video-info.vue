<template>
  <view class="video-info">
    <view class="author-info" @click="goToUserProfile">
      <image class="avatar" :src="formatImageUrl(videoData.blogger?.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
      <text class="username">@{{ videoData.blogger?.blogger_name }}</text>
    </view>
    <view class="video-title">{{ videoData.title }}</view>
    <view class="video-description" v-if="videoData.description">{{ videoData.description }}</view>
  </view>
</template>

<script setup>
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// Props
const props = defineProps({
  videoData: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

// 跳转到用户主页
const goToUserProfile = () => {
  if (props.videoData.blogger?.id) {
    uni.navigateTo({
      url: `/video/pages/space/index?id=${props.videoData.blogger.id}`,
      fail: (error) => {
        console.error('跳转用户主页失败:', error)
      }
    })
  }
}
</script>

<style lang="scss" scoped>
.video-info {
  .author-info {
    display: flex;
    align-items: center;
    margin-bottom: 20rpx;
    cursor: pointer;
    
    .avatar {
      width: 80rpx;
      height: 80rpx;
      border-radius: 50%;
      border: 2rpx solid #fff;
      margin-right: 20rpx;
    }
    
    .username {
      color: #fff;
      font-size: 30rpx;
      font-weight: bold;
      word-break: break-word;
      overflow-wrap: break-word;
    }
  }
  
  .video-title {
    color: #fff;
    font-size: 32rpx;
    font-weight: bold;
    margin-bottom: 10rpx;
    line-height: 1.4;
    word-break: break-word;
    overflow-wrap: break-word;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .video-description {
    color: rgba(255, 255, 255, 0.8);
    font-size: 28rpx;
    line-height: 1.5;
    word-break: break-word;
    overflow-wrap: break-word;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
