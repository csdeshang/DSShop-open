<template>
  <view class="coupon-page">


    <!-- 优惠券列表 -->
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <!-- 顶部切换栏 -->
      <view class="tabs">
        <view v-for="(tab, index) in tabs" :key="index" class="tab-item" :class="{ active: currentTab === index }"
          @click="switchTab(index)">
          {{ tab }}
        </view>
      </view>
      <view class="coupon-list">
        <view v-for="(item, index) in dataList" :key="index" class="coupon-item">
          <!-- 优惠券内容 -->
          <view class="coupon-content">
            <view class="left">
              <view class="amount" :class="{ 'discount': item.storeCoupon.coupon_type === 2 }">
                <text class="prefix" v-if="item.storeCoupon.coupon_type === 1">¥</text>
                <text class="value">{{ item.storeCoupon.discount_value }}</text>
                <text class="suffix" v-if="item.storeCoupon.coupon_type === 2">折</text>
              </view>
              <text class="condition">满{{ item.storeCoupon.min_spend }}元可用</text>
            </view>
            <view class="right">
              <text class="name">{{ item.storeCoupon.coupon_name }}</text>
              <text class="time">{{ formatTimeRange(item.storeCoupon.start_time, item.storeCoupon.end_time) }}</text>
              <view class="tag" :class="getStatusClass(item)">{{ getStatusText(item) }}</view>
            </view>
          </view>

          <!-- 优惠券底部 -->
          <view class="coupon-footer">
            <view class="store-info">
              <text class="label">领取时间:</text>
              <text class="value">{{ formatDate(item.create_at) }}</text>
            </view>
            <view class="action">
              <button class="btn" :class="getActionBtnClass(item)" @click="goToUse(item)">
                {{ item.status === 0 ? '去使用' : '已使用' }}
              </button>
            </view>
          </view>
        </view>
      </view>



    </z-paging>
  </view>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { getUserTblStoreCouponPages } from '@/home/api/user/userTblStoreCoupon';

// 分页组件引用
const paging = ref<any>(null)
const dataList = ref<any[]>([])

// 标签页定义
const tabs = ['全部', '未使用', '已使用', '已过期']
const currentTab = ref(0)

// 切换标签
const switchTab = (index: number) => {
  currentTab.value = index
  // 重新加载数据
  paging.value?.reload()
}

// 查询优惠券列表
const queryList = async (page_current: number, page_size: number) => {
  try {
    const params: any = {
      page_current,
      page_size,
    }

    // 根据标签添加状态筛选
    if (currentTab.value === 1) {
      params.status = 0 // 未使用
    } else if (currentTab.value === 2) {
      params.status = 1 // 已使用
    } else if (currentTab.value === 3) {
      params.status = 2 // 已过期，假设后端支持这个状态
    }

    const result = await getUserTblStoreCouponPages(params)
    paging.value?.complete(result.data.data)
  } catch (e) {
    console.log('加载优惠券列表失败', e)
    paging.value?.complete(false)
  }
}


// 格式化时间范围
const formatTimeRange = (startTime: string, endTime: string) => {
  // 简化日期显示，只显示年月日
  const start = startTime.split(' ')[0]
  const end = endTime.split(' ')[0]
  return `${start} - ${end}`
}

// 格式化日期
const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}

// 判断优惠券状态
const isExpired = (item: any) => {
  const now = new Date().getTime()
  const endTime = new Date(item.storeCoupon.end_time).getTime()
  return now > endTime
}

// 获取优惠券状态文本
const getStatusText = (item: any) => {
  if (item.status === 1) return '已使用'
  if (isExpired(item)) return '已过期'
  return '可使用'
}

// 获取状态样式类
const getStatusClass = (item: any) => {
  if (item.status === 1) return 'used'
  if (isExpired(item)) return 'expired'
  return 'valid'
}

// 获取按钮样式类
const getActionBtnClass = (item: any) => {
  if (item.status === 1) return 'used'
  if (isExpired(item)) return 'expired'
  return 'valid'
}

// 去使用优惠券
const goToUse = (item: any) => {
  if (item.status === 1 || isExpired(item)) return

  // 跳转到商城首页
  uni.switchTab({
    url: '/home/pages/index/index'
  })
}
</script>

<style lang="scss" scoped>
.coupon-page {
  background-color: #f8f8f8;
  min-height: 100vh;
}

.tabs {
  display: flex;
  background-color: #fff;
  padding: 0 20rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 10;

  .tab-item {
    flex: 1;
    height: 80rpx;
    line-height: 80rpx;
    text-align: center;
    font-size: 26rpx;
    color: #666;
    position: relative;

    &.active {
      color: #FF6034;
      font-weight: bold;

      &:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60rpx;
        height: 4rpx;
        background-color: #FF6034;
        border-radius: 2rpx;
      }
    }
  }
}

.coupon-list {
  padding: 20rpx;
}

.coupon-item {
  background: #fff;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  margin-bottom: 20rpx;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    left: 160rpx;
    top: 0;
    height: 100%;
    width: 1rpx;
    background: repeating-linear-gradient(to bottom, #eee 0, #eee 6rpx, transparent 6rpx, transparent 12rpx);
  }
}

.coupon-content {
  display: flex;
  padding: 30rpx;
  position: relative;

  .left {
    width: 160rpx;
    text-align: center;
    position: relative;

    .amount {
      font-weight: bold;
      color: #FF6034;
      display: flex;
      align-items: baseline;
      justify-content: center;

      .prefix {
        font-size: 28rpx;
      }

      .value {
        font-size: 48rpx;
      }

      .suffix {
        font-size: 24rpx;
        margin-left: 4rpx;
      }

      &.discount {
        color: #FF9500;
      }
    }

    .condition {
      font-size: 24rpx;
      color: #999;
      display: block;
      margin-top: 8rpx;
    }
  }

  .right {
    flex: 1;
    padding-left: 30rpx;

    .name {
      font-size: 32rpx;
      color: #333;
      font-weight: bold;
      display: block;
      margin-bottom: 10rpx;
    }

    .time {
      font-size: 24rpx;
      color: #999;
      display: block;
    }

    .tag {
      position: absolute;
      top: 0;
      right: 0;
      padding: 6rpx 16rpx;
      font-size: 20rpx;
      color: #fff;
      border-radius: 0 0 0 16rpx;

      &.valid {
        background-color: #FF6034;
      }

      &.used {
        background-color: #909399;
      }

      &.expired {
        background-color: #C0C4CC;
      }
    }
  }
}

.coupon-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20rpx 30rpx;
  border-top: 1px dashed #f0f0f0;

  .store-info {
    font-size: 24rpx;
    color: #999;

    .label {
      margin-right: 10rpx;
    }
  }

  .action {
    .btn {
      min-width: 140rpx;
      height: 60rpx;
      line-height: 60rpx;
      font-size: 24rpx;
      border-radius: 30rpx;
      padding: 0 30rpx;
      margin: 0;

      &.valid {
        background: linear-gradient(to right, #ff6b6b, #FF6034);
        color: #fff;
      }

      &.used {
        background-color: #909399;
        color: #fff;
      }

      &.expired {
        background-color: #f5f5f5;
        color: #999;
      }
    }
  }
}
</style>