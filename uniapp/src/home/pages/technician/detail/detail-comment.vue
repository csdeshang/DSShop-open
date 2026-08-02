<template>
  <view class="comment-container">
    <!-- 评论头部 -->
    <view class="comment-header">
      <text class="header-title">用户评价</text>
      <view class="view-all" @click="navigateToAllComments">
        查看全部 <uni-icons type="right" size="14" color="#999"></uni-icons>
      </view>
    </view>
    
    <!-- 加载状态 -->
    <view v-if="loading" class="loading-container">
      <uni-load-more status="loading" />
    </view>
    
    <!-- 评论列表 -->
    <view v-else-if="commentList.length > 0" class="comment-list">
      <view class="comment-item" v-for="item in commentList" :key="item.id">
        <!-- 评论头部 -->
        <view class="comment-item-header">
          <view class="user-info">
            <image 
              class="user-avatar" 
              :src="formatImageUrl(item.user?.avatar || '', ThumbnailPresets.small, 'avatar')"
              mode="aspectFill" 
            />
            <view class="user-details">
              <text class="user-name">{{ item.is_anonymous ? '匿名用户' : (item.user?.nickname || item.user?.username || '用户') }}</text>
              <text class="comment-time">{{ item.create_at }}</text>
            </view>
          </view>
        </view>

        <!-- 评论内容 -->
        <view class="comment-content">
          <view class="comment-text">{{ item.content }}</view>

          <!-- 评分 -->
          <view class="comment-rating">
            <uni-rate :value="Number(item.service_score)" :readonly="true" :size="16" active-color="#ffca3e" />
            <text class="rating-text">{{ item.service_score }}分</text>
          </view>
        </view>

        <!-- 回复区域 -->
        <view class="reply-section" v-if="item.is_reply && item.reply_content">
          <view class="reply-content">
            <view class="reply-label">师傅回复：</view>
            <view class="reply-text">{{ item.reply_content }}</view>
            <view class="reply-time" v-if="item.reply_time">{{ item.reply_time }}</view>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 空状态 -->
    <view v-else class="empty-container">
      <DsEmpty text="暂无评价" />
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { getTechnicianCommentPages } from '@/home/api/technician/technicianComment'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import DsEmpty from '@/components/ds-empty/index.vue'

// Props 定义
const props = defineProps({
  technicianDetail: {
    type: Object,
    default: () => ({})
  }
})

// 定义评论数据接口
interface CommentItem {
  id: number
  user_id: number
  technician_id: number
  order_id: number
  content: string
  service_score: number | string
  is_show: number
  is_anonymous: number
  is_reply: number
  reply_content: string
  reply_time: string | number
  create_at: string
  update_at: string
  user?: {
    id: number
    nickname: string
    avatar: string
    username: string
  }
}

// 评论列表数据
const commentList = ref<CommentItem[]>([])
const loading = ref(false)

// 获取评论列表 - 固定获取10条数据作为预览
const fetchCommentList = async () => {
  if (!props.technicianDetail.id) return
  
  loading.value = true
  try {
    const params = {
      page_current: 1,
      page_size: 10,
      technician_id: props.technicianDetail.id,
      is_reply: ''
    }

    const result = await getTechnicianCommentPages(params)

    if (result.code === 10000) {
      commentList.value = result.data?.data || []
    } else {
      commentList.value = []
      console.error('获取评论失败:', result.message)
    }
  } catch (error) {
    console.error('获取评论列表失败:', error)
    commentList.value = []
  } finally {
    loading.value = false
  }
}

// 跳转到完整评论页面
const navigateToAllComments = () => {
  if (!props.technicianDetail.id) return
  
  uni.navigateTo({
    url: `/home/pages/technician/comment/index?technician_id=${props.technicianDetail.id}`
  })
}

// 监听 technicianDetail.id 的变化并重新获取数据
watch(
  () => props.technicianDetail.id,
  (newTechnicianId) => {
    if (newTechnicianId) {
      fetchCommentList()
    }
  },
  { immediate: true }
)

// 暴露刷新方法
defineExpose({
  refresh: fetchCommentList
})
</script>

<style scoped lang="scss">
.comment-container {
  background-color: #f8f8f8;
  padding: 20rpx;

  .comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    padding: 30rpx;
    border-radius: 12rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);

    .header-title {
      font-size: 32rpx;
      font-weight: 600;
      color: #333;
    }

    .view-all {
      display: flex;
      align-items: center;
      font-size: 26rpx;
      color: #999;
    }
  }

  .loading-container {
    display: flex;
    justify-content: center;
    padding: 60rpx 0;
  }

  .empty-container {
    background-color: #fff;
    border-radius: 12rpx;
    padding: 60rpx 0;
  }

  .comment-list {
    .comment-item {
      background: #fff;
      border-radius: 12rpx;
      padding: 24rpx;
      margin-bottom: 20rpx;
      box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);

      .comment-item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16rpx;

        .user-info {
          display: flex;
          align-items: center;

          .user-avatar {
            width: 60rpx;
            height: 60rpx;
            border-radius: 50%;
            margin-right: 16rpx;
            background-color: #f5f5f5;
          }

          .user-details {
            display: flex;
            flex-direction: column;

            .user-name {
              font-size: 28rpx;
              font-weight: 600;
              color: #333;
              margin-bottom: 4rpx;
            }

            .comment-time {
              font-size: 24rpx;
              color: #999;
            }
          }
        }
      }

      .comment-content {
        margin-bottom: 16rpx;

        .comment-text {
          font-size: 28rpx;
          color: #333;
          line-height: 1.6;
          margin-bottom: 16rpx;
        }

        .comment-rating {
          display: flex;
          align-items: center;

          .rating-text {
            font-size: 24rpx;
            color: #666;
            margin-left: 8rpx;
          }
        }
      }

      .reply-section {
        .reply-content {
          background: #f7f8fa;
          padding: 16rpx;
          border-radius: 8rpx;
          border-left: 4rpx solid #67c23a;

          .reply-label {
            font-size: 24rpx;
            color: #67c23a;
            font-weight: 500;
            margin-bottom: 8rpx;
          }

          .reply-text {
            font-size: 26rpx;
            color: #333;
            line-height: 1.5;
            margin-bottom: 8rpx;
          }

          .reply-time {
            font-size: 22rpx;
            color: #999;
          }
        }
      }
    }
  }
}
</style>