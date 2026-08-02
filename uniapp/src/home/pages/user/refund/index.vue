<template>
    <view class="page-container">
      <z-paging ref="paging" v-model="dataList" @query="queryList">
        <view class="refund-list">
          <view class="refund-item" v-for="item in dataList" :key="item.id" @click="goToPage({ url: '/home/pages/user/refund/view?id=' + item.id })">
            <view class="item-header">
              <text class="type">{{ item.refund_type_desc }}</text>
              <text class="status">
                {{ item.refund_status_desc }}
              </text>
            </view>
            
            <view class="item-body">
              <view class="amount">
                <text class="label">申请金额：</text>
                <text class="value">¥{{ item.apply_amount }}</text>
              </view>
              
              <view class="info-row" v-if="item.refund_method">
                <text class="label">退款方式：</text>
                <text class="value">{{ item.refund_method_desc }}</text>
              </view>
              
              <view class="info-row">
                <text class="label">申请时间：</text>
                <text class="value">{{ item.create_at }}</text>
              </view>
              
              <view class="info-row" v-if="item.refund_explain">
                <text class="label">退款说明：</text>
                <text class="value">{{ item.refund_explain }}</text>
              </view>
            </view>
          </view>
        </view>
      </z-paging>
    </view>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  import { getRefundPages } from '@/home/api/tbl-order/tblOrderReturn'
  import { goToPage } from '@/utils/util'
  const paging = ref(null)
  const dataList = ref([])
  
  
  const queryList = async (page_current: number, page_size: number) => {
    try {
      const result = await getRefundPages({
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
  .page-container {
    padding: 20rpx;
    background-color: #f5f5f5;
  }
  
  .refund-list {
    .refund-item {
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
        
        .type {
          font-size: 28rpx;
          font-weight: bold;
          color: #333;
        }
        
        .status {
          font-size: 26rpx;
          padding: 4rpx 12rpx;
          border-radius: 20rpx;
          color: #666;
        }
      }
      
      .item-body {
        .amount {
          margin-bottom: 16rpx;
          
          .label { color: #999; }
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
            min-width: 140rpx;
          }
          
          .value {
            color: #333;
            flex: 1;
          }
        }
      }
    }
  }
  </style>
  