<template>
  <view class="comment-popup">
    <view class="comment-header">
      <text class="comment-title">评论 ({{ commentCount }})</text>
      <uni-icons type="close" size="24" color="#999" @click="handleClose"></uni-icons>
    </view>

    <!-- 评论列表 -->
    <z-paging ref="paging" v-model="commentList" @query="queryCommentList" class="comment-list" :fixed="false"
      :empty-view-style="{ height: '300rpx' }" empty-view-text="暂无评论，快来抢沙发吧~">
      <view v-for="comment in commentList" :key="comment.id" class="comment-item" 
            @longpress="handleLongPress(comment)">
        <image class="comment-avatar" :src="formatImageUrl(comment.user.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
        <view class="comment-content">
          <!-- 置顶标识 -->
          <view v-if="comment.is_top" class="top-badge">
            <uni-icons type="flag-filled" size="12" color="#ff6b35" />
            <text class="top-text">置顶</text>
          </view>
          
          <view class="comment-user">{{ comment.user?.nickname || comment.user?.username || '用户' }}</view>
          <view class="comment-text">{{ comment.comment_content }}</view>
          <view class="comment-info">
            <text class="comment-time">{{ formatTime(comment.update_at || comment.created_at) }}</text>
            <view class="comment-actions">
              <view class="action-item" @click="toggleLike(comment)">
                <uni-icons :type="comment.is_liked ? 'heart-filled' : 'heart'" size="16"
                  :color="comment.is_liked ? '#ff2d92' : '#999'" />
                <text class="action-count">{{ comment.like_count || 0 }}</text>
              </view>
              <view class="action-item" @click="replyComment(comment)">
                <uni-icons type="chatbubble" size="16" color="#999" />
                <text class="reply-text">回复</text>
              </view>
            </view>
          </view>


          <!-- 回复信息 -->
          <view v-if="comment.reply_count > 0" class="reply-info">
            <view v-if="!comment.replies || comment.replies.length === 0" class="load-replies"
              @click="loadReplies(comment)">
              <text class="reply-count">共{{ comment.reply_count }}条回复 ></text>
            </view>

            <!-- 回复列表 -->
            <view v-if="comment.replies && comment.replies.length > 0" class="reply-list">
              <view v-for="(reply, rIndex) in comment.replies" :key="rIndex" class="reply-item">
                <image class="reply-avatar" :src="formatImageUrl(reply.user.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
                <view class="reply-content">
                  <text class="reply-username">{{ reply.user?.nickname || reply.user?.username || '用户' }}</text>
                  <text class="reply-text">{{ reply.comment_content }}</text>
                  <text class="reply-time">{{ formatTime(reply.update_at || reply.created_at) }}</text>
                </view>
              </view>

              <!-- 如果还有更多回复 -->
              <view v-if="comment.hasMoreReplies" class="load-more-replies" @click="loadReplies(comment, true)">
                <text>查看更多回复...</text>
              </view>
            </view>
          </view>
        </view>
      </view>
    </z-paging>

    <!-- 评论输入区域 -->
    <view class="comment-input-area">
      <view class="input-wrapper">
        <input class="comment-input" v-model="commentContent"
          :placeholder="replyTo ? `回复 @${replyTo.user?.nickname || replyTo.user?.username || '用户'}` : '说点什么...'"
          :focus="inputFocus" />
        <view v-if="replyTo" class="cancel-reply" @click="cancelReply">
          <uni-icons type="close" size="16" color="#999" />
        </view>
      </view>
      <button class="send-btn" :class="{ active: commentContent.trim() }" @click="submitComment"
        :disabled="isSubmitting">
        {{ isSubmitting ? '发送中' : '发送' }}
      </button>
    </view>

    <!-- 博主管理菜单 -->
    <CommentManageMenu 
      :visible="showManageMenu"
      :comment="selectedComment"
      @close="showManageMenu = false"
      @refresh="handleManageRefresh"
    />
  </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { createVideoComment, toggleVideoCommentLike } from '@/video/api/user/videoComment'
import { getVideoCommentPages } from '@/video/api/home/videoComment'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import CommentManageMenu from './manage-menu.vue'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'

// Props
const props = defineProps({
  contentType: {
    type: String,
    default: 'video_short'
  },
  contentId: {
    type: [String, Number],
    required: true
  },
  commentCount: {
    type: Number,
    default: 0
  },
  // 视频博主ID（注意：这是blogger.id，不是user.id，用于判断当前用户是否为视频博主）
  videoBloggerId: {
    type: [String, Number],
    default: null
  }
})

// Emits
const emit = defineEmits(['close', 'commentCountChanged'])

// z-paging 相关
const paging = ref<any>(null)
const commentList = ref<any[]>([])

// 评论相关数据
const commentContent = ref('')
const replyTo = ref<any>(null)
const isSubmitting = ref(false)
const inputFocus = ref(false)

// 管理菜单相关
const showManageMenu = ref(false)
const selectedComment = ref<any>(null)

// 用户store - 参考骑手页面的方式
const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)

// 判断当前用户是否为视频博主
const isCurrentUserVideoBlogger = () => {
  // 获取当前用户的博主ID
  const currentBloggerId = userInfo.value?.blogger?.id
  return isLogin.value && currentBloggerId && props.videoBloggerId && String(currentBloggerId) === String(props.videoBloggerId)
}

// 查询评论列表
const queryCommentList = async (pageNo: number, pageSize: number) => {
  try {
    const result = await getVideoCommentPages({
      page_current: pageNo,
      page_size: pageSize,
      content_type: props.contentType,
      content_id: props.contentId,
      pid: 0
    })

    // 返回数据给z-paging
    paging.value.complete(result.data?.data || [])
  } catch (error) {
    console.error('获取评论列表失败:', error)
    paging.value.complete(false)
  }
}

// 监听内容ID变化，重新加载评论
watch(() => props.contentId, (newId) => {
  if (newId && paging.value) {
    paging.value.reload()
  }
})

// 提交评论
const submitComment = async () => {
  if (!commentContent.value.trim() || isSubmitting.value) return

  isSubmitting.value = true

  try {
    // 调用真实的评论提交API
    const result = await createVideoComment({
      content_type: props.contentType,
      content_id: props.contentId,
      comment_content: commentContent.value.trim(),
      pid: replyTo.value ? replyTo.value.id : 0
    })

    if (result.code === 10000) {
      // API调用成功
      if (replyTo.value) {
        // 回复评论 - 重新获取父评论的回复列表
        const parentComment = commentList.value.find(item => item.id === replyTo.value.id)
        if (parentComment) {
          // 更新回复数量
          parentComment.reply_count = (parentComment.reply_count || 0) + 1

          // 重新加载回复列表以获取最新数据
          parentComment.replyPage = undefined
          parentComment.hasMoreReplies = true
          parentComment.replies = [] // 清空现有回复
          await loadReplies(parentComment) // 重新加载回复
        }
      } else {
        // 新评论 - 重新加载评论列表以获取最新数据
        paging.value.reload()
        // 通知父组件评论数量变化
        emit('commentCountChanged', props.commentCount + 1)
      }

      // 清空输入
      commentContent.value = ''
      cancelReply()

      uni.showToast({
        title: replyTo.value ? '回复成功' : '评论成功',
        icon: 'success'
      })
    } else {
      throw new Error(result.message || '评论失败')
    }

  } catch (error: any) {
    console.error('提交评论失败:', error)
    uni.showToast({
      title: error.message || '评论失败，请重试',
      icon: 'none'
    })
  } finally {
    isSubmitting.value = false
  }
}

// 回复评论
const replyComment = (comment: any) => {
  replyTo.value = comment
  inputFocus.value = true
}

// 取消回复
const cancelReply = () => {
  replyTo.value = null
  inputFocus.value = false
}

// 点赞/取消点赞
const toggleLike = async (comment: any) => {
  // 防止重复点击
  if (comment.isLiking) return

  // 乐观更新UI
  const originalIsLiked = comment.is_liked
  const originalLikeCount = comment.like_count || 0

  comment.is_liked = !originalIsLiked
  comment.like_count = originalIsLiked ? originalLikeCount - 1 : originalLikeCount + 1
  comment.isLiking = true

  try {
    // 调用真实的点赞API，只传递评论ID
    const result = await toggleVideoCommentLike(comment.id)

    if (result.code === 10000) {
      // API调用成功，使用返回的数据更新状态
      if (result.data) {
        comment.is_liked = result.data.is_liked !== undefined ? result.data.is_liked : comment.is_liked
        comment.like_count = result.data.like_count !== undefined ? result.data.like_count : comment.like_count
      }

      // 显示反馈
      uni.showToast({
        title: comment.is_liked ? '点赞成功' : '取消点赞',
        icon: 'none'
      })
    } else {
      throw new Error(result.message || '操作失败')
    }

  } catch (error: any) {
    console.error('点赞失败:', error)

    // 恢复原始状态
    comment.is_liked = originalIsLiked
    comment.like_count = originalLikeCount

    uni.showToast({
      title: error.message || '操作失败，请重试',
      icon: 'none'
    })
  } finally {
    comment.isLiking = false
  }
}

// 关闭弹窗
const handleClose = () => {
  emit('close')
}

// 格式化时间
const formatTime = (timeStr: string) => {
  const time = new Date(timeStr)
  const now = new Date()
  const diff = now.getTime() - time.getTime()

  if (diff < 60000) return '刚刚'
  if (diff < 3600000) return `${Math.floor(diff / 60000)}分钟前`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}小时前`
  return `${Math.floor(diff / 86400000)}天前`
}

// 加载回复列表
const loadReplies = async (comment: any, loadMore = false) => {
  try {
    // 初始化回复分页信息
    if (!comment.replyPage) {
      comment.replyPage = 1
      comment.hasMoreReplies = true
    }

    // 如果是加载更多，但没有更多数据，则直接返回
    if (loadMore && !comment.hasMoreReplies) {
      return
    }

    const result = await getVideoCommentPages({
      page_current: loadMore ? comment.replyPage + 1 : 1,
      page_size: 10, // 固定每次加载10条
      content_type: props.contentType,
      content_id: props.contentId,
      pid: comment.id // 获取该评论的回复
    })

    if (result.code === 10000 && result.data) {
      const newReplies = result.data.data || []

      if (loadMore) {
        // 加载更多：追加到现有回复列表
        comment.replies = [...(comment.replies || []), ...newReplies]
        comment.replyPage += 1
      } else {
        // 首次加载：替换回复列表
        comment.replies = newReplies
        comment.replyPage = 1
      }

      // 根据返回的has_more字段判断是否还有更多
      comment.hasMoreReplies = result.data.has_more || false
    }

  } catch (error) {
    console.error('加载回复失败:', error)
    uni.showToast({
      title: '加载回复失败',
      icon: 'none'
    })
  }
}

// 长按触发管理菜单（仅视频博主可用）
const handleLongPress = (comment: any) => {
  // 检查是否为视频博主：需要传入videoBloggerId且当前用户ID匹配
  if (!props.videoBloggerId || !isCurrentUserVideoBlogger()) return
  
  // 震动反馈
  uni.vibrateShort()
  
  selectedComment.value = comment
  showManageMenu.value = true
}

// 处理管理操作的刷新
const handleManageRefresh = (params?: any) => {
  if (params?.action === 'reply') {
    // 进入回复模式
    replyComment(params.comment)
  } else {
    // 刷新评论列表
    if (paging.value) {
      paging.value.reload()
    }
  }
  
  showManageMenu.value = false
  selectedComment.value = null
}



// 页面显示时也获取用户信息（确保数据最新）
onShow(async () => {
  await userStore.getUserInfoWithRoles()
})
</script>

<style scoped lang="scss">
.comment-popup {
  margin-bottom: 100rpx;
  background-color: #fff;
  border-top-left-radius: 20rpx;
  border-top-right-radius: 20rpx;
  height: 70vh;
  display: flex;
  flex-direction: column;

  .comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30rpx;
    border-bottom: 1rpx solid #f0f0f0;

    .comment-title {
      font-size: 32rpx;
      font-weight: bold;
    }
  }

  .comment-list {
    flex: 1;

    .comment-item {
      display: flex;
      padding: 30rpx 30rpx;
      border-bottom: 1rpx solid #f5f5f5;

      .comment-avatar {
        width: 70rpx;
        height: 70rpx;
        border-radius: 50%;
        margin-right: 20rpx;
      }

      .comment-content {
        flex: 1;

        .comment-user {
          font-size: 28rpx;
          font-weight: bold;
          margin-bottom: 10rpx;
        }

        .comment-text {
          font-size: 30rpx;
          line-height: 1.5;
          margin-bottom: 10rpx;
          color: #333;
        }

        .top-badge {
          display: inline-flex;
          align-items: center;
          background: linear-gradient(45deg, #ff6b35, #f7931e);
          color: #fff;
          font-size: 20rpx;
          padding: 4rpx 8rpx;
          border-radius: 8rpx;
          margin-bottom: 8rpx;

          .top-text {
            margin-left: 4rpx;
            font-weight: 500;
          }
        }

        .comment-info {
          display: flex;
          justify-content: space-between;
          align-items: center;

          .comment-time {
            color: #999;
            font-size: 24rpx;
          }

          .comment-actions {
            display: flex;
            gap: 30rpx;

            .action-item {
              display: flex;
              align-items: center;
              gap: 6rpx;

              .action-count,
              .reply-text {
                font-size: 24rpx;
                color: #666;
              }
            }
          }
        }

        .reply-info {
          margin-top: 20rpx;

          .load-replies {
            padding: 15rpx 0;
            font-size: 26rpx;
            color: #666;
            cursor: pointer;

            .reply-count {
              color: #1890ff;
            }
          }

          .reply-list {
            margin-top: 10rpx;

            .reply-item {
              display: flex;
              margin-bottom: 15rpx;
              font-size: 26rpx;
              line-height: 1.5;

              .reply-avatar {
                width: 50rpx;
                height: 50rpx;
                border-radius: 50%;
                margin-right: 15rpx;
                flex-shrink: 0;
              }

              .reply-content {
                flex: 1;

                .reply-username {
                  color: #666;
                  font-weight: bold;
                  margin-right: 10rpx;
                }

                .reply-text {
                  color: #333;
                }

                .reply-time {
                  display: block;
                  margin-top: 8rpx;
                  color: #999;
                  font-size: 22rpx;
                }
              }
            }

            .load-more-replies {
              text-align: center;
              padding: 15rpx 0;
              font-size: 24rpx;
              color: #1890ff;
              cursor: pointer;
            }
          }
        }
      }
    }
  }

  .comment-input-area {
    display: flex;
    align-items: center;
    padding: 20rpx 30rpx;
    border-top: 1rpx solid #f0f0f0;
    gap: 20rpx;

    .input-wrapper {
      flex: 1;
      position: relative;

      .comment-input {
        width: 100%;
        height: 70rpx;
        background-color: #f5f5f5;
        border-radius: 35rpx;
        padding: 0 30rpx;
        font-size: 28rpx;
      }

      .cancel-reply {
        position: absolute;
        right: 15rpx;
        top: 50%;
        transform: translateY(-50%);
        padding: 10rpx;
      }
    }

    .send-btn {
      width: 120rpx;
      height: 70rpx;
      line-height: 70rpx;
      background-color: #ddd;
      color: #999;
      font-size: 28rpx;
      border-radius: 35rpx;
      border: none;
      padding: 0;

      &.active {
        background-color: #ff4040;
        color: #fff;
      }
    }
  }
}
</style>
