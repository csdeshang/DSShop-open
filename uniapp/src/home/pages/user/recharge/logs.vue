<template>
  <view>
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <view class="recharge-list">
        <view class="recharge-item" v-for="(item, index) in dataList" :key="item.id">
          <view class="item-header">
            <text class="trade-no">{{ item.out_trade_no }}</text>
            <text class="status" :class="'status-' + item.recharge_status">
              {{ item.recharge_status_desc }}
            </text>
          </view>
          
          <view class="item-body">
            <view class="amount">
              <text class="label">金额：</text>
              <text class="value">¥{{ item.recharge_amount }}</text>
            </view>
            
            <view class="info-row">
              <text class="label">支付方式：</text>
              <text class="value">{{ item.pay_channel_desc }}</text>
            </view>
            
            <view class="info-row">
              <text class="label">创建时间：</text>
              <text class="value">{{ item.create_at }}</text>
            </view>
          </view>
        </view>
      </view>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getUserRechargeLogPages } from '@/home/api/user/user'
const paging = ref(null)
const dataList = ref([])


const queryList = async (page_current: number, page_size: number) => {
  try {
    const result = await getUserRechargeLogPages({
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
.recharge-list {
  padding: 20rpx;
}

.recharge-item {
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
    border-bottom: 1rpx solid #f5f5f5;
    padding-bottom: 16rpx;
    
    .trade-no {
      font-size: 26rpx;
      color: #666;
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .status {
      font-size: 26rpx;
      padding: 4rpx 12rpx;
      border-radius: 20rpx;
      
      &-0 {
        color: #FF9500;
        background: #FFF4E5;
      }
      &-1 {
        color: #07C160;
        background: #E7F7EF;
      }
      &-2 {
        color: #FF2D55;
        background: #FFECF1;
      }
    }
  }
  
  .item-body {
    .amount {
      margin-bottom: 16rpx;
      
      .value {
        font-size: 32rpx;
        font-weight: bold;
        color: #FF9500;
      }
    }
    
    .info-row {
      display: flex;
      margin-bottom: 12rpx;
      font-size: 26rpx;
      
      .label {
        color: #999;
        margin-right: 10rpx;
      }
      
      .value {
        color: #333;
      }
    }
  }
  
}
</style>
