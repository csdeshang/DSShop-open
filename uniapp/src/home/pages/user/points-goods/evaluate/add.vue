<template>
  <view class="evaluate-container">
    <!-- 顶部提示 -->
    <view class="evaluate-tips">
      <text>您的评价对其他买家很有帮助哦</text>
    </view>

    <!-- 商品信息 -->
    <view class="goods-section">
      <view class="goods-item">
        <view class="goods-info">
          <image :src="formatImageUrl(orderDetail.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" class="goods-image"></image>
          <view class="goods-detail">
            <text class="goods-name">{{ orderDetail.goods_name }}</text>
            <text class="goods-points">{{ orderDetail.points_price }}积分</text>
            <text class="goods-qty">兑换数量: {{ orderDetail.exchange_num }}件</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 评分区域 -->
    <view class="score-section">
      <view class="score-item">
        <text class="score-label">商品评分</text>
        <uni-rate v-model="evaluateScore" size="20" active-color="#FF6034"></uni-rate>
      </view>
    </view>

    <!-- 评价内容 -->
    <view class="content-section">
      <textarea v-model="evaluateContent" placeholder="宝贝满足你的期待吗？说说你的使用心得，分享给想买的他们吧"
        class="content-input"></textarea>
    </view>

    <!-- 图片上传 -->
    <view class="upload-section">
      <DsUploadImage v-model="evaluateImages" :maxCount="5" :maxSize="2" buttonText="上传图片" />
      <text class="upload-tips">最多上传5张图片</text>
    </view>

    <!-- 匿名选项 -->
    <view class="anonymous-section">
      <checkbox-group @change="setAnonymous">
        <label>
          <checkbox :value="isAnonymous" color="#FF6034" />
          <text>匿名评价</text>
        </label>
      </checkbox-group>
    </view>

    <!-- 提交按钮 -->
    <view class="submit-section">
      <button class="submit-btn" @click="submitEvaluate" :disabled="isSubmitting">{{ isSubmitting ? '提交中...' : '提交评价' }}</button>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getPointsGoodsOrderDetail } from '@/home/api/user/userPointsGoodsOrder'
import { addPointsGoodsEvaluate } from '@/home/api/user/userPointsGoodsEvaluate'
import * as DsUploadImageModule from '@/components/ds-attachment/upload-image.vue'
import { onLoad } from '@dcloudio/uni-app'

const DsUploadImage = DsUploadImageModule.default || DsUploadImageModule

interface OrderDetail {
  id: number
  order_sn: string
  goods_id: number
  goods_name: string
  goods_image: string
  points_price: number
  exchange_num: number
  total_points: number
  order_status: number
  [key: string]: any
}

// 订单详情
const orderDetail = ref<OrderDetail>({
  id: 0,
  order_sn: '',
  goods_id: 0,
  goods_name: '',
  goods_image: '',
  points_price: 0,
  exchange_num: 0,
  total_points: 0,
  order_status: 0
})

// 评价数据
const evaluateScore = ref(5)
const evaluateContent = ref('')
const evaluateImages = ref<string[]>([])
const isAnonymous = ref(false)
const isSubmitting = ref(false)

// 获取订单详情
const getOrderDetail = async (orderId: number) => {
  try {
    uni.showLoading({ title: '加载中...' })
    const res = await getPointsGoodsOrderDetail(orderId)
    if (res.code === 10000) {
      orderDetail.value = res.data
    } else {
      uni.showToast({
        title: res.message || '获取订单详情失败',
        icon: 'none'
      })
    }
  } catch (err) {
    console.error('获取订单详情失败:', err)
    uni.showToast({
      title: '获取订单详情失败',
      icon: 'none'
    })
  } finally {
    uni.hideLoading()
  }
}

// 设置匿名
const setAnonymous = (e: any) => {
  isAnonymous.value = e.detail.value.length > 0
}

// 验证表单
const validateForm = (): boolean => {
  if (!evaluateContent.value || evaluateContent.value.trim() === '') {
    uni.showToast({
      title: '请填写评价内容',
      icon: 'none'
    })
    return false
  }

  if (!evaluateScore.value || evaluateScore.value < 1) {
    uni.showToast({
      title: '请为商品评分',
      icon: 'none'
    })
    return false
  }

  return true
}

// 提交评价
const submitEvaluate = async () => {
  if (!validateForm()) return
  if (isSubmitting.value) return

  isSubmitting.value = true
  uni.showLoading({ title: '提交中...' })

  try {
    // 构建评价数据
    const evaluateData = {
      order_id: orderDetail.value.id,
      goods_id: orderDetail.value.goods_id,
      evaluate_content: evaluateContent.value,
      evaluate_images: evaluateImages.value ? evaluateImages.value.join(',') : '',
      evaluate_score: evaluateScore.value,
      is_anonymous: isAnonymous.value ? 1 : 0
    }

    // 调用积分商品评价API
    const res = await addPointsGoodsEvaluate(evaluateData)

    if (res.code === 10000) {
      uni.showToast({
        title: '评价成功',
        icon: 'success'
      })
      setTimeout(() => {
        uni.navigateBack()
      }, 1500)
    } else {
      uni.showToast({
        title: res.message || '评价失败',
        icon: 'none'
      })
    }

  } catch (error) {
    console.error('提交评价失败', error)
    uni.showToast({
      title: '评价失败',
      icon: 'none'
    })
  } finally {
    uni.hideLoading()
    isSubmitting.value = false
  }
}

onLoad((options: any) => {
  const orderId = options.order_id
  if (orderId) {
    getOrderDetail(parseInt(orderId))
  } else {
    uni.showToast({
      title: '订单ID不存在',
      icon: 'none'
    })
  }
})
</script>

<style lang="scss" scoped>
.evaluate-container {
  min-height: 100vh;
  background: #f8f8f8;
  padding-bottom: 120rpx;
}

.evaluate-tips {
  background: #fff7e6;
  padding: 20rpx 30rpx;
  font-size: 26rpx;
  color: #ff9900;
}

.goods-section {
  margin-top: 20rpx;

  .goods-item {
    background: #fff;
    margin-bottom: 20rpx;
    padding: 30rpx;
    border-radius: 12rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .goods-info {
      display: flex;

      .goods-image {
        width: 160rpx;
        height: 160rpx;
        border-radius: 8rpx;
        margin-right: 20rpx;
      }

      .goods-detail {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;

        .goods-name {
          font-size: 28rpx;
          color: #333;
          margin-bottom: 10rpx;
          font-weight: 500;
        }

        .goods-points {
          font-size: 26rpx;
          color: #FF6034;
          font-weight: bold;
          margin-bottom: 8rpx;
        }

        .goods-qty {
          font-size: 24rpx;
          color: #999;
        }
      }
    }
  }
}

.score-section {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .score-item {
    display: flex;
    align-items: center;

    .score-label {
      font-size: 28rpx;
      color: #333;
      margin-right: 20rpx;
      min-width: 120rpx;
    }
  }
}

.content-section {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .content-input {
    width: 100%;
    height: 200rpx;
    padding: 20rpx;
    box-sizing: border-box;
    border-radius: 8rpx;
    background: #f8f8f8;
    font-size: 28rpx;
  }
}

.upload-section {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .upload-tips {
    font-size: 24rpx;
    color: #999;
    margin-top: 10rpx;
  }
}

.anonymous-section {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  label {
    font-size: 26rpx;
    color: #666;
    display: flex;
    align-items: center;
  }
}

.submit-section {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  background: #fff;
  padding: 20rpx 30rpx;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);

  .submit-btn {
    width: 100%;
    height: 80rpx;
    line-height: 80rpx;
    background: linear-gradient(135deg, #FF6034, #FF8C6A);
    color: #fff;
    font-size: 30rpx;
    border-radius: 40rpx;
    text-align: center;

    &:active {
      opacity: 0.9;
    }

    &[disabled] {
      background: #cccccc;
      opacity: 0.8;
    }
  }
}
</style>
