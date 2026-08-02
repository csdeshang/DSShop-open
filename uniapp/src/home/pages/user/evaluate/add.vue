<template>
  <view class="evaluate-container">
    <!-- 顶部提示 -->
    <view class="evaluate-tips">
      <text>您的评价对其他买家很有帮助哦</text>
    </view>

    <!-- 商品列表 -->
    <view class="goods-list">
      <view v-for="goods in orderDetail.orderGoodsList" :key="goods.id" class="goods-item">
        <view class="goods-info">
          <image :src="formatImageUrl(goods.goods_image, ThumbnailPresets.small, 'goods')" mode="aspectFill" class="goods-image"></image>
          <view class="goods-detail">
            <text class="goods-name">{{ goods.goods_name }}</text>
            <text class="goods-sku">{{ goods.sku_name }}</text>
          </view>
        </view>

        <!-- 评分区域 -->
        <view class="score-section">
          <view class="score-item">
            <text class="score-label">商品评分</text>
            <uni-rate v-model="goodsScores[goods.id]" size="20" active-color="#FF6034"></uni-rate>
          </view>
        </view>

        <!-- 评价内容 -->
        <view class="content-section">
          <textarea v-model="contents[goods.id]" placeholder="宝贝满足你的期待吗？说说你的使用心得，分享给想买的他们吧"
            class="content-input"></textarea>
        </view>

        <!-- 图片上传 -->
        <view class="upload-section">
          <DsUploadImage v-model="images[goods.id]" :maxCount="5" :maxSize="2" buttonText="上传图片" />
          <text class="upload-tips">最多上传5张图片</text>
        </view>


      </view>
    </view>

    <!-- 店铺评分 -->
    <view class="store-score">
      <view class="score-title">店铺评分</view>
      <view class="score-item">
        <text>描述相符</text>
        <uni-rate v-model="storeScores.describe" size="20" active-color="#FF6034"></uni-rate>
      </view>
      <view class="score-item">
        <text>物流服务</text>
        <uni-rate v-model="storeScores.logistics" size="20" active-color="#FF6034"></uni-rate>
      </view>
      <view class="score-item">
        <text>服务态度</text>
        <uni-rate v-model="storeScores.service" size="20" active-color="#FF6034"></uni-rate>
      </view>
    </view>

    <!-- 骑手评价 交付方式为骑手 -->
    <view v-if="orderDetail.delivery_method == 'rider'" class="service-score">
      <view class="score-title">骑手评价</view>
      <view class="score-item">
        <text>服务评分</text>
        <uni-rate v-model="riderServiceScore" size="20" active-color="#FF6034"></uni-rate>
      </view>
      <view class="content-section">
        <textarea v-model="riderContent" placeholder="对骑手的配送服务还满意吗？说说你的感受吧"
          class="content-input"></textarea>
      </view>
    </view>

    <!-- 师傅评价 交付方式为师傅 -->
    <view v-if="orderDetail.delivery_method == 'technician'" class="service-score">
      <view class="score-title">师傅评价</view>
      <view class="score-item">
        <text>服务评分</text>
        <uni-rate v-model="technicianServiceScore" size="20" active-color="#FF6034"></uni-rate>
      </view>
      <view class="content-section">
        <textarea v-model="technicianContent" placeholder="对师傅的服务还满意吗？说说你的感受吧"
          class="content-input"></textarea>
      </view>
    </view>

    <!-- 统一匿名选项 -->
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
      <button class="submit-btn" @click="submitEvaluate" :disabled="isSubmitting">{{ isSubmitting ? '提交中...' : '提交评价'
        }}</button>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { addUserGoodsComment } from '@/home/api/user/user'
import { getTblOrderInfo } from '@/home/api/tbl-order/tblOrder'
import * as DsUploadImageModule from '@/components/ds-attachment/upload-image.vue'
import { onLoad } from '@dcloudio/uni-app'
const DsUploadImage = DsUploadImageModule.default || DsUploadImageModule

interface OrderGoods {
  id: number
  order_id: number
  goods_name: string
  sku_name: string
  goods_image: string
  goods_num: number
  pay_price: number
}

interface OrderDetail {
  id: number
  order_sn: string
  orderGoodsList: OrderGoods[]
  [key: string]: any
}

// 订单详情
const orderDetail = ref<OrderDetail>({
  id: 0,
  order_sn: '',
  orderGoodsList: []
})

// 评分数据
const goodsScores = reactive<{ [key: number]: number }>({})
const contents = reactive<{ [key: number]: string }>({})
const images = reactive<{ [key: number]: string[] }>({})
const storeScores = reactive({
  describe: 5,
  logistics: 5,
  service: 5
})
const riderServiceScore = ref(5)
const riderContent = ref('')
const technicianServiceScore = ref(5)
const technicianContent = ref('')
const isAnonymous = ref(false)

const isSubmitting = ref(false)

// 获取订单详情
const getOrderDetail = async (orderId: number) => {
  try {
    const res = await getTblOrderInfo(orderId)
    if (res.code === 10000) {
      orderDetail.value = res.data
      // 初始化评分数据
      orderDetail.value.orderGoodsList.forEach((goods: OrderGoods) => {
        goodsScores[goods.id] = 5
        contents[goods.id] = ''
        images[goods.id] = []
      })
    } else {
      uni.showToast({
        title: res.message || '获取订单详情失败',
        icon: 'none'
      })
    }
  } catch (err) {
    uni.showToast({
      title: '获取订单详情失败',
      icon: 'none'
    })
  }
}

// 设置匿名
const setAnonymous = (e: any) => {
  isAnonymous.value = e.detail.value.length > 0
}

// 上传图片 - 使用组件返回的URL，无需手动上传
const uploadImages = async (goodsId: number, paths: string[]): Promise<string> => {
  if (!paths || paths.length === 0) return ''

  // 直接返回以逗号连接的URL字符串，因为组件已经自动上传了图片
  return paths.join(',')
}

// 验证表单
const validateForm = (): boolean => {
  for (const goods of orderDetail.value.orderGoodsList) {
    if (!contents[goods.id] || contents[goods.id].trim() === '') {
      uni.showToast({
        title: `请填写"${goods.goods_name}"的评价内容`,
        icon: 'none'
      })
      return false
    }

    if (!goodsScores[goods.id] || goodsScores[goods.id] < 1) {
      uni.showToast({
        title: `请为"${goods.goods_name}"评分`,
        icon: 'none'
      })
      return false
    }
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
      goods_comments: orderDetail.value.orderGoodsList.map((goods: OrderGoods) => {
        // 图片URLs已经在组件中上传，直接使用
        const imageUrls = images[goods.id] ? images[goods.id].join(',') : ''

        return {
          order_id: goods.order_id,
          order_goods_id: goods.id,
          content: contents[goods.id],
          images: imageUrls,
          is_anonymous: isAnonymous.value ? 1 : 0,
          goods_score: goodsScores[goods.id],
          describe_score: storeScores.describe,
          logistics_score: storeScores.logistics,
          service_score: storeScores.service
        }
      }),
      is_anonymous: isAnonymous.value ? 1 : 0,
      // 骑手评价参数
      rider_content: riderContent.value,
      rider_service_score: riderServiceScore.value,
      // 师傅评价参数
      technician_content: technicianContent.value,
      technician_service_score: technicianServiceScore.value
    }

    // 一次性提交所有评价
    const res = await addUserGoodsComment(evaluateData)

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

.goods-list {
  margin-top: 20rpx;

  .goods-item {
    background: #fff;
    margin-bottom: 20rpx;
    padding: 30rpx;
    border-radius: 12rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .goods-info {
      display: flex;
      margin-bottom: 30rpx;

      .goods-image {
        width: 160rpx;
        height: 160rpx;
        border-radius: 8rpx;
      }

      .goods-detail {
        flex: 1;
        margin-left: 20rpx;
        display: flex;
        flex-direction: column;
        justify-content: center;

        .goods-name {
          font-size: 28rpx;
          color: #333;
          margin-bottom: 10rpx;
          font-weight: 500;
        }

        .goods-sku {
          font-size: 24rpx;
          color: #999;
          background: #f5f5f5;
          padding: 4rpx 12rpx;
          border-radius: 4rpx;
          display: inline-block;
          max-width: 300rpx;
        }
      }
    }
  }
}

.score-section {
  margin-bottom: 30rpx;

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
  margin-bottom: 30rpx;

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
  margin-bottom: 30rpx;

  .upload-tips {
    font-size: 24rpx;
    color: #999;
    margin-top: 10rpx;
  }
}

.anonymous-section {
  label {
    font-size: 26rpx;
    color: #666;
    display: flex;
    align-items: center;
  }
}

.store-score {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx 20rpx 140rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .score-title {
    font-size: 30rpx;
    color: #333;
    font-weight: bold;
    margin-bottom: 20rpx;
    position: relative;
    padding-left: 20rpx;

    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 6rpx;
      height: 30rpx;
      background: linear-gradient(to bottom, #FF6034, #FF8C6A);
      border-radius: 3rpx;
    }
  }

  .score-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30rpx;

    &:last-child {
      margin-bottom: 0;
    }

    text {
      font-size: 28rpx;
      color: #666;
    }
  }
}

.service-score {
  background: #fff;
  padding: 30rpx;
  margin: 20rpx 20rpx 140rpx;
  border-radius: 12rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .score-title {
    font-size: 30rpx;
    color: #333;
    font-weight: bold;
    margin-bottom: 20rpx;
    position: relative;
    padding-left: 20rpx;

    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 6rpx;
      height: 30rpx;
      background: linear-gradient(to bottom, #FF6034, #FF8C6A);
      border-radius: 3rpx;
    }
  }

  .score-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30rpx;

    &:last-child {
      margin-bottom: 0;
    }

    text {
      font-size: 28rpx;
      color: #666;
    }
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
