<template>
  <view class="distributor-container">
    <!-- 顶部个人信息区域 -->
    <view class="top-section">
      <view class="top-bg">
        <image mode="aspectFill" :src="fetchRemoteIconUrl('/uniapp/user/index/bg_wode_com.png')" class="bg-image" />
        <view class="top-overlay"></view>
      </view>

      <!-- 用户信息 -->
      <view class="user-profile">
        <view class="avatar-container">
          <image v-if="isLogin && userInfo.avatar" class="avatar" :src="formatImageUrl(userInfo.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
          <view v-else class="avatar-placeholder">
            <uni-icons type="person" size="32" color="#fff"></uni-icons>
          </view>
        </view>
        <view class="user-name">{{ isLogin ? userInfo.nickname : '点击登录' }}</view>
        <view class="user-tag" v-if="isLogin">分销商</view>
      </view>
    </view>

    <!-- 佣金数据卡片 -->
    <view class="balance-cards">
      <view class="balance-card" @click="goToPage({url: '/home/pages/distributor/balance/logs'})">
        <view class="card-title">可用佣金</view>
        <view class="card-amount">¥{{ userInfo.distributor_balance }}</view>
      </view>
      <view class="balance-card" @click="goToPage({url: '/home/pages/distributor/balance/logs'})">
        <view class="card-title">佣金总收入</view>
        <view class="card-amount">¥{{ userInfo.distributor_balance_in }}</view>
      </view>
      <view class="balance-card" @click="goToPage({url: '/home/pages/distributor/balance/logs'})">
        <view class="card-title">佣金总支出</view>
        <view class="card-amount">¥{{ userInfo.distributor_balance_out }}</view>
      </view>
    </view>
    
    <!-- 提现按钮 -->
    <view class="withdraw-section">
      <button class="withdraw-btn" @click="distributorBalanceWithdrawal.open()">转至钱包余额</button>
      <button class="withdraw-btn" @click="goToPage({url: '/home/pages/user/balance/index'})">查看钱包</button>
    </view>



    <!-- 分销数据统计 -->
    <view class="stat-section">
      <view class="section-title">分销数据</view>
      <view class="stat-grid">
        <view class="stat-item" @click="goToPage({url: '/home/pages/distributor/order/index'})">
          <view class="stat-value">{{ distributorStat.order_count }}</view>
          <view class="stat-label">订单总数</view>
        </view>
        <view class="stat-item" @click="goToPage({url: '/home/pages/distributor/order/index'})">
          <view class="stat-value">¥{{ distributorStat.order_amount }}</view>
          <view class="stat-label">订单总金额</view>
        </view>
        <view class="stat-item" @click="goToPage({url: '/home/pages/distributor/order/index'})">
          <view class="stat-value">{{ distributorStat.order_wait_count }}</view>
          <view class="stat-label">待结算订单</view>
        </view>
        <view class="stat-item" @click="goToPage({url: '/home/pages/distributor/order/index'})">
          <view class="stat-value">¥{{ distributorStat.order_wait_commission }}</view>
          <view class="stat-label">待结算佣金</view>
        </view>
      </view>
    </view>

    <!-- 分销团队 -->
    <view class="team-section">
      <view class="section-title">分销团队</view>
      <view class="team-stats">
        <view class="team-item" @click="goToPage({url: '/home/pages/distributor/relation/index'})">
          <view class="team-value">{{ distributorStat.parent1_count }}</view>
          <view class="team-label">一级分销员</view>
        </view>
        <view class="team-item" @click="goToPage({url: '/home/pages/distributor/relation/index'})">
          <view class="team-value">{{ distributorStat.parent2_count }}</view>
          <view class="team-label">二级分销员</view>
        </view>
      </view>
    </view>


    <!-- 分享按钮 -->
    <view class="share-section">
      <button class="ds-btn-lg ds-btn-orange" @click="openSharePopup">
        <uni-icons type="redo" size="20" color="#fff" style="margin-right: 12rpx;"></uni-icons>
        推广分享
      </button>
    </view>

    <!-- 分享组件 -->
    <SharePopup 
      ref="sharePopupRef"
      :share-url="getReferrerUrl()"
      title="邀请您成为我的分销员"
      :content="`一起赚钱！邀请码：${userInfo.id}`"
    />
    
    <DistributorBalanceWithdrawal ref="distributorBalanceWithdrawal" />
  </view>
</template>

<script setup lang="ts">
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { onShow } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets, fetchRemoteIconUrl } from '@/utils/image'
import { ref } from 'vue'
import { getDistributorStatistics } from '@/home/api/distributor/distributor'


import { useConfigStore } from '@/stores/modules/config';

// 网站配置
const configStore = useConfigStore();
// 获取网站配置
const websiteConfig = ref<Record<string, string>>({});
async function fetchWebsiteConfig() {
	websiteConfig.value = await configStore.fetchConfigsByType('website');
}


const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)

// 获取分销商统计信息
const distributorStat = ref({
  // 订单总数
  order_count: 0,
  // 订单总金额
  order_amount: 0,
  // 待结算订单数
  order_wait_count: 0,
  // 待结算订单佣金
  order_wait_commission: 0,
  // 一级分销员数量
  parent1_count: 0,
  // 二级分销员数量
  parent2_count: 0,
})

async function fetchDistributorStat() {
  const res = await getDistributorStatistics()
  distributorStat.value = res.data
}



// 佣金转入余额
const distributorBalanceWithdrawal = ref()
// @ts-ignore
import DistributorBalanceWithdrawal from './balance/withdrawal.vue'

// 分享弹窗
const sharePopupRef = ref()
// @ts-ignore
import SharePopup from '@/home/components/share/SharePopup.vue'

// 获取邀请链接
const getReferrerUrl = () => {
  if (!userInfo.value.id) return ''
  return `${websiteConfig.value.h5_url}?referrer_id=${userInfo.value.id}`
}

// 打开分享弹窗
const openSharePopup = () => {
  sharePopupRef.value?.open()
}



onShow(async () => {
  await userStore.getUserInfoWithRoles(true)
  // 如果用户不是分销商，则跳转到分销商申请页面
  if (userInfo.value.is_distributor == 0) {
    goToPage({url: '/home/pages/distributor/apply/index'})
    return
  }
  fetchDistributorStat()
  fetchWebsiteConfig()
})
</script>

<style scoped lang="scss">
.distributor-container {
  min-height: 100vh;
  background-color: #f8f8f8;
}

// 顶部个人信息区域
.top-section {
  position: relative;
  height: 380rpx;
  overflow: visible;

  .top-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;

    .bg-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.12;
    }

    .top-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #FF4141 0%, #FF6F6F 100%);
      z-index: 2;
    }
  }

  .user-profile {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 40rpx;
    margin-bottom: 80rpx;
    z-index: 3;

    .avatar-container {
      width: 120rpx;
      height: 120rpx;
      border-radius: 60rpx;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
      overflow: hidden;
      margin-bottom: 16rpx;
      border: 4rpx solid rgba(255, 255, 255, 0.6);

      .avatar {
        width: 100%;
        height: 100%;
      }
    }

    .user-name {
      font-size: 32rpx;
      color: #fff;
      font-weight: 600;
      margin-bottom: 8rpx;
      text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
    }

    .user-tag {
      font-size: 22rpx;
      color: #fff;
      background: rgba(255, 255, 255, 0.2);
      padding: 4rpx 20rpx;
      border-radius: 30rpx;
      backdrop-filter: blur(4px);
    }
  }
}

.balance-cards {
  display: flex;
  justify-content: space-between;
  margin: -70rpx 20rpx 30rpx;
  position: relative;
  z-index: 5;

  
  .balance-card {
    flex: 1;
    background: #fff;
    border-radius: 16rpx;
    padding: 24rpx;
    margin: 0 10rpx;
    box-shadow: 0 4rpx 16rpx rgba(255, 65, 65, 0.12);
    
    .card-title {
      font-size: 28rpx;
      color: #666;
    }
    
    .card-amount {
      font-size: 36rpx;
      font-weight: bold;
      color: #FF4141;
      margin-top: 10rpx;
    }
  }
}

// 提现按钮区域
.withdraw-section {
  display: flex;
  justify-content: space-between;
  margin: 0 20rpx 30rpx;
  
  .withdraw-btn {
    width: 48%;
    height: 80rpx;
    font-size: 30rpx;
    font-weight: bold;
    border-radius: 45rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
    
    &:first-child {
      background: linear-gradient(135deg, #FF4141 0%, #FF6F6F 100%);
      color: #fff;
      box-shadow: 0 4rpx 16rpx rgba(255, 65, 65, 0.3);
      
      &:active {
        background: linear-gradient(135deg, #E53935 0%, #EF5350 100%);
        transform: scale(0.98);
      }
    }
    
    &:last-child {
      background: #fff;
      color: #FF4141;
      border: 2rpx solid #FF4141;
      box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
      
      &:active {
        background: #FFEBEE;
        transform: scale(0.98);
      }
    }
  }
}

// 分享按钮区域
.share-section {
  margin:  20rpx ;
}

.section-title {
  font-size: 32rpx;
  font-weight: bold;
  color: #333;
  margin-bottom: 20rpx;
  padding-left: 20rpx;
  border-left: 8rpx solid #FF4141;
}

.stat-section {
  background: #fff;
  border-radius: 16rpx;
  padding: 30rpx;
  margin: 0 20rpx 30rpx;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
  
  .stat-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20rpx;
    
    .stat-item {
      background: #f8f9fc;
      padding: 20rpx;
      border-radius: 12rpx;
      text-align: center;
      
      .stat-value {
        font-size: 36rpx;
        font-weight: bold;
        color: #FF4141;
      }
      
      .stat-label {
        font-size: 26rpx;
        color: #666;
        margin-top: 8rpx;
      }
    }
  }
}



.team-section {
  background: #fff;
  border-radius: 16rpx;
  padding: 30rpx;
  margin: 0 20rpx;
  box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
  
  .team-stats {
    display: flex;
    justify-content: space-around;
    
    .team-item {
      text-align: center;
      
      .team-value {
        font-size: 48rpx;
        font-weight: bold;
        color: #FF4141;
      }
      
      .team-label {
        font-size: 28rpx;
        color: #666;
        margin-top: 10rpx;
      }
    }
  }
}
</style>
