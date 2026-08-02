<template>
  <view>
    <view class="coupon-wrapper" :style="containerStyle">
      <view class="coupon-header" v-if="couponSetting.is_show_header_title">
        <text class="coupon-title" :style="fontStyle">{{ couponSetting.header_title }}</text>
        <text class="coupon-more" @click="goToCouponCenter">查看更多</text>
      </view>
      
      <view class="coupon-list">
        <scroll-view scroll-x class="coupon-scroll">
          <view class="coupon-content">
            <view 
              class="coupon-item" 
              v-for="(item, index) in couponList" 
              :key="index"
            >
              <view class="coupon-left">
                <view class="coupon-price">
                  <text class="price-symbol">¥</text>
                  <text class="price-value">{{ formatPrice(item.discount_value) }}</text>
                </view>
                <view class="coupon-condition">
                  {{ formatCondition(item.min_spend) }}
                </view>
              </view>
              
              <view class="coupon-divider">
                <view class="circle circle-top"></view>
                <view class="dash"></view>
                <view class="circle circle-bottom"></view>
              </view>
              
              <view class="coupon-right">
                <view class="coupon-name">{{ item.coupon_name }}</view>
                <view class="coupon-date">{{ formatDate(item.start_time, item.end_time) }}</view>
                <view 
                  class="coupon-btn"
                  @click.stop="receiveCoupon(item)"
                >
                  {{ getBtnText(item) }}
                </view>
              </view>
              
              <view class="coupon-tag" v-if="item.status === 0">{{ item.status_desc }}</view>
            </view>

            <DsEmpty v-if="!couponList || couponList.length === 0" text="暂无优惠券" />
            
 
          </view>
        </scroll-view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { getTblStoreCouponList } from '@/home/api/tbl-store/tblStoreCoupon';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

import DsEmpty from '@/components/ds-empty/index.vue';

interface Settings {
  couponSetting: {
    // 平台 all:全部 platform:平台
    platform: string;
    // 显示数量
    displayCount: number;
    // 是否显示头部标题
    is_show_header_title: boolean;
    // 头部标题
    header_title: string;
    // 头部更多链接
    header_more_link: string;
  };

  baseStyles: BaseStyles; // 使用共享接口
}

// 扩展基础配置接口
interface BaseConfig extends BaseDiyConfig {
  settings: Settings;
}

// 接收父组件传递的配置
const props = defineProps<{
  config: BaseConfig;
}>();

// 获取店铺设置
const couponSetting = computed(() => {
    return props.config.settings?.couponSetting || {};
});

// 优惠券数据
const couponList = ref([]);

// 获取优惠券列表
const fetchTblStoreCouponList = async (limit: number) => {
  try {
    const res = await getTblStoreCouponList({
      limit: limit
    });
    
    if (res.code === 10000 && res.data) {
      couponList.value = res.data;
    } else {
      console.error('获取优惠券失败:', res.message);
      couponList.value = [];
    }
  } catch (error) {
    console.error('获取优惠券异常:', error);
    couponList.value = [];
  }
};


// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);



// 格式化价格
const formatPrice = (price) => {
  const value = parseFloat(price);
  return value % 1 === 0 ? value.toFixed(0) : value.toFixed(1);
};

// 格式化使用条件
const formatCondition = (minSpend) => {
  const min = parseFloat(minSpend);
  return min <= 0 ? '无门槛' : `满${min}元可用`;
};

// 格式化日期
const formatDate = (startTime: any, endTime: any) => {
  if (!startTime || !endTime) return '';
  
  try {
    const start = new Date(startTime);
    const end = new Date(endTime);
    
    const startFormat = `${start.getMonth() + 1}.${start.getDate()}`;
    const endFormat = `${end.getMonth() + 1}.${end.getDate()}`;
    
    return `${startFormat}-${endFormat}`;
  } catch (e) {
    return `${startTime.substr(5, 5)}-${endTime.substr(5, 5)}`;
  }
};

// 获取按钮文本
const getBtnText = (item) => {
  if (item.status === 0) return '已失效';
  return item.claim_type === 4 ? '已发放' : '立即领取';
};

// 领取优惠券
const receiveCoupon = (coupon) => {
  if (coupon.status === 0) {
    uni.showToast({
      title: '该优惠券已失效',
      icon: 'none'
    });
    return;
  }
  
  if (coupon.claim_type === 4) {
    uni.showToast({
      title: '优惠券已自动发放',
      icon: 'none'
    });
    return;
  }
  
  console.log('领取优惠券:', coupon);
  uni.showToast({
    title: '领取成功',
    icon: 'success'
  });
};

// 前往优惠券中心
const goToCouponCenter = () => {
  uni.navigateTo({
    url: '/home/pages/user/coupon/index'
  });
};

// 获取优惠券数据
onMounted(() => {
  fetchTblStoreCouponList(props.config.settings.couponSetting.displayCount);
});
</script>

<style scoped lang="scss">
.coupon-wrapper {
  width: 100%;
  box-sizing: border-box;
  background-color: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  
  .coupon-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f5f5f5;
    
    .coupon-title {
      font-size: 15px;
      font-weight: 500;
      color: #333333;
    }
    
    .coupon-more {
      font-size: 12px;
      color: #999999;
    }
  }
  
  .coupon-list {
    padding: 12px 0;
    
    .coupon-scroll {
      width: 100%;
      white-space: nowrap;
      
      .coupon-content {
        padding: 0 15px;
        display: inline-flex;
        
        .coupon-item {
          display: flex;
          width: 240px;
          height: 80px;
          margin-right: 10px;
          background: linear-gradient(to right, #ff6b6b, #ff8e8e);
          border-radius: 8px;
          overflow: hidden;
          position: relative;
          box-shadow: 0 2px 8px rgba(255, 107, 107, 0.2);
        
          
          .coupon-left {
            width: 80px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 0 8px;
            
            .coupon-price {
              display: flex;
              align-items: flex-start;
              color: #ffffff;
              
              .price-symbol {
                font-size: 12px;
                margin-top: 4px;
              }
              
              .price-value {
                font-size: 24px;
                font-weight: bold;
              }
            }
            
            .coupon-condition {
              font-size: 11px;
              color: #ffffff;
              margin-top: 4px;
              text-align: center;
            }
          }
          
          .coupon-divider {
            position: relative;
            width: 1px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            
            .coupon-dash {
              flex: 1;
              width: 0;
              border-left: 1px dashed rgba(255, 255, 255, 0.6);
            }
            
            .circle {
              width: 8px;
              height: 8px;
              background-color: #ffffff;
              border-radius: 50%;
              position: absolute;
              
              &.circle-top {
                top: -4px;
              }
              
              &.circle-bottom {
                bottom: -4px;
              }
            }
          }
          
          .coupon-right {
            flex: 1;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            
            .coupon-name {
              font-size: 14px;
              color: #ffffff;
              font-weight: 500;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
            }
            
            .coupon-date {
              font-size: 11px;
              color: rgba(255, 255, 255, 0.8);
            }
            
            .coupon-btn {
              width: 72px;
              height: 22px;
              line-height: 22px;
              text-align: center;
              background-color: #ffffff;
              color: #ff6b6b;
              font-size: 11px;
              border-radius: 11px;
              align-self: flex-end;
              font-weight: 500;
              
            }
          }
          
          .coupon-tag {
            position: absolute;
            top: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.5);
            color: #ffffff;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 0 8px 0 8px;
          }
        }
        
        .coupon-empty {
          width: 240px;
          height: 80px;
          display: flex;
          justify-content: center;
          align-items: center;
          background-color: #f8f8f8;
          border-radius: 8px;
          

        }
      }
    }
  }
}
</style>
