<template>
  <view class="evaluate-container">
    <z-paging ref="paging" v-model="evaluates" @query="queryList">
      <!-- 评价状态选项卡 -->
      <template v-slot:top>
        <view class="evaluate-tabs">
          <view v-for="(tab, index) in tabs" :key="index" class="tab-item"
            :class="{ active: currentTab === index }" @click="switchTab(index)">
            {{ tab.label }}
          </view>
        </view>
      </template>

      <!-- 评价列表 -->
      <view class="evaluate-list" v-if="evaluates.length > 0">
        <view class="evaluate-item" v-for="evaluate in evaluates" :key="evaluate.id">
          <!-- 商品信息 -->
          <view class="goods-info" @click="goGoodsDetailPage(evaluate.goods)">
            <image class="goods-image" :src="formatImageUrl(evaluate.goods.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
            <view class="goods-details">
              <view class="goods-name">{{ evaluate.goods.goods_name }}</view>
              <view class="goods-platform">{{ evaluate.platform }}</view>
            </view>
          </view>

          <!-- 评价内容 -->
          <view class="evaluate-content">
            <!-- 评分 -->
            <view class="evaluate-score">
              <view class="score-label">商品评分：</view>
              <view class="stars">
                <text v-for="i in 5" :key="i" class="star" 
                  :class="{ active: i <= evaluate.goods_score }">★</text>
              </view>
              <text class="score-text">{{ evaluate.goods_score }}分</text>
            </view>

            <!-- 详细评分 -->
            <view class="detail-scores">
              <view class="score-item">
                <text class="score-label">描述相符：</text>
                <view class="stars">
                  <text v-for="i in 5" :key="i" class="star" 
                    :class="{ active: i <= evaluate.describe_score }">★</text>
                </view>
                <text class="score-text">{{ evaluate.describe_score }}分</text>
              </view>
              <view class="score-item">
                <text class="score-label">物流服务：</text>
                <view class="stars">
                  <text v-for="i in 5" :key="i" class="star" 
                    :class="{ active: i <= evaluate.logistics_score }">★</text>
                </view>
                <text class="score-text">{{ evaluate.logistics_score }}分</text>
              </view>
              <view class="score-item">
                <text class="score-label">服务态度：</text>
                <view class="stars">
                  <text v-for="i in 5" :key="i" class="star" 
                    :class="{ active: i <= evaluate.service_score }">★</text>
                </view>
                <text class="score-text">{{ evaluate.service_score }}分</text>
              </view>
            </view>

            <!-- 评价文字 -->
            <view class="evaluate-text" v-if="evaluate.content">
              {{ evaluate.content }}
            </view>

            <!-- 评价图片 -->
            <view class="evaluate-images" v-if="evaluate.images">
              <image 
                class="evaluate-image" 
                :src="formatImageUrl(evaluate.images, ThumbnailPresets.small)" 
                mode="aspectFill"
                @click="previewImage([evaluate.images], 0)" 
              />
            </view>

            <!-- 用户信息 -->
            <view class="user-info">
              <text class="user-label">评价用户：</text>
              <text class="user-name">{{ evaluate.is_anonymous ? '匿名用户' : evaluate.user.username }}</text>
            </view>

            <!-- 评价时间 -->
            <view class="evaluate-time">
              {{ formatTime(evaluate.create_at) }}
            </view>

            <!-- 商家回复 -->
            <view class="reply-section" v-if="evaluate.reply_content">
              <view class="reply-header">
                <uni-icons type="chat" size="14" color="#666"></uni-icons>
                <text class="reply-label">商家回复</text>
              </view>
              <text class="reply-content">{{ evaluate.reply_content }}</text>
              <text class="reply-time" v-if="evaluate.reply_time">
                {{ formatTime(evaluate.reply_time) }}
              </text>
            </view>
          </view>

          <!-- 操作按钮 -->
          <!-- <view class="evaluate-actions">
            <button class="ds-btn-sm ds-btn-white" @click="goToEvaluateDetail(evaluate.id)">查看详情</button>
            <button class="ds-btn-sm ds-btn-white" v-if="!evaluate.reply_content" @click="handleReply(evaluate)">回复</button>
            <button class="ds-btn-sm ds-btn-white" @click="handleEdit(evaluate)">编辑</button>
          </view> -->
        </view>
      </view>

      <!-- 空状态 -->
      <view class="empty-state" v-else>
        <image class="empty-icon" src="/static/images/empty-evaluate.png" mode="aspectFit" />
        <text class="empty-text">暂无评价记录</text>
        <button class="ds-btn-md ds-btn-orange" @click="goToOrderList">去评价</button>
      </view>
    </z-paging>
  </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getUserGoodsCommentPages } from '@/home/api/user/user'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { goGoodsDetailPage } from '@/utils/platform'

// 定义类型
interface GoodsComment {
  id: number
  platform: string
  user_id: number
  order_id: number
  order_goods_id: number
  goods_id: number
  sku_id: number
  content: string
  images: string
  is_show: number
  is_anonymous: number
  is_reply: number | null
  reply_content: string | null
  reply_time: string
  goods_score: number
  describe_score: number
  logistics_score: number
  service_score: number
  create_at: string
  update_at: string
  is_deleted: number
  deleted_at: number
  user: {
    id: number
    username: string
    avatar: string
  }
  goods: {
    id: number
    goods_name: string
    cover_image: string
  }
}

// 定义评价状态选项卡
const tabs = [
  { label: '全部', value: '' },
  { label: '好评', value: '5' },
  { label: '中评', value: '3,4' },
  { label: '差评', value: '1,2' }
]

// 当前选中的选项卡
const currentTab = ref(0)

// 评价列表
const evaluates = ref<GoodsComment[]>([])

// z-paging 引用
const paging = ref<any>(null)

// 切换选项卡
const switchTab = (index: number) => {
  if (currentTab.value !== index) {
    currentTab.value = index
    // 使用z-paging提供的刷新方法
    if (paging.value) {
      paging.value.reload()
    }
  }
}

// 查询列表数据 - 提供给z-paging的回调函数
const queryList = async (page_current: number, page_size: number) => {
  try {
    const params: any = {
      page_current,
      page_size
    }

    // 根据选项卡添加评分筛选
    const tabValue = tabs[currentTab.value].value
    if (tabValue) {
      if (tabValue.includes(',')) {
        // 多个评分值，使用IN查询
        params.goods_score = tabValue.split(',')
      } else {
        // 单个评分值
        params.goods_score = tabValue
      }
    }

    const result = await getUserGoodsCommentPages(params)

    // 处理完成后通知z-paging
    if (paging.value) {
      paging.value.complete(result.data.data)
    }
  } catch (e) {
    console.error('查询商品评价列表失败:', e)
    if (paging.value) {
      paging.value.complete(false)
    }
    uni.showToast({
      title: '加载评价失败',
      icon: 'none'
    })
  }
}


// 跳转到评价详情
// const goToEvaluateDetail = (evaluateId: number) => {
//   uni.navigateTo({
//     url: `/home/pages/user/evaluate/detail?id=${evaluateId}`
//   })
// }

// 处理回复
// const handleReply = (evaluate: GoodsComment) => {
//   uni.navigateTo({
//     url: `/home/pages/user/evaluate/reply?id=${evaluate.id}`
//   })
// }

// 处理编辑
// const handleEdit = (evaluate: GoodsComment) => {
//   uni.navigateTo({
//     url: `/home/pages/user/evaluate/edit?id=${evaluate.id}`
//   })
// }

// 预览图片
const previewImage = (images: string[], current: number) => {
  const urls = images.map(img => formatImageUrl(img, ThumbnailPresets.original))
  uni.previewImage({
    urls,
    current: current
  })
}

// 跳转到订单列表
const goToOrderList = () => {
  uni.switchTab({
    url: '/home/pages/user/order/index'
  })
}

// 格式化时间
const formatTime = (timestamp: string | number): string => {
  const date = new Date(typeof timestamp === 'string' ? timestamp : timestamp * 1000)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  
  // 小于1分钟
  if (diff < 60000) {
    return '刚刚'
  }
  
  // 小于1小时
  if (diff < 3600000) {
    return Math.floor(diff / 60000) + '分钟前'
  }
  
  // 小于1天
  if (diff < 86400000) {
    return Math.floor(diff / 3600000) + '小时前'
  }
  
  // 小于7天
  if (diff < 604800000) {
    return Math.floor(diff / 86400000) + '天前'
  }
  
  // 超过7天显示具体日期
  return date.getFullYear() + '-' + 
         String(date.getMonth() + 1).padStart(2, '0') + '-' + 
         String(date.getDate()).padStart(2, '0')
}

// 暴露方法给父组件
defineExpose({
  refresh: () => paging.value?.reload(),
  resetToFirstPage: () => paging.value?.reload(true)
})
</script>

<style lang="scss" scoped>
.evaluate-container {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.evaluate-tabs {
  display: flex;
  background-color: #fff;
  padding: 20rpx 0;
  border-radius: 10rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .tab-item {
    flex: 1;
    text-align: center;
    font-size: 26rpx;
    color: #666;
    position: relative;
    padding-bottom: 10rpx;
  }

  .tab-item.active {
    color: #FF6034;
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
    background-color: #FF6034;
    border-radius: 2rpx;
  }
}

.evaluate-list {
  padding: 20rpx;
  margin-bottom: 20rpx;
}

.evaluate-item {
  background-color: #fff;
  border-radius: 10rpx;
  margin-bottom: 20rpx;
  padding: 20rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

  .goods-info {
    display: flex;
    padding-bottom: 20rpx;
    border-bottom: 1rpx solid #f0f0f0;

    .goods-image {
      width: 120rpx;
      height: 120rpx;
      border-radius: 8rpx;
      margin-right: 20rpx;
      background-color: #f9f9f9;
    }

    .goods-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;

      .goods-name {
        font-size: 28rpx;
        color: #333;
        margin-bottom: 10rpx;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
      }

      .goods-platform {
        font-size: 24rpx;
        color: #999;
        margin-bottom: 8rpx;
        background-color: #f0f0f0;
        padding: 4rpx 8rpx;
        border-radius: 4rpx;
        display: inline-block;
      }
    }
  }

  .evaluate-content {
    padding: 20rpx 0;

    .evaluate-score {
      display: flex;
      align-items: center;
      margin-bottom: 16rpx;

      .score-label {
        font-size: 26rpx;
        color: #666;
        margin-right: 10rpx;
      }

      .stars {
        margin-right: 10rpx;

        .star {
          font-size: 32rpx;
          color: #ddd;
          margin-right: 4rpx;
        }

        .star.active {
          color: #FF6034;
        }
      }

      .score-text {
        font-size: 24rpx;
        color: #FF6034;
        font-weight: bold;
      }
    }

    .detail-scores {
      margin-bottom: 20rpx;
      padding: 16rpx;
      background-color: #f8f9fa;
      border-radius: 8rpx;

      .score-item {
        display: flex;
        align-items: center;
        margin-bottom: 12rpx;

        &:last-child {
          margin-bottom: 0;
        }

        .score-label {
          font-size: 24rpx;
          color: #666;
          margin-right: 8rpx;
          min-width: 120rpx;
        }

        .stars {
          margin-right: 8rpx;

          .star {
            font-size: 24rpx;
            color: #ddd;
            margin-right: 2rpx;
          }

          .star.active {
            color: #FF6034;
          }
        }

        .score-text {
          font-size: 22rpx;
          color: #FF6034;
          font-weight: bold;
        }
      }
    }

    .evaluate-text {
      font-size: 28rpx;
      color: #333;
      line-height: 1.6;
      margin-bottom: 20rpx;
    }

    .evaluate-images {
      display: flex;
      flex-wrap: wrap;
      gap: 10rpx;
      margin-bottom: 20rpx;

      .evaluate-image {
        width: 120rpx;
        height: 120rpx;
        border-radius: 8rpx;
        background-color: #f9f9f9;
      }
    }

    .user-info {
      display: flex;
      align-items: center;
      margin-bottom: 16rpx;

      .user-label {
        font-size: 24rpx;
        color: #999;
        margin-right: 8rpx;
      }

      .user-name {
        font-size: 24rpx;
        color: #666;
      }
    }

    .evaluate-time {
      font-size: 24rpx;
      color: #999;
      margin-bottom: 20rpx;
    }

    .reply-section {
      background-color: #f8f9fa;
      padding: 20rpx;
      border-radius: 12rpx;
      margin-bottom: 20rpx;

      .reply-header {
        display: flex;
        align-items: center;
        margin-bottom: 12rpx;

        .reply-label {
          font-size: 24rpx;
          color: #666;
          margin-left: 8rpx;
        }
      }

      .reply-content {
        font-size: 26rpx;
        color: #333;
        line-height: 1.5;
        margin-bottom: 8rpx;
      }

      .reply-time {
        font-size: 22rpx;
        color: #999;
      }
    }
  }

  .evaluate-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 20rpx;
    border-top: 1rpx solid #f0f0f0;
    gap: 20rpx;
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;

  .empty-icon {
    width: 200rpx;
    height: 200rpx;
    margin-bottom: 20rpx;
  }

  .empty-text {
    font-size: 28rpx;
    color: #999;
    margin-bottom: 40rpx;
  }
}
</style>
