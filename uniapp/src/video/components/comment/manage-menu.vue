<template>
  <view v-if="visible" class="manage-menu-mask" @click="handleClose">
    <!-- 底部弹出菜单 -->
    <view class="manage-menu" :class="[menuShow ? 'menu-show' : '']" @click.stop>
      <!-- 头部信息 -->
      <view class="menu-header">
        <view class="comment-preview">
          <image class="comment-avatar" :src="formatImageUrl(comment?.user?.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
          <view class="comment-info">
            <text class="comment-user">{{ comment?.user?.nickname || comment?.user?.username || '用户' }}</text>
            <text class="comment-text">{{ comment?.comment_content }}</text>
          </view>
        </view>
      </view>

      <!-- 操作列表 -->
      <view class="menu-actions">
        <!-- 置顶/取消置顶 -->
        <view class="action-item" @click="handleToggleField(comment, 'is_top')">
          <view class="action-icon">
            <uni-icons :type="comment?.is_top ? 'flag-filled' : 'flag'" size="24" 
                      :color="comment?.is_top ? '#ff6b35' : '#666'" />
          </view>
          <text class="action-text">{{ comment?.is_top ? '取消置顶' : '置顶评论' }}</text>
        </view>

        <!-- 隐藏 -->
        <view class="action-item" @click="handleToggleField(comment, 'is_show')">
          <view class="action-icon">
            <uni-icons type="eye-slash" size="24" color="#666" />
          </view>
          <text class="action-text">隐藏评论</text>
        </view>

        <!-- 作者回复 -->
        <view class="action-item" @click="handleAuthorReply">
          <view class="action-icon">
            <uni-icons type="chatbubble" size="24" color="#666" />
          </view>
          <text class="action-text">作者回复</text>
        </view>



        <!-- 复制内容 -->
        <view class="action-item" @click="handleCopyContent">
          <view class="action-icon">
            <uni-icons type="paperplane" size="24" color="#666" />
          </view>
          <text class="action-text">复制内容</text>
        </view>
      </view>

      <!-- 取消按钮 -->
      <view class="menu-cancel" @click="handleClose">
        <text class="cancel-text">取消</text>
      </view>
    </view>


  </view>
</template>

<script setup lang="ts">
import { ref, reactive, nextTick, watch } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { toggleVideoCommentField } from '@/video/api/blogger/videoComment'

// 定义评论数据接口
interface CommentItem {
  id: number
  comment_content: string
  is_top?: number
  is_deleted?: number
  user: {
    id: number
    nickname?: string
    username?: string
    avatar?: string
  }
}

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  comment: {
    type: Object as () => CommentItem | null,
    default: null
  }
})

// Emits - 简化事件，只需要关闭和刷新
const emit = defineEmits(['close', 'refresh'])

// 响应式数据
const menuShow = ref(false)

// 监听visible变化，控制动画
watch(() => props.visible, (newVal) => {
  if (newVal) {
    // 显示菜单时，先显示遮罩，再显示菜单（动画效果）
    nextTick(() => {
      menuShow.value = true
    })
  } else {
    // 隐藏菜单时，先隐藏菜单，再隐藏遮罩
    menuShow.value = false
  }
})


// 切换字段状态
const handleToggleField = async (row: any, field: string) => {
    if (!row) return
    
    try {
        // 添加震动反馈
        uni.vibrateShort()
        
        // 显示加载状态
        uni.showLoading({ title: '处理中...' })
        
        // 调用API
        const result = await toggleVideoCommentField({
            id: row.id,
            field: field
        })
        
        uni.hideLoading()
        
        if (result.code === 10000) {
            // 根据字段类型显示不同的成功提示
            let successMessage = '操作成功'
            if (field === 'is_top') {
                successMessage = row.is_top ? '取消置顶成功' : '置顶成功'
            } else if (field === 'is_show') {
                successMessage = '隐藏成功'
            }
            
            uni.showToast({
                title: successMessage,
                icon: 'success'
            })
            
            // 刷新数据
            emit('refresh')
            
            // 关闭菜单
            handleClose()
        } else {
            throw new Error(result.message || '操作失败')
        }
    } catch (error: any) {
        uni.hideLoading()
        
        if (error !== 'cancel') {
            console.error('操作失败', error)
            uni.showToast({
                title: error.message || '操作失败',
                icon: 'none'
            })
        }
    }
}

// 关闭菜单
const handleClose = () => {
  menuShow.value = false
  setTimeout(() => {
    emit('close')
  }, 200)
}

// 作者回复
const handleAuthorReply = () => {
  if (!props.comment) return
  
  // 通知父组件进入回复模式
  emit('refresh', { action: 'reply', comment: props.comment })
  handleClose()
}

// 复制内容
const handleCopyContent = () => {
  if (!props.comment?.comment_content) return

  uni.setClipboardData({
    data: props.comment.comment_content,
    success: () => {
      uni.showToast({
        title: '内容已复制',
        icon: 'success'
      })
    },
    fail: () => {
      uni.showToast({
        title: '复制失败',
        icon: 'none'
      })
    }
  })

  handleClose()
}


</script>

<style scoped lang="scss">
.manage-menu-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  align-items: flex-end;
  animation: fadeIn 0.2s ease-out;
}

.manage-menu {
  width: 100%;
  background-color: #fff;
  border-top-left-radius: 24rpx;
  border-top-right-radius: 24rpx;
  transform: translateY(100%);
  transition: transform 0.3s ease-out;

  &.menu-show {
    transform: translateY(0);
  }
}

.menu-header {
  padding: 32rpx;
  border-bottom: 1rpx solid #f0f0f0;

  .comment-preview {
    display: flex;
    align-items: flex-start;

    .comment-avatar {
      width: 60rpx;
      height: 60rpx;
      border-radius: 50%;
      margin-right: 20rpx;
      flex-shrink: 0;
    }

    .comment-info {
      flex: 1;
      min-width: 0;

      .comment-user {
        display: block;
        font-size: 28rpx;
        font-weight: 600;
        color: #333;
        margin-bottom: 8rpx;
      }

      .comment-text {
        display: block;
        font-size: 26rpx;
        color: #666;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
      }
    }
  }
}

.menu-actions {
  padding: 16rpx 0;

  .action-item {
    display: flex;
    align-items: center;
    padding: 24rpx 32rpx;
    transition: background-color 0.2s;

    &:active {
      background-color: #f5f5f5;
    }

    .action-icon {
      width: 56rpx;
      height: 56rpx;
      border-radius: 50%;
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 24rpx;
    }

    .action-text {
      font-size: 30rpx;
      color: #333;
      font-weight: 500;
    }
  }
}

.menu-cancel {
  padding: 24rpx 32rpx 40rpx;
  border-top: 16rpx solid #f8f9fa;

  .cancel-text {
    display: block;
    text-align: center;
    font-size: 32rpx;
    color: #666;
    font-weight: 500;
    padding: 24rpx 0;
  }
}



@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
</style>
