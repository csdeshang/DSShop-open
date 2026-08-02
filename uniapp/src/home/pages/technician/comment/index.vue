<template>
  <view class="comment-page">
    <!-- 评论列表 -->
    <z-paging ref="pagingRef" v-model="commentList" @query="queryCommentList">
      <!-- 筛选条件 -->
      <view class="comment-tabs">
        <view v-for="(tab, index) in replyStatusOptions" :key="index" class="tab-item"
          :class="{ active: replyStatusIndex === index }" @click="switchTab(index)">
          {{ tab.label }}
        </view>
      </view>

      <view class="comment-list">
        <view class="comment-item" v-for="item in commentList" :key="item.id">
          <!-- 评论头部 -->
          <view class="comment-header">
            <view class="user-info">
              <image class="user-avatar" :src="formatImageUrl(item.user.avatar, ThumbnailPresets.small, 'avatar')"
                mode="aspectFill" />
              <view class="user-details">
                <text class="user-name">{{ item.is_anonymous ? '匿名用户' : (item.user?.nickname || item.user?.username ||
                  '用户') }}</text>
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

      <!-- 自定义空数据 -->
      <template #empty>
        <DsEmpty text="暂无评价" />
      </template>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getTechnicianCommentPages } from '@/home/api/technician/technicianComment'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import DsEmpty from '@/components/ds-empty/index.vue'

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

// 分页组件引用
const pagingRef = ref<any>(null)

// 评论列表数据
const commentList = ref<CommentItem[]>([])

// 师傅ID
const technicianId = ref<string | number>('')

// 筛选参数
const filterParams = reactive({
  technician_id: technicianId,
  is_reply: ''
})

// 回复状态选项索引
const replyStatusIndex = ref(0)

// 回复状态选项
const replyStatusOptions = [
  { label: '全部', value: '' },
  { label: '未回复', value: '0' },
  { label: '已回复', value: '1' }
]

// 获取评论列表
const queryCommentList = async (page_current: number, page_size: number) => {
  try {
    const params = {
      page_current,
      page_size,
      ...filterParams
    }

    const result = await getTechnicianCommentPages(params)

    if (result.code === 10000) {
      pagingRef.value?.complete(result.data?.data || [])
    } else {
      pagingRef.value?.complete(false)
      uni.showToast({
        title: result.message || '获取评论失败',
        icon: 'none'
      })
    }
  } catch (error) {
    console.error('获取评论列表失败:', error)
    pagingRef.value?.complete(false)
    uni.showToast({
      title: '网络错误',
      icon: 'none'
    })
  }
}

// 切换选项卡
const switchTab = (index: number) => {
  if (replyStatusIndex.value !== index) {
    replyStatusIndex.value = index
    filterParams.is_reply = replyStatusOptions[index].value
    refreshList()
  }
}

// 刷新列表
const refreshList = () => {
  pagingRef.value?.reload()
}

// 页面加载
onLoad((options) => {
  if (options?.technician_id) {
    technicianId.value = Number(options.technician_id)
  }
})
</script>

<style scoped lang="scss">
.comment-page {
  min-height: 100vh;
  background-color: #f8f8f8;

  .comment-tabs {
    display: flex;
    background-color: #fff;
    padding: 20rpx 0;
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
    margin-bottom: 20rpx;

    .tab-item {
      flex: 1;
      text-align: center;
      padding: 20rpx 0;
      font-size: 26rpx;
      color: #666;
      position: relative;

      &.active {
        color: #67c23a;
        font-weight: 500;

        &::after {
          content: '';
          position: absolute;
          bottom: 0;
          left: 50%;
          transform: translateX(-50%);
          width: 60rpx;
          height: 4rpx;
          background-color: #67c23a;
          border-radius: 2rpx;
        }
      }
    }
  }

  .comment-list {
    padding: 0 20rpx;

    .comment-item {
      background: #fff;
      border-radius: 12rpx;
      padding: 24rpx;
      margin-bottom: 20rpx;
      box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.06);

      .comment-header {
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
