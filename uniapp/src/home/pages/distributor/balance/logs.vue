<template>
  <view>
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <!-- 类型选项卡 - 使用z-paging的fixed特性 -->
      <template v-slot:top>
        <view class="type-tabs">
          <!-- 全部选项 -->
          <view class="tab-item" :class="{ active: currentType === '' }" @click="switchType('')">
            全部
          </view>

          <!-- 动态渲染类型选项 -->
          <view v-for="option in change_type_options" :key="option.value" class="tab-item"
            :class="{ active: currentType === String(option.value) }" @click="switchType(String(option.value))">
            {{ option.label }}
          </view>
        </view>
      </template>

      <view class="balance-list">
        <view class="balance-item" v-for="item in dataList" :key="item.id">
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
import { getDistributorBalanceLogPages } from '@/home/api/distributor/distributor'
import { useEnum } from '@/hooks/useEnum'

// 使用枚举 Hook获取变动类型选项
const { options: change_type_options } = useEnum('default.distributor_balance_log.change_type')

// 定义余额记录类型
interface BalanceLogItem {
  id: number;
  change_type: string;
  change_type_desc: string;
  change_mode: number;
  change_amount: number;
  change_desc: string;
  before_balance: number;
  after_balance: number;
  create_at: string;
}

// 当前选中的类型选项卡 (空字符串表示全部)
const currentType = ref('')

// 切换类型选项卡
const switchType = (type: string) => {
  if (currentType.value !== type) {
    currentType.value = type
    // 使用z-paging提供的刷新方法
    if (paging.value) {
      paging.value.reload()
    }
  }
}

const paging = ref<any>(null)
const dataList = ref<BalanceLogItem[]>([])

const queryList = async (page_current: number, page_size: number) => {
  try {
    // 根据当前选中的类型获取对应数据
    const params: any = {
      page_current,
      page_size,
    }

    // 如果选择了特定类型（非全部）
    if (currentType.value !== '') {
      params.change_type = currentType.value
    }

    const result = await getDistributorBalanceLogPages(params)
    paging.value?.complete(result.data.data)
  } catch (e) {
    console.log('下拉加载', e)
    paging.value?.complete(false)
  }
}
</script>

<style scoped lang="scss">
.type-tabs {
  display: flex;
  background-color: #fff;
  padding: 20rpx 0;
  border-radius: 10rpx;
  margin-bottom: 10rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  overflow-x: auto;
  white-space: nowrap;

  .tab-item {
    flex: 1;
    min-width: 120rpx;
    text-align: center;
    font-size: 26rpx;
    color: #666;
    position: relative;
    padding-bottom: 10rpx;
  }

  .tab-item.active {
    color: #FF4141;
    font-weight: bold;
  }

  .tab-item.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 40rpx;
    height: 4rpx;
    background-color: #FF4141;
    border-radius: 2rpx;
  }
}

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

      &.type-withdraw {
        background: #fff2f0;
        color: #ff4d4f;
      }

      &.type-transfer {
        background: #f9f0ff;
        color: #722ed1;
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