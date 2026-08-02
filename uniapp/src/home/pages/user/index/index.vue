<template>
  <view class="user-page">
    <!-- 顶部个人信息区域 -->
    <view class="top-section" :style="topSectionStyle">
      <!-- 顶部操作按钮 -->
      <view class="top-actions">
        <view class="action-btn" @click="goToPage({ url: '/home/pages/user/setting/index' })">
          <uni-icons type="gear" size="22" color="#fff"></uni-icons>
        </view>
        <view class="action-btn" @click="goToPage({ url: '/shared/pages/system/notice/index' })">
          <uni-icons type="notification" size="22" color="#fff"></uni-icons>
        </view>
      </view>

      <!-- 用户信息 -->
      <view class="user-profile">
        <view class="avatar-container" @click="isLogin ? goToPage({ url: '/home/pages/user/setting/profile' }) : navigateToAuth('login', '/home/pages/user/index/index')">
          <image class="avatar" :src="formatImageUrl(userInfo.avatar, ThumbnailPresets.small, 'avatar') + '?' + time"
            mode="aspectFill" />
        </view>
        <view class="user-name" @click="isLogin ? goToPage({ url: '/home/pages/user/setting/profile' }) : navigateToAuth('login', '/home/pages/user/index/index')">{{ isLogin ? userInfo.nickname : '点击登录' }}</view>
        
        <!-- 会员等级显示 -->
        <view
          class="user-level"
          v-if="isLogin && userLevelInfo.level_progress && userLevelInfo.level_progress.current_level"
        >
          {{ userLevelInfo.level_progress.current_level.level_name }}
        </view>
        
        <!-- 删除原来的 user-tag -->
      </view>
    </view>

    <!-- 用户数据概览 - 修正遮挡问题 -->
    <view class="user-stats-container">
      <view class="user-stats">
        <view class="stat-item" @click="goToPage({ url: '/home/pages/user/balance/index' })">
          <view class="stat-value">{{ isLogin ? userInfo.balance : '0.00' }}</view>
          <view class="stat-label">我的余额</view>
        </view>
        <view class="stat-divider"></view>
        <view class="stat-item" @click="goToPage({ url: '/home/pages/user/points/index' })">
          <view class="stat-value">{{ isLogin ? userInfo.points : '0' }}</view>
          <view class="stat-label">我的积分</view>
        </view>
      </view>
    </view>

    <!-- 订单模块 -->
    <view class="card">
      <view class="card-header" @click="goToPage({ url: '/home/pages/user/order/index' })">
        <view class="header-title">
          <view class="title-icon"><uni-icons type="paperplane" size="18" color="#fff"></uni-icons></view>
          <text>我的订单</text>
        </view>
        <view class="header-action">
          <text>查看全部订单</text>
          <uni-icons type="right" size="14" color="#999"></uni-icons>
        </view>
      </view>

      <view class="order-icons">
        <view class="order-icon-item" @click="goToPage({ url: '/home/pages/user/order/index' , params: { tab_selected: 'pending' } })">
          <view class="icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_01.png')" class="icon-image" />
            <view class="badge" v-if="orderStatusCount.pending_count > 0">{{ orderStatusCount.pending_count }}</view>
          </view>
          <text class="icon-text">待付款</text>
        </view>
        <view class="order-icon-item" @click="goToPage({ url: '/home/pages/user/order/index', params: { tab_selected: 'accepted' } })">
          <view class="icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_02.png')" class="icon-image" />
            <view class="badge" v-if="orderStatusCount.paid_count > 0">{{ orderStatusCount.paid_count }}</view>
          </view>
          <text class="icon-text">待处理</text>
        </view>
        <view class="order-icon-item" @click="goToPage({ url: '/home/pages/user/order/index', params: { tab_selected: 'accepted' } })">
          <view class="icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_05.png')" class="icon-image" />
            <view class="badge" v-if="orderStatusCount.accepted_count > 0">{{ orderStatusCount.accepted_count }}</view>
          </view>
          <text class="icon-text">待收货</text>
        </view>
        <view class="order-icon-item" @click="goToPage({ url: '/home/pages/user/order/index' , params: { tab_selected: 'comment' } })">
          <view class="icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_04.png')" class="icon-image" />
            <view class="badge" v-if="orderStatusCount.comment_count > 0">{{ orderStatusCount.comment_count }}</view>
          </view>
          <text class="icon-text">待评价</text>
        </view>
        <view class="order-icon-item" @click="goToPage({ url: '/home/pages/user/refund/index' })">
          <view class="icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_order_11.png')" class="icon-image" />
            <view class="badge" v-if="orderStatusCount.refund_count > 0">{{ orderStatusCount.refund_count }}</view>
          </view>
          <text class="icon-text">退款/退货</text>
        </view>
      </view>
    </view>

    <!-- 会员中心 -->
    <view class="card">
      <view class="card-header">
        <view class="header-title">
          <view class="title-icon"><uni-icons type="star" size="18" color="#fff"></uni-icons></view>
          <text>会员中心</text>
        </view>
      </view>

      <view class="menu-grid">
        <view class="menu-item" v-for="(item, index) in memberTools" :key="index" @click="goToPage({ url: item.url })">
          <view class="menu-icon-wrapper">
            <image :src="fetchRemoteIconUrl(item.icon)" class="menu-icon" />
          </view>
          <text class="menu-text">{{ item.title }}</text>
        </view>
      </view>
    </view>

    <!-- 常用工具 -->
    <view class="card">
      <view class="card-header">
        <view class="header-title">
          <view class="title-icon"><uni-icons type="gear" size="18" color="#fff"></uni-icons></view>
          <text>常用工具</text>
        </view>
      </view>

      <view class="menu-grid">
        <view class="menu-item" v-for="(item, index) in commonTools" :key="index" @click="goToPage({ url: item.url })">
          <view class="menu-icon-wrapper">
            <image :src="fetchRemoteIconUrl(item.icon)" class="menu-icon" />
          </view>
          <text class="menu-text">{{ item.title }}</text>
        </view>

        <view class="menu-item" @click="goDistributor">
          <view class="menu-icon-wrapper">
            <image :src="fetchRemoteIconUrl('/uniapp/icons/64x64/icon_23.png')" class="menu-icon" />
          </view>
          <text class="menu-text">分销推广</text>
        </view>

      </view>
    </view>

    <!-- 我的活动 -->
    <view class="card">
      <view class="card-header">
        <view class="header-title">
          <view class="title-icon"><uni-icons type="flag" size="18" color="#fff"></uni-icons></view>
          <text>我的活动</text>
        </view>
      </view>

      <view class="menu-grid">
        <view class="menu-item" v-for="(item, index) in rolesTools" :key="index" @click="goToPage({ url: item.url })">
          <view class="menu-icon-wrapper">
            <image :src="fetchRemoteIconUrl(item.icon)" class="menu-icon" />
          </view>
          <text class="menu-text">{{ item.title }}</text>
        </view>
      </view>
    </view>

    <!-- 底部导航 -->
    <DsBottomNav />
  </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import DsBottomNav from '@/components/ds-bottom-nav/index.vue'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { goToPage, navigateToAuth } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets, fetchRemoteIconUrl } from '@/utils/image'
import { onShow } from '@dcloudio/uni-app'
import { getTblOrderStatusCount } from '@/home/api/tbl-order/tblOrder'
import { getUserLevelInfo, getUserLevelList } from '@/home/api/user/userLevel'

const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)
const time = ref(Date.now())


const orderStatusCount = ref({
  pending_count: 0,
  paid_count: 0,
  accepted_count: 0,
  comment_count: 0,
  refund_count: 0
})

const fetchTblOrderStatusCount = async () => {
  const res = await getTblOrderStatusCount()
  orderStatusCount.value = res.data
}

// 用户等级信息
const userLevelInfo = ref({})
const fetchUserLevelInfo = async () => {
  const res = await getUserLevelInfo()
  userLevelInfo.value = res.data
}


// 会员中心工具
const memberTools = reactive([
  {
    title: '我的钱包',
    icon: '/uniapp/icons/64x64/icon_01.png',
    url: '/home/pages/user/balance/index'
  },
  {
    title: '我的积分',
    icon: '/uniapp/icons/64x64/icon_02.png',
    url: '/home/pages/user/points/index'
  },
  {
    title: '退款退货',
    icon: '/uniapp/icons/64x64/icon_03.png',
    url: '/home/pages/user/refund/index'
  },
  {
    title: '开票记录',
    icon: '/uniapp/icons/64x64/icon_26.png',
    url: '/home/pages/user/order/invoice-list'
  },
  {
    title: '我的评价',
    icon: '/uniapp/icons/64x64/icon_04.png',
    url: '/home/pages/user/evaluate/index'
  },
  {
    title: '我的等级',
    icon: '/uniapp/icons/64x64/icon_15.png',
    url: '/home/pages/user/level/index'
  },
  {
    title: '成长值',
    icon: '/uniapp/icons/64x64/icon_05.png',
    url: '/home/pages/user/growth/logs'
  },
  {
    title: '我的发票',
    icon: '/uniapp/icons/64x64/icon_26.png',
    url: '/home/pages/user/invoice/index'
  }
])

// 常用工具
const commonTools = reactive([
  // {
  //   title: '我的好友',
  //   icon: '/uniapp/user/index/icon_friend_pre.png',
  //   url: '#'
  // },
  // {
  //   title: '聊天列表',
  //   icon: '/uniapp/user/index/icon_list_pre.png',
  //   url: '#'
  // },
  {
    title: '商品收藏',
    icon: '/uniapp/icons/64x64/icon_06.png',
    url: '/home/pages/user/favorites/goods'
  },
  // {
  //   title: '举报管理',
  //   icon: '/uniapp/user/index/icon_offence_pre.png',
  //   url: '#'
  // },
  // {
  //   title: '投诉管理',
  //   icon: '/uniapp/user/index/icon_propose_pre.png',
  //   url: '#'
  // },
  {
    title: '消息通知',
    icon: '/uniapp/icons/64x64/icon_27.png',
    url: '/shared/pages/system/notice/index'
  },
  // {
  //   title: '浏览记录',
  //   icon: '/uniapp/user/index/icon_footprint_pre.png',
  //   url: '#'
  // },
  // {
  //   title: '反馈意见',
  //   icon: '/uniapp/user/index/icon_feedback_pre.png',
  //   url: '#'
  // },
  {
    title: '联系客服',
    icon: '/uniapp/icons/64x64/icon_09.png',
    url: '/shared/pages/contact_us'
  },
  {
    title: '店铺优惠券',
    icon: '/uniapp/icons/64x64/icon_10.png',
    url: '/home/pages/user/store-coupon/index'
  }
])

// 角色中心
const rolesTools = reactive([
  {
    title: '骑手中心',
    icon: '/uniapp/icons/64x64/icon_12.png',
    // 此处是公共申请界面,如果需要多端管理，shared 为 公共页面
    // url: '/shared/pages/apply/rider'
    url: '/rider/pages/index/index'
  },
  {
    title: '师傅中心',
    icon: '/uniapp/icons/64x64/icon_14.png',
    // 此处是公共申请界面,如果需要多端管理，shared 为 公共页面
    // url: '/shared/pages/apply/technician'
    url: '/technician/pages/index/index'
  },

])

const goDistributor = () => {
  if (userInfo.value.is_distributor) {
    goToPage({ url: '/home/pages/distributor/index' })
  } else {
    goToPage({ url: '/home/pages/distributor/apply/index' })
  }
}





// 计算顶部区域的样式
const topSectionStyle = computed(() => {
    return {
      backgroundImage: `url(${fetchRemoteIconUrl('/uniapp/user_header_bg.png')})`
    }
})



onShow(async () => {
  await userStore.getUserInfoWithRoles(true)
  time.value = Date.now() // 更新时间戳避免头像缓存
  fetchTblOrderStatusCount()
  fetchUserLevelInfo()
})

</script>

<style lang="scss" scoped>
.user-page {
  min-height: 100vh;
  background-color: #f8f8f8;

}

// 顶部个人信息区域
.top-section {
  position: relative;
  height: 380rpx; // 可以再减小一些高度
  overflow: visible;
  background-color: #e93323;
  background-size: 100% auto;
  


  // 顶部操作按钮
  .top-actions {
    position: absolute;
    top: 40rpx;
    left: 0;
    right: 0;
    z-index: 10;
    padding: 0 30rpx;
    display: flex;
    justify-content: space-between;
    pointer-events: none; // 避免影响下层元素点击

    .action-btn {
      width: 64rpx;
      height: 64rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      backdrop-filter: blur(10px);
      transition: all 0.3s;
      pointer-events: auto; // 恢复按钮点击

      &:active {
        transform: scale(0.95);
        background: rgba(255, 255, 255, 0.3);
      }
    }
  }

  // 用户信息
  .user-profile {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 40rpx; // 调整顶部间距,为按钮留出空间
    margin-bottom: 80rpx; // 适当减小底部间距
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
      transition: all 0.3s;

      &:active {
        transform: scale(0.98);
      }

      .avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
    }

    .user-name {
      font-size: 32rpx;
      color: #fff;
      font-weight: 600;
      margin-bottom: 8rpx;
      text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
    }

    .user-level {
      font-size: 22rpx;
      color: #fff;
      background: rgba(255, 255, 255, 0.2);
      padding: 4rpx 20rpx;
      border-radius: 30rpx;
      margin-top: 8rpx;
      backdrop-filter: blur(4px);
    }
  }
}

// 用户数据统计区域
.user-stats-container {
  position: relative;
  height: 140rpx;
  margin-top: -70rpx;
  margin-bottom: 20rpx;
  z-index: 5;
}

.user-stats {
  margin: 0 20rpx;
  height: 140rpx;
  background: #fff;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 16rpx rgba(255, 65, 65, 0.12);
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;

  &::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 200%;
    height: 200%;
    border: 2rpx solid #f5f5f5;
    transform: scale(0.5);
    transform-origin: 0 0;
    box-sizing: border-box;
    border-radius: 32rpx;
    pointer-events: none;
  }

  .stat-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;

    &:not(:last-child)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 2rpx;
      height: 50rpx;
      background: #f5f5f5;
    }

    .stat-value {
      font-size: 36rpx;
      color: #FF4141;
      font-weight: 600;
      margin-bottom: 8rpx;
    }

    .stat-label {
      font-size: 26rpx;
      color: #666;
    }
  }
}

// 卡片样式优化
.card {
  margin: 20rpx 20rpx;
  background: #fff;
  border-radius: 16rpx;
  position: relative;
  overflow: hidden;

  &::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 200%;
    height: 200%;
    border: 2rpx solid #f5f5f5;
    transform: scale(0.5);
    transform-origin: 0 0;
    box-sizing: border-box;
    border-radius: 32rpx;
    pointer-events: none;
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24rpx 30rpx;
    position: relative;

    &::after {
      content: '';
      position: absolute;
      left: 30rpx;
      right: 30rpx;
      bottom: 0;
      height: 2rpx;
      background: #f5f5f5;
      transform: scaleY(0.5);
    }

    .header-title {
      display: flex;
      align-items: center;

      .title-icon {
        width: 40rpx;
        height: 40rpx;
        border-radius: 20rpx;
        background: linear-gradient(135deg, #FF4141, #FF6F6F);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12rpx;
        box-shadow: 0 4rpx 8rpx rgba(255, 65, 65, 0.2);
      }

      text {
        font-size: 30rpx;
        font-weight: 600;
        color: #333;
        background: linear-gradient(90deg, #333, #666);
        -webkit-background-clip: text;
        color: transparent;
      }
    }

    .header-action {
      display: flex;
      align-items: center;

      text {
        font-size: 26rpx;
        color: #999;
        margin-right: 6rpx;
      }
    }
  }
}

// 订单图标优化
.order-icons {
  display: flex;
  padding: 30rpx 20rpx;

  .order-icon-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;

    .icon-wrapper {
      position: relative;
      width: 96rpx;
      height: 96rpx;
      display: flex;
      align-items: center;
      justify-content: center;

      .icon-image {
        width: 66rpx;
        height: 66rpx;
      }

      .badge {
        position: absolute;
        top: -6rpx;
        right: -6rpx;
        min-width: 36rpx;
        height: 36rpx;
        line-height: 36rpx;
        padding: 0 8rpx;
        background: linear-gradient(135deg, #FF4141, #FF6F6F);
        color: #fff;
        font-size: 22rpx;
        border-radius: 18rpx;
        text-align: center;
        box-shadow: 0 4rpx 8rpx rgba(255, 65, 65, 0.3);
      }
    }

    .icon-text {
      font-size: 26rpx;
      color: #666;
      margin-top: 16rpx;
    }
  }
}

// 菜单网格优化
.menu-grid {
  display: flex;
  flex-wrap: wrap;
  padding: 10rpx 0;

  .menu-item {
    width: 25%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20rpx 0;

    .menu-icon-wrapper {
      width: 88rpx;
      height: 88rpx;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12rpx;


      .menu-icon {
        width: 56rpx;
        height: 56rpx;
      }
    }

    .menu-text {
      font-size: 24rpx;
      color: #666;
    }
  }
}
</style>