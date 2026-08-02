<template>
  <view class="short-video-page">
    <!-- 主要内容区域 -->
    <view class="main-content">
      <!-- 顶部导航栏 -->
      <view class="nav-bar">
        <view class="nav-item" :class="{ active: activeTab === 'default' }" @click="switchTab('default')">推荐</view>
        <!-- <view class="nav-item" :class="{ active: activeTab === 'follow' }" @click="switchTab('follow')">关注</view> -->
      </view>

      <!-- 视频滑动区域 -->
      <swiper class="video-swiper" :current="currentIndex" vertical @change="onSwiperChange" :indicator-dots="false"
        refresher-enabled :refresher-triggered="isLoading && videoList.length === 0" @refresherrefresh="refreshVideoList">
      <swiper-item v-for="(item, index) in videoList" :key="item.id" class="swiper-item">
        <view class="video-wrapper">
          <!-- 视频播放区域 -->
          <video :id="`video-${index}`" class="video-player" :src="item.video_url" :poster="item.cover_image"
            :show-play-btn="false" :controls="false" :loop="true" :enable-progress-gesture="false"
            :object-fit="'contain'" @click="togglePlay(index)" @ended="onVideoEnded"></video>

          <!-- 右侧操作栏 -->
          <view class="action-bar">
            <view class="action-item">
              <VideoLike :contentId="item.id" :contentType="'short'" :likeCount="item.like_count || 0"
                :isLiked="item.is_liked || false" theme="dark" :size="30" />
            </view>
            <view class="action-item" @click="handleComment(item)">
              <uni-icons type="chatbubble" size="30" color="#fff"></uni-icons>
              <text class="action-count">{{ formatCount(item.comment_count) }}</text>
            </view>
            <view class="action-item">
              <VideoCollect :contentId="item.id" :contentType="'short'" :collectCount="item.collect_count || 0"
                :isCollected="item.is_collected || false" theme="dark" :size="30" />
            </view>
            <view class="action-item">
              <VideoShare :contentId="item.id" :contentType="'short'" :shareTitle="item.title || '精彩视频分享'"
                :shareDesc="item.description || ''" :shareImage="formatImageUrl(item.cover_image || '', ThumbnailPresets.large)" theme="dark"
                :size="30" />
            </view>
          </view>

          <!-- 底部基本信息 -->
          <view class="video-bottom">
            <VideoInfo :videoData="item" />

            <!-- 底部关联商品组件 -->
            <VideoGoodsList :videoData="item" />
          </view>
        </view>
      </swiper-item>

      <!-- 加载更多的占位项 -->
      <swiper-item v-if="isLoading && videoList.length > 0" class="swiper-item">
        <view class="loading-wrapper">
          <uni-icons type="spinner-cycle" size="40" color="#fff" class="loading-icon"></uni-icons>
          <text class="loading-text">加载中...</text>
        </view>
      </swiper-item>
    </swiper>

    <!-- 首次加载状态 -->
    <view v-if="isLoading && videoList.length === 0" class="initial-loading">
      <uni-icons type="spinner-cycle" size="50" color="#fff" class="loading-icon"></uni-icons>
      <text class="loading-text">正在加载精彩内容...</text>
    </view>

    <!-- 加载错误状态 -->
    <view v-if="loadError && videoList.length === 0" class="error-state">
      <uni-icons type="warning" size="60" color="#999"></uni-icons>
      <text class="error-text">加载失败</text>
      <button class="retry-btn" @click="retryLoad">重新加载</button>
    </view>

    <!-- 无内容状态 -->
    <view v-if="!isLoading && !loadError && videoList.length === 0" class="empty-state">
      <uni-icons type="videocam" size="60" color="#999"></uni-icons>
      <text class="empty-text">暂无视频内容</text>
      <button class="retry-btn" @click="refreshVideoList">刷新试试</button>
    </view>

    <!-- 没有更多内容提示 -->
    <view v-if="!pageInfo.hasMore && videoList.length > 0 && currentIndex >= videoList.length - 1" class="no-more">
      <text class="no-more-text">没有更多内容了~</text>
    </view>

      <!-- 评论弹出层 -->
      <uni-popup ref="commentPopup" type="bottom">
        <CommentList :contentType="'short'" :contentId="currentVideoId"
          :commentCount="videoList[currentIndex]?.comment_count || 0" @close="closeCommentPopup"
          @commentCountChanged="onCommentCountChanged" :videoBloggerId="videoList[currentIndex]?.blogger_id" />
      </uni-popup>
    </view>

    <!-- 底部导航 -->
    <VideoBottomNav 
      current="discover"
      theme="dark"
    />
  </view>
</template>

<script setup>
import { ref, getCurrentInstance, onMounted, onUnmounted } from 'vue'
import { getVideoShortPages } from '@/video/api/home/videoShort'
import { createVideoViewLog } from '@/video/api/user/videoViewLog'

import { formatFileUrl } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

import CommentList from '@/video/components/comment/list.vue'

import VideoGoodsList from './video-goods-list.vue'
import VideoInfo from './video-info.vue'
// @ts-ignore
import VideoLike from '@/video/components/interaction/video-like.vue'
// @ts-ignore
import VideoCollect from '@/video/components/interaction/video-collect.vue'
// @ts-ignore
import VideoShare from '@/video/components/interaction/video-share.vue'

import { onLoad, onShow, onHide, onUnload } from '@dcloudio/uni-app'

import VideoBottomNav from '@/video/components/video-bottom-nav/index.vue'

// 获取当前实例
const { proxy } = getCurrentInstance()

// 响应式数据
const activeTab = ref('default')
const currentIndex = ref(0)
const isPlaying = ref(false)
const videoList = ref([])

// 请求参数（包含所有API和页面参数）
const params = ref({
  query_type: 'default',
  user_id: 0,
  type: 1, // 1为视频
  page_current: 1,
  page_size: 10,
  // 指定为第一个的视频id
  specified_id: 0,
})


// 评论相关数据
const currentVideoId = ref(null)

// 分页和加载状态
const pageInfo = ref({
  current: 1,
  size: 10,
  hasMore: true
})
const isLoading = ref(false)
const loadError = ref(false)

// ✨ 浏览记录相关状态
const viewLogCache = ref(new Set()) // 缓存已记录的视频ID，避免重复请求
const viewLogDebounceMap = ref(new Map()) // 防抖处理

// ref模板引用
const commentPopup = ref(null)

// 格式化数字
const formatCount = (count) => {
  count = parseInt(count) || 0
  if (count < 1000) {
    return count.toString()
  } else if (count < 10000) {
    return (count / 1000).toFixed(1) + 'k'
  } else {
    return (count / 10000).toFixed(1) + 'w'
  }
}

// ✨ 记录视频浏览
const recordVideoView = async (videoData) => {
  try {
    // 检查是否已记录过
    if (viewLogCache.value.has(videoData.id)) {
      return
    }

    // 防抖处理 - 如果500ms内重复调用同一视频，则取消之前的请求
    const videoId = videoData.id
    if (viewLogDebounceMap.value.has(videoId)) {
      clearTimeout(viewLogDebounceMap.value.get(videoId))
    }

    const timeoutId = setTimeout(async () => {
      try {
        await createVideoViewLog({
          content_type: 'short',
          content_id: videoData.id
        })
        
        // 记录到缓存，避免重复请求
        viewLogCache.value.add(videoData.id)
        
        console.log(`✅ 已记录视频 ${videoData.id} 的浏览记录`)
      } catch (error) {
        console.error('记录浏览记录失败:', error)
        // 记录失败不影响用户体验，静默处理
      } finally {
        viewLogDebounceMap.value.delete(videoId)
      }
    }, 500) // 500ms 防抖

    viewLogDebounceMap.value.set(videoId, timeoutId)
  } catch (error) {
    console.error('准备记录浏览记录时出错:', error)
  }
}

// 获取视频列表
const fetchVideoList = async (isLoadMore = false) => {
  if (isLoading.value || (!pageInfo.value.hasMore && isLoadMore)) {
    return
  }

  isLoading.value = true
  loadError.value = false

  try {
    // 更新分页参数
    params.value.page_current = isLoadMore ? pageInfo.value.current : 1

    const result = await getVideoShortPages(params.value)

    if (result.code === 10000) {
      const newVideos = result.data.data || []

      // 数据处理：添加交互状态
      const processedVideos = newVideos.map(video => {
        return {
          ...video,
          // 确保视频URL格式正确
          video_url: formatFileUrl(video.video_url),
          cover_image: formatImageUrl(video.cover_image, ThumbnailPresets.large)
        }
      })

      if (isLoadMore) {
        // 追加数据
        videoList.value = [...videoList.value, ...processedVideos]
      } else {
        // 替换数据（首次加载或刷新）
        videoList.value = processedVideos
        currentIndex.value = 0
      }

      // 更新分页信息
      pageInfo.value.hasMore = processedVideos.length === params.value.page_size
      if (processedVideos.length > 0) {
        pageInfo.value.current = isLoadMore ? pageInfo.value.current + 1 : 2
      }

    } else {
      throw new Error(result.message || '获取视频列表失败')
    }

  } catch (error) {
    console.error('获取视频列表失败:', error)
    loadError.value = true

    uni.showToast({
      title: '加载失败，请重试',
      icon: 'none'
    })
  } finally {
    isLoading.value = false
  }
}

// 切换标签
const switchTab = (tab) => {
  activeTab.value = tab

  // 更新参数
  params.value.query_type = tab

  // 重置分页信息
  pageInfo.value = {
    current: 1,
    size: 10,
    hasMore: true
  }
  fetchVideoList(false)
}

// 滑动切换视频
const onSwiperChange = (e) => {
  const lastIndex = currentIndex.value
  currentIndex.value = e.detail.current

  // 暂停上一个视频
  pauseVideo(lastIndex)
  // 播放当前视频
  playVideo(currentIndex.value)

  // 检查是否需要加载更多
  checkLoadMore()
}

// 检查是否需要加载更多
const checkLoadMore = () => {
  const totalVideos = videoList.value.length
  const threshold = 3 // 剩余3个视频时开始加载

  if (currentIndex.value >= totalVideos - threshold && pageInfo.value.hasMore && !isLoading.value) {
    console.log('触发加载更多，当前索引:', currentIndex.value, '总数量:', totalVideos)
    fetchVideoList(true)
  }
}

// 重试加载
const retryLoad = () => {
  if (loadError.value) {
    fetchVideoList(false)
  }
}

// 下拉刷新
const refreshVideoList = async () => {
  // 重置分页信息
  pageInfo.value = {
    current: 1,
    size: 10,
    hasMore: true
  }
  await fetchVideoList(false)
}

// 播放当前视频
const playCurrentVideo = () => {
  playVideo(currentIndex.value)
}

// 暂停当前视频
const pauseCurrentVideo = () => {
  pauseVideo(currentIndex.value)
}

// 播放指定索引的视频
const playVideo = (index) => {
  const videoContext = uni.createVideoContext(`video-${index}`, proxy)
  if (videoContext) {
    videoContext.play()
    isPlaying.value = true
    
    // 关键：在视频开始播放时记录浏览
    const currentVideo = videoList.value[index]
    if (currentVideo) {
      recordVideoView(currentVideo)
    }
  }
}

// 暂停指定索引的视频
const pauseVideo = (index) => {
  const videoContext = uni.createVideoContext(`video-${index}`, proxy)
  if (videoContext) {
    videoContext.pause()
    isPlaying.value = false
  }
}

// 点击视频切换播放/暂停
const togglePlay = (index) => {
  if (isPlaying.value) {
    pauseVideo(index)
  } else {
    playVideo(index)
  }
}

// 视频播放结束
const onVideoEnded = () => {
  // 循环播放已在video组件中设置
}

// 处理评论 - 优化后
const handleComment = (item) => {
  // 设置当前评论的视频信息
  currentVideoId.value = item.id

  // 打开评论弹窗
  commentPopup.value?.open()

  // 暂停当前播放的视频
  pauseVideo(currentIndex.value)
}

// 关闭评论弹窗
const closeCommentPopup = () => {
  commentPopup.value?.close()
  currentVideoId.value = null

  // 恢复视频播放
  setTimeout(() => {
    playVideo(currentIndex.value)
  }, 300)
}

// 评论数量变化的回调
const onCommentCountChanged = (newCount) => {
  // 更新当前视频的评论数
  const currentVideo = videoList.value[currentIndex.value]
  if (currentVideo && currentVideo.id === currentVideoId.value) {
    currentVideo.comment_count = newCount
  }
}

// ✨ 清理浏览记录相关资源
const cleanupViewLog = () => {
  // 清理所有防抖定时器
  viewLogDebounceMap.value.forEach(timeoutId => {
    clearTimeout(timeoutId)
  })
  viewLogDebounceMap.value.clear()
}

// Vue生命周期
onMounted(async () => {
  await fetchVideoList()
})

// uniapp页面生命周期 - 正确的使用方式
onLoad(async (options) => {


  // 设置页面参数
  if (options?.query_type) {
    params.value.query_type = options.query_type
  }
  if (options?.user_id) {
    params.value.user_id = options.user_id
  }
  if (options?.specified_id) {
    params.value.specified_id = options.specified_id
  }

  await fetchVideoList()
})

onShow(() => {
  playCurrentVideo()
})

onHide(() => {
  pauseCurrentVideo()
})

// ✨ 页面卸载时清理资源
onUnload(() => {
  cleanupViewLog()
})
</script>

<style lang="scss" scoped>
.short-video-page {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100vh;
  background-color: #000;
  overflow: hidden;
}

.main-content {
  flex: 1;
  position: relative;
  overflow: hidden;
}

.nav-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  display: flex;
  justify-content: center;
  padding: 40rpx 0 20rpx;

  .nav-item {
    color: rgba(255, 255, 255, 0.6);
    font-size: 32rpx;
    margin: 0 30rpx;
    padding: 0 10rpx;

    &.active {
      color: #fff;
      font-weight: bold;
      position: relative;

      &::after {
        content: '';
        position: absolute;
        bottom: -10rpx;
        left: 50%;
        transform: translateX(-50%);
        width: 30rpx;
        height: 6rpx;
        background-color: #fff;
        border-radius: 3rpx;
      }
    }
  }
}

.video-swiper {
  width: 100%;
  height: 100%;
  z-index: 1;
}

.swiper-item {
  width: 100%;
  height: 100%;
}

.video-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
}

.video-player {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.action-bar {
  position: absolute;
  right: 30rpx;
  bottom: 200rpx;
  display: flex;
  flex-direction: column;
  z-index: 5;

  .action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 50rpx;

    .action-count {
      color: #fff;
      font-size: 24rpx;
      margin-top: 10rpx;
    }
  }
}

.video-bottom {
  position: absolute;
  bottom: 20rpx;
  left: 30rpx;
  right: 120rpx;
  z-index: 5;

  // 视频信息与商品区域间距
  > view:first-child {
    margin-bottom: 30rpx;
  }
}

.loading-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  background-color: #000;

  .loading-icon {
    margin-bottom: 20rpx;
    animation: spin 1s linear infinite;
  }

  .loading-text {
    color: #fff;
    font-size: 28rpx;
  }
}

.initial-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #000;
  z-index: 100;

  .loading-icon {
    margin-bottom: 20rpx;
    animation: spin 1s linear infinite;
  }

  .loading-text {
    color: #fff;
    font-size: 28rpx;
  }
}

.error-state {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #000;
  z-index: 100;

  .error-text {
    color: #fff;
    font-size: 28rpx;
    margin: 20rpx 0;
  }

  .retry-btn {
    background-color: #fff;
    color: #000;
    padding: 20rpx 40rpx;
    border-radius: 10rpx;
    border: none;
    font-size: 28rpx;
  }
}

.empty-state {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: #000;
  z-index: 100;

  .empty-text {
    color: #fff;
    font-size: 28rpx;
    margin: 20rpx 0;
  }

  .retry-btn {
    background-color: #fff;
    color: #000;
    padding: 20rpx 40rpx;
    border-radius: 10rpx;
    border: none;
    font-size: 28rpx;
  }
}

.no-more {
  position: absolute;
  bottom: 100rpx;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.6);
  padding: 20rpx 40rpx;
  border-radius: 50rpx;
  z-index: 10;

  .no-more-text {
    color: #fff;
    font-size: 24rpx;
  }
}

// 旋转动画
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>
