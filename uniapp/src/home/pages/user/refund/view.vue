<template>
  <view class="pb-200">


    <view class="ds-card">

      <view class="card-header">
        <view class="card-title">
          退款信息
        </view>
      </view>
      <view class="info-item">
        <text class="info-label">退款类型：</text>
        <text class="info-value">{{ refundInfo.refund_type_desc }}</text>
      </view>
      <view class="info-item">
        <text class="info-label">退款状态：</text>
        <text class="info-value">{{ refundInfo.refund_status_desc }}</text>
      </view>
      <view class="info-item" v-if="refundInfo.refund_method">
        <text class="info-label">退款方式：</text>
        <text class="info-value">{{ refundInfo.refund_method_desc }}</text>
      </view>
      <view class="info-item">
        <text class="info-label">申请金额：</text>
        <text class="info-value">¥{{ refundInfo.apply_amount }}</text>
      </view>
      <view class="info-item">
        <text class="info-label">退款说明：</text>
        <text class="info-value">{{ refundInfo.refund_explain || '无' }}</text>
      </view>
      <view class="info-item" v-if="refundInfo.refund_images">
        <text class="info-label">退款凭证：</text>
        <view class="images-container">
          <image 
            v-for="(image, index) in refundImages" 
            :key="index"
            :src="formatImageUrl(image, ThumbnailPresets.small)" 
            mode="aspectFill" 
            class="refund-image"
            @click="previewImage(index)"
          />
        </view>
      </view>
    </view>

    <view class="ds-card">
      <view class="card-header">
        <view class="card-title">退款日志</view>
      </view>
      <view v-for="(log, index) in refundInfo.orderRefundLogList" :key="index" class="log-item">
        <view class="log-content">{{ log.message }}</view>
        <view class="log-time">{{ log.create_at }}</view>
      </view>
    </view>

    <view class="footer-actions" v-if="refundInfo.user_refund_actions.length > 0">
      <button class="ds-btn-md" v-if="refundInfo.user_refund_actions.includes('edit_refund')"
        @click="handleEditRefund(refundInfo)">编辑退款</button>
      <button class="ds-btn-md" v-if="refundInfo.user_refund_actions.includes('cancel_refund')"
        @click="handleCancelRefund(refundInfo)">取消退款</button>
      <button class="ds-btn-md" v-if="refundInfo.user_refund_actions.includes('return_goods')"
        @click="handleReturnGoods(refundInfo)">退货</button>
    </view>



    <EditRefund ref="editRefundPopup" @success="handleEditSuccess" />
    <ReturnGoods ref="returnGoodsPopup" @success="handleReturnGoodsSuccess" />
  </view>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { getRefundInfo, editRefund, cancelRefund } from '@/home/api/tbl-order/tblOrderReturn'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import EditRefund from './edit.vue'
import ReturnGoods from './returnGoods.vue'

const refundInfo = reactive<any>({
  user_refund_actions: []
});

// 计算属性：将图片字符串转换为数组
const refundImages = computed(() => {
  if (!refundInfo.refund_images) return []
  return refundInfo.refund_images.split(',').filter(img => img.trim())
})

// 预览图片
const previewImage = (current: number) => {
  const urls = refundImages.value.map(img => formatImageUrl(img, ThumbnailPresets.original))
  uni.previewImage({
    urls,
    current: current
  })
}







// 取消退款
const handleCancelRefund = (refundInfo: any) => {
  uni.showModal({
    title: '提示',
    content: '确定要取消退款申请吗？',
    success: async (res) => {
      if (res.confirm) {
        try {
          await cancelRefund(refundInfo.id)
          uni.showToast({
            title: '取消成功',
            icon: 'success'
          })
          // 刷新退款信息
          loadRefundDetail(refundInfo.id)
        } catch (error) {
          uni.showToast({
            title: '取消失败',
            icon: 'error'
          })
        }
      }
    }
  })
}

// 编辑退款信息
const editRefundPopup = ref();
const handleEditRefund = (refundInfo: any) => {
  editRefundPopup.value.open(refundInfo)
}
// 编辑成功回调
const handleEditSuccess = () => {
  loadRefundDetail(refundInfo.id)
}

// 退货
const returnGoodsPopup = ref();
const handleReturnGoods = (refundInfo: any) => {
  returnGoodsPopup.value.open(refundInfo)
}
// 退货成功回调
const handleReturnGoodsSuccess = () => {
  loadRefundDetail(refundInfo.id)
}



// 页面参数 - 存储退款ID，用于页面显示时刷新数据
let refundId = 0;

/**
 * 页面加载时获取参数
 * 只在页面首次加载时执行一次，用于获取退款ID参数
 */
onLoad((options: any) => {
  if (options?.id) {
    refundId = parseInt(options.id);
  } else {
    uni.showToast({
      title: '退款ID不存在',
      icon: 'none'
    });
    setTimeout(() => {
      uni.navigateBack();
    }, 1500);
  }
});

/**
 * 页面显示时刷新数据
 * 每次页面显示时都会执行，确保数据是最新的
 * 适用场景：
 * 1. 用户从编辑退款页面返回时，退款状态需要更新
 * 2. 用户从退货页面返回时，退款状态需要更新
 * 3. 用户从后台恢复应用时，数据需要刷新
 * 4. 用户从其他页面返回时，确保看到最新状态
 */
onShow(() => {
  if (refundId > 0) {
    loadRefundDetail(refundId);
  }
});

// 加载退款详情
const loadRefundDetail = async (refund_id: number) => {
  try {
    uni.showLoading({
      title: '加载中...'
    });

    const response = await getRefundInfo(refund_id);

    // 更新退款详情
    Object.assign(refundInfo, response.data);

    uni.hideLoading();
  } catch (error) {
    console.error('获取退款详情失败:', error);
    uni.hideLoading();
    uni.showToast({
      title: '获取退款详情失败',
      icon: 'none'
    });
  }
};
</script>


<style lang="scss" scoped>
.footer-actions {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: flex-end;
  padding: 20rpx 30rpx;
  background-color: #fff;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
  z-index: 100;
}

.images-container {
  display: flex;
  flex-wrap: wrap;
  gap: 12rpx;
  margin-top: 12rpx;
}

.refund-image {
  width: 120rpx;
  height: 120rpx;
  border-radius: 8rpx;
  background-color: #f5f5f5;
}
</style>