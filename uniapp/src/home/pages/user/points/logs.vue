<template>
  <view>
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <view class="points-list">
        <view class="points-card" v-for="(item, index) in dataList" :key="item.id">
          <view class="card-header">
            <view class="user-info">
              <text class="username">{{ item.user.nickname }}</text>
            </view>
            <view class="amount-badge" :class="item.change_mode === 1 ? 'increase' : 'decrease'">
              {{ item.change_mode === 1 ? '+' : '-' }}{{ item.change_num }}
            </view>
          </view>

          <view class="card-content">
            <view class="transaction-type">
              <view class="type-badge" :class="'type-' + item.change_type">
                {{ item.change_type_desc }}
              </view>
              <text class="transaction-desc">{{ item.change_desc }}</text>
            </view>

            <view class="transaction-details">
              <view class="detail-row">
                <uni-icons type="calendar" size="16" color="#999"></uni-icons>
                <text class="time">{{ item.create_at }}</text>
              </view>

              <view class="balance-change">
                <text class="balance-label">积分变化：</text>
                <text class="balance-value">{{ item.before_num }} → {{ item.after_num }}</text>
              </view>
            </view>
          </view>
        </view>
      </view>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getUserPointsLogPages } from '@/home/api/user/user'
const paging = ref(null)
const dataList = ref([])

const queryList = async (page_current: number, page_size: number) => {
  try {
    const result = await getUserPointsLogPages({
      page_current,
      page_size,
    })
    paging.value.complete(result.data.data)
  } catch (e) {
    console.log('下拉加载', e)
    paging.value.complete(false)
  }
}
</script>

<style scoped lang="scss">
.points-list {
  padding: 20rpx;
}

.points-card {
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20rpx;
    border-bottom: 1rpx solid #f5f5f5;
    padding-bottom: 16rpx;

    .user-info {
      display: flex;
      align-items: center;

      .username {
        font-size: 28rpx;
        color: #333;
      }
    }

    .amount-badge {
      font-size: 32rpx;
      font-weight: bold;

      &.increase {
        color: #07C160;
      }

      &.decrease {
        color: #FF2D55;
      }
    }
  }

  .card-content {
    .transaction-type {
      display: flex;
      align-items: center;
      margin-bottom: 16rpx;

      .type-badge {
        font-size: 24rpx;
        padding: 4rpx 12rpx;
        border-radius: 20rpx;
        margin-right: 12rpx;

        &.type-1 {
          color: #07C160;
          background: #E7F7EF;
        }

        &.type-2 {
          color: #FF9500;
          background: #FFF4E5;
        }

        &.type-3 {
          color: #10AEFF;
          background: #E6F7FF;
        }
      }

      .transaction-desc {
        font-size: 26rpx;
        color: #333;
      }
    }

    .transaction-details {
      .detail-row {
        display: flex;
        align-items: center;
        margin-bottom: 12rpx;
        font-size: 26rpx;

        .time {
          color: #999;
          margin-left: 8rpx;
        }
      }

      .balance-change {
        display: flex;
        font-size: 26rpx;

        .balance-label {
          color: #999;
          margin-right: 10rpx;
        }

        .balance-value {
          color: #333;
        }
      }
    }
  }
}
</style>