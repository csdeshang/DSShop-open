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
              <image class="user-avatar" :src="item.user?.avatar || '/static/images/default-avatar.png'"
                mode="aspectFill" />
              <view class="user-details">
                <text class="user-name">{{ item.user?.nickname || '匿名用户' }}</text>
                <text class="comment-time">{{ item.create_at }}</text>
              </view>
            </view>

            <view class="comment-status">
              <view class="status-tag" :class="item.is_show ? 'show' : 'hide'">
                {{ item.is_show ? '显示' : '隐藏' }}
              </view>
            </view>
          </view>

          <!-- 订单信息 -->
          <view class="order-info" v-if="item.order_id">
            <text class="order-label">订单ID：</text>
            <text class="order-value">{{ item.order_id }}</text>
          </view>

          <!-- 评论内容 -->
          <view class="comment-content">
            <view class="comment-text">{{ item.content }}</view>

            <!-- 评分 -->
            <view class="comment-rating">
              <uni-rate :value="item.service_score" :readonly="true" :size="16" />
              <text class="rating-text">{{ item.service_score }}分</text>
            </view>
          </view>

          <!-- 回复区域 -->
          <view class="reply-section">
            <view class="reply-content" v-if="item.is_reply && item.reply_content">
              <view class="reply-label">师傅回复：</view>
              <view class="reply-text">{{ item.reply_content }}</view>
              <view class="reply-time">{{ item.reply_time }}</view>
            </view>

            <view class="reply-actions" v-if="!item.is_reply">
              <button class="reply-btn ds-btn-sm ds-btn-green" @click="showReplyDialog(item)">
                回复评论
              </button>
            </view>
          </view>
        </view>
      </view>
    </z-paging>

    <!-- 回复弹窗组件 -->
    <ReplyPopup ref="replyPopupRef" @success="onReplySuccess" />
  </view>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { getTechnicianCommentPages } from '@/technician/api/technicianComment'
import ReplyPopup from './reply-popup.vue'

// 定义评论数据接口
interface CommentItem {
  id: number
  user_id: number
  technician_id: number
  order_id: number
  content: string
  service_score: number
  is_show: number
  is_anonymous: number
  is_reply: number
  reply_content: string
  reply_time: number
  create_at: string
  update_at: string
  user?: {
    id: number
    nickname: string
    avatar: string
    username: string
  }
  technician?: {
    id: number
    name: string
    mobile: string
    avg_score: string
    comment_count: number
  }
}

// 分页组件引用
const pagingRef = ref<any>(null)

// 评论列表数据
const commentList = ref<CommentItem[]>([])

// 筛选参数
const filterParams = reactive({
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

// 回复弹窗组件引用
const replyPopupRef = ref<any>(null)

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
      pagingRef.value?.complete(result.data.data)
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

// 显示回复弹窗
const showReplyDialog = (comment: CommentItem) => {
  replyPopupRef.value?.openDialog(comment)
}

// 回复成功回调
const onReplySuccess = () => {
  refreshList()
}

// 页面加载
onMounted(() => {
  // 初始化数据会通过 z-paging 自动触发
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

    .tab-item {
      flex: 1;
      text-align: center;
      font-size: 26rpx;
      color: #666;
      position: relative;
      padding-bottom: 10rpx;

      &.active {
        color: #67c23a;
        font-weight: bold;

        &::after {
          content: '';
          position: absolute;
          bottom: 0;
          left: 50%;
          transform: translateX(-50%);
          width: 40rpx;
          height: 4rpx;
          background-color: #67c23a;
          border-radius: 2rpx;
        }
      }
    }
  }

  .comment-list {
    padding: 20rpx;
    margin-top: 20rpx;

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

        .comment-status {
          display: flex;
          align-items: center;

          .status-tag {
            padding: 8rpx 16rpx;
            border-radius: 20rpx;
            font-size: 24rpx;

            &.show {
              background: #e7f4ff;
              color: #1890ff;
            }

            &.hide {
              background: #fff2e8;
              color: #fa8c16;
            }
          }
        }
      }

      .order-info {
        margin-bottom: 16rpx;
        padding: 12rpx 16rpx;
        background: #f8f9fa;
        border-radius: 8rpx;

        .order-label {
          font-size: 24rpx;
          color: #666;
        }

        .order-value {
          font-size: 24rpx;
          color: #333;
          font-weight: 500;
        }
      }

      .comment-content {
        margin-bottom: 16rpx;

        .comment-text {
          font-size: 28rpx;
          color: #333;
          line-height: 1.6;
          margin-bottom: 12rpx;
        }

        .comment-rating {
          display: flex;
          align-items: center;
          gap: 8rpx;

          .rating-text {
            font-size: 24rpx;
            color: #666;
          }
        }
      }

      .reply-section {
        border-top: 1rpx solid #eee;
        padding-top: 16rpx;

        .reply-content {
          background: #f8f9fa;
          padding: 16rpx;
          border-radius: 8rpx;
          margin-bottom: 12rpx;

          .reply-label {
            font-size: 24rpx;
            color: #666;
            margin-bottom: 8rpx;
          }

          .reply-text {
            font-size: 28rpx;
            color: #333;
            line-height: 1.5;
            margin-bottom: 8rpx;
          }

          .reply-time {
            font-size: 24rpx;
            color: #999;
          }
        }

        .reply-actions {
          display: flex;
          justify-content: flex-end;

          .reply-btn {
            padding: 12rpx 24rpx;
          }
        }
      }
    }
  }
}
</style>
