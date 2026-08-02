<template>
  <uni-popup ref="popup" type="bottom" :safe-area="false">
    <view class="reply-popup">
      <view class="popup-header">
        <text class="popup-title">回复评论</text>
        <uni-icons 
          type="closeempty" 
          size="20" 
          color="#999" 
          @click="closeDialog"
        />
      </view>
      
      <view class="popup-content">
        <!-- 原评论内容 -->
        <view class="original-comment" v-if="currentComment">
          <view class="comment-user">{{ currentComment.user?.nickname || '匿名用户' }}</view>
          <view class="comment-text">{{ currentComment.content }}</view>
        </view>
        
        <!-- 回复输入框 -->
        <view class="reply-input-section">
          <textarea 
            v-model="replyContent"
            class="reply-textarea"
            placeholder="请输入回复内容..."
            :maxlength="500"
            auto-height
          />
          <view class="char-count">{{ replyContent.length }}/500</view>
        </view>
      </view>
      
      <view class="popup-footer">
        <button class="cancel-btn ds-btn-md ds-btn-white" @click="closeDialog">
          取消
        </button>
        <button 
          class="confirm-btn ds-btn-md ds-btn-blue" 
          @click="submitReply"
          :disabled="!replyContent.trim() || submitting"
        >
          {{ submitting ? '提交中...' : '发送回复' }}
        </button>
      </view>
    </view>
  </uni-popup>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { replyRiderComment } from '../../../api/riderComment'

// 定义评论数据接口
interface CommentItem {
  id: number
  user_id: number
  rider_id: number
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
  rider?: {
    id: number
    name: string
    mobile: string
    avg_score: string
    comment_count: number
  }
}

// Emits 定义
interface Emits {
  'success': []
}

const emit = defineEmits<Emits>()

// 弹窗引用
const popup = ref<any>(null)

// 当前评论数据
const currentComment = ref<CommentItem | null>(null)

// 回复内容
const replyContent = ref('')

// 提交状态
const submitting = ref(false)

// 打开弹窗
const openDialog = (comment: CommentItem) => {
  currentComment.value = comment
  replyContent.value = ''
  popup.value?.open()
}

// 关闭弹窗
const closeDialog = () => {
  popup.value?.close()
  currentComment.value = null
  replyContent.value = ''
}

// 提交回复
const submitReply = async () => {
  if (!replyContent.value.trim()) {
    uni.showToast({
      title: '请输入回复内容',
      icon: 'none'
    })
    return
  }

  if (!currentComment.value) {
    uni.showToast({
      title: '评论信息错误',
      icon: 'none'
    })
    return
  }

  submitting.value = true

  try {
    const result = await replyRiderComment({
      id: currentComment.value.id,
      reply_content: replyContent.value.trim()
    })

    if (result.code === 10000) {
      uni.showToast({
        title: '回复成功',
        icon: 'success'
      })
      
      closeDialog()
      emit('success')
    } else {
      uni.showToast({
        title: result.message || '回复失败',
        icon: 'none'
      })
    }
  } catch (error) {
    console.error('回复评论失败:', error)
    uni.showToast({
      title: '网络错误',
      icon: 'none'
    })
  } finally {
    submitting.value = false
  }
}

// 暴露方法给父组件
defineExpose({
  openDialog,
  closeDialog
})
</script>

<script lang="ts">
export default {
  name: 'ReplyPopup'
}
</script>

<style scoped lang="scss">
.reply-popup {
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  max-height: 80vh;
  overflow: hidden;

  .popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 32rpx 40rpx;
    border-bottom: 1rpx solid #eee;

    .popup-title {
      font-size: 32rpx;
      font-weight: 600;
      color: #333;
    }
  }

  .popup-content {
    padding: 32rpx 40rpx;
    max-height: 60vh;
    overflow-y: auto;

    .original-comment {
      background: #f8f9fa;
      padding: 20rpx;
      border-radius: 12rpx;
      margin-bottom: 24rpx;

      .comment-user {
        font-size: 24rpx;
        color: #666;
        margin-bottom: 8rpx;
      }

      .comment-text {
        font-size: 28rpx;
        color: #333;
        line-height: 1.5;
      }
    }

    .reply-input-section {
      position: relative;

      .reply-textarea {
        width: 100%;
        min-height: 200rpx;
        max-height: 400rpx;
        padding: 20rpx;
        border: 1rpx solid #ddd;
        border-radius: 12rpx;
        font-size: 28rpx;
        color: #333;
        background: #fff;
        resize: none;
        box-sizing: border-box;
        overflow-y: auto;
      }

      .char-count {
        position: absolute;
        bottom: 12rpx;
        right: 12rpx;
        font-size: 24rpx;
        color: #999;
        background: rgba(255, 255, 255, 0.8);
        padding: 4rpx 8rpx;
        border-radius: 8rpx;
      }
    }
  }

  .popup-footer {
    display: flex;
    gap: 20rpx;
    padding: 32rpx 40rpx;
    border-top: 1rpx solid #eee;

    .cancel-btn,
    .confirm-btn {
      flex: 1;
      height: 80rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12rpx;
      font-size: 28rpx;
    }

    .confirm-btn {
      &:disabled {
        opacity: 0.6;
      }
    }
  }
}
</style> 