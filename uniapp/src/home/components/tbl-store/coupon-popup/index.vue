<template>
  <view @click="open">
    <slot>

    </slot>
  </view>
  <uni-popup ref="popupRef" type="bottom">
    <view class="popup-container">
      <!-- 标题 -->
      <view class="header">
        <text class="title">优惠券</text>
        <uni-icons type="closeempty" size="24" color="#999" @click="close" />
      </view>

      <!-- 优惠券列表 -->
      <scroll-view scroll-y class="coupon-list" v-if="couponList.length > 0">
        <view v-for="(item, index) in couponList" :key="index" class="coupon-item">
          <view class="coupon-content">
            <view class="left">
              <view class="amount" :class="[item.coupon_type === 2 ? 'discount' : '']">
                <text class="prefix" v-if="item.coupon_type === 1">¥</text>
                <text class="value">{{ formatDiscountValue(item) }}</text>
                <text class="suffix" v-if="item.coupon_type === 2">折</text>
              </view>
              <text class="condition">满{{ item.min_spend }}元可用</text>
            </view>
            <view class="right">
              <text class="name">{{ item.coupon_name }}</text>
              <text class="time">{{ formatTimeRange(item.start_time, item.end_time) }}</text>
              <view class="tag" v-if="item.status === 1">{{ item.status_desc }}</view>
            </view>
          </view>
          <view class="action">
            <button class="btn" @click="receiveCoupon(item)">立即领取</button>
          </view>
        </view>
      </scroll-view>
      <DsEmpty v-if="couponList.length === 0" text="暂无优惠券" />
    </view>
  </uni-popup>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getTblStoreCouponList } from '@/home/api/tbl-store/tblStoreCoupon'
import { receiveTblStoreCoupon } from '@/home/api/user/userTblStoreCoupon'

// 正确导入DsEmpty组件
import DsEmpty from '@/components/ds-empty/index.vue'

const props = defineProps({
})

const popupRef = ref()
const couponList = ref<any[]>([])

// 打开弹窗
const open = async () => {
  popupRef.value.open();
  await getCouponList()
}

// 关闭弹窗
const close = () => {
  popupRef.value.close();
}

// 获取优惠券列表
const getCouponList = async () => {
  try {
    const res = await getTblStoreCouponList({ status: 1 })
    couponList.value = res.data
  } catch (error) {
    console.error(error)
  }
}

// 领取优惠券
const receiveCoupon = async (item: any) => {

  const res = await receiveTblStoreCoupon(item.id)
  if (res.code == 10000) {
    uni.showToast({
      title: '领取成功',
      icon: 'success'
    })
  } else {
    uni.showToast({
      title: res.message,
      icon: 'none'
    })
  }

}

// 格式化折扣值
const formatDiscountValue = (item: any) => {
  if (item.coupon_type === 1) { // 满减券
    return item.discount_value
  } else if (item.coupon_type === 2) { // 折扣券
    return (item.discount_value * 10).toFixed(1)
  } else { // 直减券
    return item.discount_value
  }
}

// 格式化时间范围
const formatTimeRange = (startTime: string, endTime: string) => {
  // 简化日期显示，只显示年月日
  const start = startTime.split(' ')[0]
  const end = endTime.split(' ')[0]
  return `${start} - ${end}`
}

defineExpose({
  open,
  close
})
</script>

<style scoped lang="scss">
.popup-container {
  background: #fff;
  border-radius: 16px 16px 0 0;
  padding: 24px;
  max-height: 70vh;
  position: relative;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;

  .title {
    font-size: 18px;
    font-weight: bold;
    color: #333;
  }
}

.coupon-list {
  max-height: 60vh;
}

.coupon-item {
  background: linear-gradient(to right, #fff9f9, #fff);
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  margin-bottom: 16px;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    left: 100px;
    top: 0;
    height: 100%;
    width: 1px;
    background: repeating-linear-gradient(to bottom, #eee 0, #eee 6px, transparent 6px, transparent 12px);
  }

  .coupon-content {
    display: flex;
    padding: 16px;
    position: relative;

    .left {
      width: 100px;
      text-align: center;
      position: relative;

      .amount {
        font-weight: bold;
        color: #f56c6c;
        display: flex;
        align-items: baseline;
        justify-content: center;

        .prefix {
          font-size: 16px;
        }

        .value {
          font-size: 28px;
        }

        .suffix {
          font-size: 16px;
          margin-left: 2px;
        }

        &.discount {
          color: #ff9500;
        }
      }

      .condition {
        font-size: 12px;
        color: #999;
        display: block;
        margin-top: 4px;
      }
    }

    .right {
      flex: 1;
      padding-left: 16px;
      position: relative;

      .name {
        font-size: 16px;
        color: #333;
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
      }

      .time {
        font-size: 12px;
        color: #999;
        display: block;
      }

      .tag {
        position: absolute;
        top: 0;
        right: 0;
        background: #67c23a;
        color: #fff;
        font-size: 12px;
        padding: 2px 6px;
        border-radius: 4px;
      }
    }
  }

  .action {
    padding: 0 16px 16px;
    text-align: right;

    .btn {
      background: linear-gradient(to right, #ff6b6b, #f56c6c);
      color: #fff;
      border-radius: 20px;
      font-size: 14px;
      padding: 0 24px;
      height: 36px;
      line-height: 36px;
      box-shadow: 0 4px 8px rgba(245, 108, 108, 0.2);
    }
  }
}
</style>
