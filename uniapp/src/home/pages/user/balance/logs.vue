<template>
  <view>
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <view class="balance-list">
        <view class="balance-item" v-for="(item, index) in dataList" :key="item.id">
          <view class="item-header">
            <view class="type-badge" :class="'type-' + item.change_type">
              {{ item.change_type_desc }}
            </view>
            <view class="amount" :class="item.change_mode === 1 ? 'increase' : 'decrease'">
              {{ item.change_mode === 1 ? '+' : '-' }}{{ item.change_amount }}
            </view>
          </view>

          <view class="item-body">
            <view class="info-row">
              <text class="label">描述：</text>
              <text class="value">{{ item.change_desc }}</text>
            </view>

            <view class="info-row">
              <text class="label">时间：</text>
              <text class="value">{{ item.create_at }}</text>
            </view>

            <view class="balance-row">
              <text class="label">余额：</text>
              <text class="value">{{ item.before_balance }} → {{ item.after_balance }}</text>
            </view>
          </view>
        </view>
      </view>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getUserBalanceLogPages } from '@/home/api/user/user'
const paging = ref(null)
const dataList = ref([])

const queryList = async (page_current: number, page_size: number) => {
  try {
    const result = await getUserBalanceLogPages({
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
.balance-list {
  padding: 20rpx;
}

.balance-item {
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

  .item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20rpx;
    padding-bottom: 16rpx;
    border-bottom: 1rpx solid #f5f5f5;

    .type-badge {
      font-size: 26rpx;
      padding: 4rpx 12rpx;
      border-radius: 20rpx;
      background: #f0f7ff;
      color: #1890ff;

      &.type-refund {
        background: #f6ffed;
        color: #52c41a;
      }

      &.type-order {
        background: #fff7e6;
        color: #fa8c16;
      }
    }

    .amount {
      font-size: 32rpx;
      font-weight: bold;

      &.increase {
        color: #FF2D55;
      }

      &.decrease {
        color: #07C160;
      }
    }
  }

  .item-body {
    .info-row {
      display: flex;
      margin-bottom: 12rpx;
      font-size: 26rpx;

      .label {
        color: #999;
        margin-right: 10rpx;
        min-width: 80rpx;
      }

      .value {
        color: #333;
        flex: 1;
      }
    }

    .balance-row {
      display: flex;
      margin-top: 16rpx;
      padding-top: 16rpx;
      border-top: 1rpx dashed #eee;
      font-size: 26rpx;

      .label {
        color: #999;
        margin-right: 10rpx;
      }

      .value {
        color: #666;
      }
    }
  }
}
</style>