<template>
  <view>
    <z-paging ref="paging" v-model="dataList" @query="queryList">
      <view class="withdrawal-list">
        <view class="withdrawal-item" v-for="item in dataList" :key="item.id">
          <view class="item-header">
            <text class="serial-no">{{ item.out_transfer_no || '未分配流水号' }}</text>
            <view class="status" :class="'status-' + item.status">
              {{ item.status_desc }}
            </view>
          </view>
          
          <view class="item-body">
            <view class="amount-row">
              <view class="amount">
                <text class="label">提现金额</text>
                <text class="value">¥{{ item.withdrawal_amount }}</text>
              </view>
              <view class="fee" v-if="item.fee_amount">
                <text class="label">手续费</text>
                <text class="value">¥{{ item.fee_amount }}</text>
              </view>
            </view>
            
            <view class="info-row">
              <text class="label">提现方式</text>
              <text class="value">{{ item.account_type_desc }}</text>
            </view>
            
            <view class="info-row">
              <text class="label">账户名称</text>
              <text class="value">{{ item.account_name }}</text>
            </view>
            
            <view class="info-row">
              <text class="label">账号信息</text>
              <text class="value">{{ item.account_number }}</text>
            </view>
            
            <view class="info-row">
              <text class="label">申请时间</text>
              <text class="value">{{ item.create_at }}</text>
            </view>
          </view>
          
          <view class="item-footer" v-if="item.operation_remark">
            <view class="remark">
              <text class="label">备注：</text>
              <text class="value">{{ item.operation_remark }}</text>
            </view>
          </view>
        </view>
        
        

        <DsEmpty v-if="!dataList.length" text="暂无提现记录" />
      </view>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getUserWithdrawalLogPages } from '@/home/api/user/userWithdrawal'
const paging = ref(null)
const dataList = ref([])

import DsEmpty from '@/components/ds-empty/index.vue';

const queryList = async (page_current: number, page_size: number) => {
  try {
    const result = await getUserWithdrawalLogPages({
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
.withdrawal-list {
  padding: 20rpx;
  min-height: calc(100vh - 100rpx);
  background-color: #f8f8f8;
}

.withdrawal-item {
  background: #fff;
  border-radius: 16rpx;
  padding: 30rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  
  .item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24rpx;
    padding-bottom: 20rpx;
    border-bottom: 1rpx solid #f5f5f5;
    
    .serial-no {
      font-size: 26rpx;
      color: #999;
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .status {
      font-size: 26rpx;
      padding: 6rpx 16rpx;
      border-radius: 20rpx;
      font-weight: 500;
      
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
      
      &-3 {
        color: #409EFF;
        background: #ECF5FF;
      }
    }
  }
  
  .item-body {
    padding-bottom: 20rpx;
    
    .amount-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 24rpx;
      
      .amount, .fee {
        display: flex;
        flex-direction: column;
        
        .label {
          font-size: 26rpx;
          color: #999;
          margin-bottom: 6rpx;
        }
      }
      
      .amount .value {
        font-size: 36rpx;
        font-weight: bold;
        color: #FF4444;
      }
      
      .fee .value {
        font-size: 28rpx;
        color: #FF9500;
      }
    }
    
    .info-row {
      display: flex;
      margin-bottom: 16rpx;
      
      &:last-child {
        margin-bottom: 0;
      }
      
      .label {
        width: 140rpx;
        font-size: 26rpx;
        color: #999;
      }
      
      .value {
        flex: 1;
        font-size: 26rpx;
        color: #333;
      }
    }
  }
  
  .item-footer {
    padding-top: 20rpx;
    border-top: 1rpx dashed #f0f0f0;
    
    .remark {
      display: flex;
      
      .label {
        font-size: 26rpx;
        color: #999;
      }
      
      .value {
        flex: 1;
        font-size: 26rpx;
        color: #666;
      }
    }
  }
}


</style>
  