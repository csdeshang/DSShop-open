<template>
  <view class="share-container">
    <!-- 分享选项弹窗 -->
    <uni-popup ref="popupRef" type="bottom" :safe-area="false">
      <view class="share-popup-content">
        <!-- 头部 -->
        <view class="popup-header">
          <text class="popup-title">分享</text>
          <view class="close-btn" @click="close">
            <uni-icons type="close" size="20" color="#999"></uni-icons>
          </view>
        </view>
        
        <!-- 分享选项 -->
        <view class="share-options">
          <view class="share-grid">
            <view class="share-item" @click="copyLink">
              <view class="share-icon">
                <uni-icons type="redo" size="24" color="#ff6b6b"></uni-icons>
              </view>
              <text class="share-label">复制链接</text>
            </view>
            
            <view class="share-item" @click="showQrCode">
              <view class="share-icon">
                <uni-icons type="scan" size="24" color="#409eff"></uni-icons>
              </view>
              <text class="share-label">生成二维码</text>
            </view>
          </view>
        </view>
        
        <!-- 取消按钮 -->
        <view class="cancel-btn" @click="close">
          <text>取消</text>
        </view>
      </view>
    </uni-popup>

    <!-- 二维码弹窗 -->
    <uni-popup ref="qrPopupRef" type="center" :safe-area="false">
      <view class="qr-popup-content">
        <!-- 二维码图片 -->
        <image :src="qrCodeUrl" class="qr-code-image" mode="aspectFit" @click="closeQrCode" />
        
        <!-- 提示文字 -->
        <text class="qr-tip">长按保存二维码图片</text>
      </view>
    </uni-popup>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
// @ts-ignore
import { goToPage } from '@/utils/util'
// @ts-ignore
import { qrcodeShow } from '@/api/qrcode'

const props = defineProps({
  shareUrl: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: '推荐给你'
  },
  content: {
    type: String,
    default: '发现好内容，分享给你！'
  }
})

const popupRef = ref()
const qrPopupRef = ref()
const qrCodeUrl = ref('')

// 打开弹窗
const open = () => {
  if (!props.shareUrl) {
    uni.showToast({
      title: '分享链接不能为空',
      icon: 'none'
    })
    return
  }
  popupRef.value?.open()
}

// 关闭弹窗
const close = () => {
  popupRef.value?.close()
}

// 复制链接
const copyLink = () => {
  close()
  uni.setClipboardData({
    data: props.shareUrl,
    success: () => {
      uni.showToast({
        title: '链接已复制',
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
}

// 显示二维码弹窗
const showQrCode = () => {
  close()
  generateQrCode()
  qrPopupRef.value?.open()
}

// 关闭二维码弹窗
const closeQrCode = () => {
  qrPopupRef.value?.close()
  qrCodeUrl.value = ''
}

// 生成二维码
const generateQrCode = () => {
  // 使用项目现有的二维码API
  qrCodeUrl.value = qrcodeShow({
    content: props.shareUrl,
    size: 300
  })
}

// 暴露方法
defineExpose({
  open,
  close
})
</script>

<style scoped lang="scss">
.share-popup-content {
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  overflow: hidden;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 30rpx;
  border-bottom: 2rpx solid #f0f0f0;
  
  .popup-title {
    font-size: 32rpx;
    font-weight: 600;
    color: #333;
  }
  
  .close-btn {
    width: 40rpx;
    height: 40rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20rpx;
    
    &:active {
      background: #f5f5f5;
    }
  }
}

.share-options {
  padding: 40rpx 30rpx;
  
  .share-grid {
    display: flex;
    justify-content: center;
    gap: 80rpx;
    
    .share-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      
      &:active {
        opacity: 0.7;
      }
      
      .share-icon {
        width: 100rpx;
        height: 100rpx;
        border-radius: 20rpx;
        background: #f8f9fc;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12rpx;
        border: 2rpx solid #f0f0f0;
      }
      
      .share-label {
        font-size: 24rpx;
        color: #666;
        text-align: center;
      }
    }
  }
}

.cancel-btn {
  height: 88rpx;
  background: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30rpx;
  color: #666;
  border-top: 2rpx solid #f0f0f0;
  
  &:active {
    background: #e8e8e8;
  }
}

// 二维码弹窗样式
.qr-popup-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #fff;
  border-radius: 24rpx;
  padding: 40rpx;
  
  .qr-code-image {
    width: 400rpx;
    height: 400rpx;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
  }
  
  .qr-tip {
    font-size: 26rpx;
    color: #999;
    text-align: center;
    margin-top: 30rpx;
  }
}
</style> 