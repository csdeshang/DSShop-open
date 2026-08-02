<template>
  <view class="points-container">
    <!-- 积分卡片 -->
    <view class="points-card">
      <view class="card-header">
        <image class="user-avatar" :src="formatImageUrl(userInfo.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
        <view class="user-info">
          <text class="user-name">{{ userInfo.nickname || '尊敬的用户' }}</text>
          <text class="user-id">ID: {{ userInfo.id }}</text>
        </view>
      </view>
      
      <view class="total-points">
        <view class="points-value">{{ userInfo.points }}</view>
        <view class="points-label">可用积分</view>
      </view>
      
      <view class="points-flow">
        <view class="flow-item">
          <view class="flow-value">{{ userInfo.points_in }}</view>
          <view class="flow-label">累计获得</view>
        </view>
        <view class="divider"></view>
        <view class="flow-item">
          <view class="flow-value">{{ userInfo.points_out }}</view>
          <view class="flow-label">累计使用</view>
        </view>
      </view>
    </view>
    
    <!-- 积分功能区 -->
    <view class="points-functions">
      <view class="function-title">积分功能</view>
      <view class="function-grid">
        <view class="function-item" @click="goToPage({url:'/home/pages/user/points/logs'})">
          <view class="function-icon points-detail"></view>
          <text class="function-name">积分明细</text>
        </view>
        <view class="function-item" @click="goToPage({url:'/home/pages/user/points-goods/order/index'})">
          <view class="function-icon points-task"></view>
          <text class="function-name">积分商品订单</text>
        </view>
        <view class="function-item" @click="goToPage({url:'/home/pages/points-goods/goods/index'})">
          <view class="function-icon points-mall"></view>
          <text class="function-name">积分商城</text>
        </view>
        <view class="function-item" @click="goToPage({url:'/home/pages/user/points/rules'})">
          <view class="function-icon points-rule"></view>
          <text class="function-name">积分规则</text>
        </view>
      </view>
    </view>
    
    <!-- 积分推荐活动 -->
    <view class="points-activities">
      <view class="activity-title">推荐活动</view>
      <view class="activity-list">
        <view class="activity-item">
          <image class="activity-image" src="/static/sign-in.png" mode="aspectFill"></image>
          <view class="activity-info">
            <text class="activity-name">每日签到</text>
            <text class="activity-desc">连续签到积分翻倍</text>
          </view>
          <button class="activity-btn" @click="goToPage({ url: '/home/pages/user/points/sign' })">去签到</button>
        </view>
        <view class="activity-item">
          <image class="activity-image" src="/static/invite.png" mode="aspectFill"></image>
          <view class="activity-info">
            <text class="activity-name">邀请好友</text>
            <text class="activity-desc">邀请好友得积分</text>
          </view>
          <button class="activity-btn">去邀请</button>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

import useUserStore from '@/stores/user'
import { onShow } from '@dcloudio/uni-app'
import { storeToRefs } from 'pinia'
import { goToPage } from '@/utils/util'

const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

onShow(() => {
  userStore.getUserInfoWithRoles(true)
})



</script>

<style scoped lang="scss">
.points-container {
  min-height: 100vh;
  background-color: #fff5f5;
  padding: 30rpx;
}

// 积分卡片
.points-card {
  background: linear-gradient(135deg, #ff3b30, #ff6b58);
  border-radius: 24rpx;
  padding: 40rpx 30rpx;
  color: #fff;
  box-shadow: 0 8rpx 16rpx rgba(255, 59, 48, 0.2);
  margin-bottom: 30rpx;
  
  .card-header {
    display: flex;
    align-items: center;
    margin-bottom: 30rpx;
    
    .user-avatar {
      width: 96rpx;
      height: 96rpx;
      border-radius: 48rpx;
      border: 4rpx solid rgba(255, 255, 255, 0.5);
      background-color: #f0f0f0;
    }
    
    .user-info {
      margin-left: 20rpx;
      
      .user-name {
        font-size: 32rpx;
        font-weight: 600;
        display: block;
        margin-bottom: 6rpx;
      }
      
      .user-id {
        font-size: 24rpx;
        opacity: 0.8;
      }
    }
  }
  
  .total-points {
    text-align: center;
    margin-bottom: 30rpx;
    
    .points-value {
      font-size: 72rpx;
      font-weight: 700;
      line-height: 1.2;
      text-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.15);
    }
    
    .points-label {
      font-size: 28rpx;
      opacity: 0.9;
    }
  }
  
  .points-flow {
    display: flex;
    border-top: 2rpx solid rgba(255, 255, 255, 0.2);
    padding-top: 30rpx;
    
    .flow-item {
      flex: 1;
      text-align: center;
      
      .flow-value {
        font-size: 36rpx;
        font-weight: 600;
        margin-bottom: 6rpx;
      }
      
      .flow-label {
        font-size: 24rpx;
        opacity: 0.8;
      }
    }
    
    .divider {
      width: 2rpx;
      background: rgba(255, 255, 255, 0.3);
      margin: 0 20rpx;
    }
  }
}

// 积分功能区
.points-functions {
  background-color: #fff;
  border-radius: 24rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  
  .function-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 20rpx;
    position: relative;
    padding-left: 20rpx;
    
    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 8rpx;
      height: 28rpx;
      background: #ff3b30;
      border-radius: 4rpx;
    }
  }
  
  .function-grid {
    display: flex;
    flex-wrap: wrap;
    
    .function-item {
      width: 25%;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20rpx 0;
      
      .function-icon {
        width: 80rpx;
        height: 80rpx;
        border-radius: 40rpx;
        margin-bottom: 12rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 59, 48, 0.1);
        position: relative;
        
        &::before {
          font-family: "uniicons";
          font-size: 40rpx;
          color: #ff3b30;
        }
        
        &.points-detail::before {
          content: "\e6a5"; // 使用uni-icons的明细图标
        }
        
        &.points-task::before {
          content: "\e644"; // 使用uni-icons的任务图标
        }
        
        &.points-mall::before {
          content: "\e67f"; // 使用uni-icons的商城图标
        }
        
        &.points-rule::before {
          content: "\e67c"; // 使用uni-icons的规则图标
        }
      }
      
      .function-name {
        font-size: 24rpx;
        color: #333;
      }
    }
  }
}

// 积分活动
.points-activities {
  background-color: #fff;
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  
  .activity-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 20rpx;
    position: relative;
    padding-left: 20rpx;
    
    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 8rpx;
      height: 28rpx;
      background: #ff3b30;
      border-radius: 4rpx;
    }
  }
  
  .activity-list {
    .activity-item {
      display: flex;
      align-items: center;
      padding: 20rpx 0;
      border-bottom: 2rpx solid #f5f5f5;
      
      &:last-child {
        border-bottom: none;
      }
      
      .activity-image {
        width: 100rpx;
        height: 100rpx;
        border-radius: 12rpx;
        background-color: #f0f0f0;
      }
      
      .activity-info {
        flex: 1;
        margin-left: 20rpx;
        
        .activity-name {
          font-size: 28rpx;
          font-weight: 500;
          color: #333;
          margin-bottom: 6rpx;
          display: block;
        }
        
        .activity-desc {
          font-size: 24rpx;
          color: #666;
        }
      }
      
      .activity-btn {
        font-size: 24rpx;
        color: #fff;
        background-color: #ff3b30;
        height: 60rpx;
        line-height: 60rpx;
        padding: 0 30rpx;
        border-radius: 30rpx;
        margin: 0;
      }
    }
  }
}
</style>
