<template>
    <view>
      <z-paging ref="paging" v-model="dataList" @query="queryList">
        <view class="growth-list">
          <view class="growth-item" v-for="(item, index) in dataList" :key="item.id">
            <view class="item-header">
              <text class="change-type" :class="'change-' + item.change_mode">
                {{ item.change_type_desc }}
              </text>
            </view>
            
            <view class="item-body">
              <view class="growth-change">
                <text class="label">成长值：</text>
                <text class="value" :class="'change-' + item.change_mode">
                  {{ item.change_mode === 1 ? '+' : '-' }}{{ item.change_num }}
                </text>
              </view>
              
              <view class="info-row">
                <text class="label">变更后：</text>
                <text class="value">{{ item.after_num }}</text>
              </view>
              
              <view class="info-row">
                <text class="label">时间：</text>
                <text class="value">{{ item.create_at }}</text>
              </view>
              
              <view class="info-row" v-if="item.change_desc">
                <text class="label">说明：</text>
                <text class="value">{{ item.change_desc }}</text>
              </view>
            </view>
          </view>
        </view>
      </z-paging>
    </view>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  import { getUserGrowthLogPages } from '@/home/api/user/user'
  const paging = ref(null)
  const dataList = ref([])
  
  
  const queryList = async (page_current: number, page_size: number) => {
    try {
      const result = await getUserGrowthLogPages({
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
  .growth-list {
    padding: 20rpx;
  }
  
  .growth-item {
    background: #fff;
    border-radius: 16rpx;
    padding: 28rpx;
    margin-bottom: 24rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
    
    .item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24rpx;
      padding-bottom: 20rpx;
      border-bottom: 1rpx solid #f0f0f0;
      
      
      .change-type {
        font-size: 26rpx;
        padding: 6rpx 16rpx;
        border-radius: 24rpx;
        background: #f0f7ff;
        color: #1890ff;
      }
    }
    
    .item-body {
      .growth-change {
        display: flex;
        align-items: center;
        margin-bottom: 20rpx;
        
        .label {
          font-size: 28rpx;
          color: #666;
          margin-right: 12rpx;
        }
        
        .value {
          font-size: 36rpx;
          font-weight: bold;
          
          &.change-1 {
            color: #07C160;
          }
        }

      }
      
      .info-row {
        display: flex;
        margin-bottom: 16rpx;
        font-size: 26rpx;
        
        .label {
          color: #999;
          margin-right: 12rpx;
          min-width: 120rpx;
        }
        
        .value {
          color: #333;
          flex: 1;
        }
      }
    }
  }
  </style>
  