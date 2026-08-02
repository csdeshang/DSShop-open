<template>
  <view class="goods-container">
    <!-- 关联商品区域 -->
    <view v-if="goodsList && goodsList.length > 0" class="goods-area">
      
      <!-- 单个商品：显示详细信息 -->
      <view v-if="goodsList.length === 1" class="single-goods">
        <view class="goods-item" @click="handleProductClick(goodsList[0])">
          <image class="goods-image" :src="formatImageUrl(goodsList[0].cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
          <view class="goods-info">
            <text class="goods-name">{{ goodsList[0].goods_name }}</text>
            <view class="goods-bottom">
              <view class="goods-price">
                <text class="price-symbol">¥</text>
                <text class="price-value">{{ formatPrice(goodsList[0].goodsSku?.sku_price || 0) }}</text>
                <text
                  v-if="goodsList[0].goodsSku?.market_price && goodsList[0].goodsSku.market_price > (goodsList[0].goodsSku?.sku_price || 0)"
                  class="market-price">
                  ¥{{ formatPrice(goodsList[0].goodsSku.market_price) }}
                </text>
              </view>
              <view class="goods-stock">
                库存{{ goodsList[0].stock_num || goodsList[0].goodsSku?.sku_stock || 0 }}件
              </view>
            </view>
          </view>
        </view>
      </view>
      
      <!-- 多个商品：显示缩略图 -->
      <view v-else class="multiple-goods">
        <view class="goods-thumbnails" @click="showGoodsPopup">
          <view class="thumbnail-container">
            <view v-for="(goods, index) in goodsList.slice(0, 4)" :key="`thumb-${goods.id}`" 
              class="thumbnail-item" :style="{ zIndex: 4 - index }">
              <image class="thumbnail-image" :src="formatImageUrl(goods.cover_image, ThumbnailPresets.small, 'goods')" mode="aspectFill"></image>
            </view>
          </view>
          <view class="goods-info">
            <text class="goods-count">{{ goodsList.length }}件相关商品</text>
            <text class="goods-tip">点击查看详情</text>
          </view>
          <view class="arrow-icon">
            <uni-icons type="right" size="16" color="rgba(255,255,255,0.8)"></uni-icons>
          </view>
        </view>
      </view>
    </view>
    
    <!-- 商品加载状态 -->
    <view v-else-if="isGoodsLoading" class="goods-loading">
      <uni-icons type="spinner-cycle" size="16" color="#fff" class="loading-icon"></uni-icons>
      <text class="loading-text">商品加载中...</text>
    </view>
    
    <!-- 加载错误状态 -->
    <view v-else-if="loadError" class="goods-error">
      <uni-icons type="warning" size="16" color="#ff6b6b"></uni-icons>
      <text class="error-text">商品加载失败</text>
    </view>
    
    <!-- 商品列表弹窗 -->
    <uni-popup ref="goodsPopup" type="bottom" :mask-click="true">
      <view class="popup-container">
        <view class="popup-header">
          <text class="popup-title">相关商品 ({{ goodsList.length }})</text>
          <view class="close-btn" @click="hideGoodsPopup">
            <uni-icons type="close" size="20" color="#666"></uni-icons>
          </view>
        </view>
        <scroll-view class="popup-content" scroll-y>
          <view class="goods-list">
            <view v-for="(goods, index) in goodsList" :key="`popup-${goods.id}-${index}`" 
              class="goods-item" @click="handleProductClick(goods, true)">
              <image class="goods-image" :src="formatImageUrl(goods.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
              <view class="goods-info">
                <text class="goods-name">{{ goods.goods_name }}</text>
                <view class="goods-bottom">
                  <view class="goods-price">
                    <text class="price-symbol">¥</text>
                    <text class="price-value">{{ formatPrice(goods.goodsSku?.sku_price || 0) }}</text>
                    <text
                      v-if="goods.goodsSku?.market_price && goods.goodsSku.market_price > (goods.goodsSku?.sku_price || 0)"
                      class="market-price">
                      ¥{{ formatPrice(goods.goodsSku.market_price) }}
                    </text>
                  </view>
                  <view class="goods-stock">
                    库存{{ goods.stock_num || goods.goodsSku?.sku_stock || 0 }}件
                  </view>
                </view>
              </view>
            </view>
          </view>
        </scroll-view>
      </view>
    </uni-popup>
  </view>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getVideoShortGoodsList } from '@/video/api/home/videoShort'
import { goGoodsDetailPage } from '@/utils/platform'
// Props
const props = defineProps({
  videoData: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

// 响应式数据
const goodsList = ref([])
const isGoodsLoading = ref(false)
const loadError = ref(false)

// 弹窗引用
const goodsPopup = ref(null)

// 格式化价格
const formatPrice = (price) => {
  const num = parseFloat(price) || 0
  return num.toFixed(2)
}

// 显示商品弹窗
const showGoodsPopup = () => {
  goodsPopup.value?.open()
}

// 隐藏商品弹窗
const hideGoodsPopup = () => {
  goodsPopup.value?.close()
}

// 获取商品数据
const fetchGoodsList = async (videoId) => {
  if (!videoId || isGoodsLoading.value) return

  isGoodsLoading.value = true
  loadError.value = false

  try {
    const result = await getVideoShortGoodsList(videoId)

    if (result.code === 10000) {
      goodsList.value = (result.data || []).map(goods => ({
        ...goods,
        cover_image: formatImageUrl(goods.cover_image, ThumbnailPresets.medium, 'goods'),
        slide_image: formatImageUrl(goods.slide_image, ThumbnailPresets.large, 'goods')
      }))
    } else {
      throw new Error(result.message || '获取商品列表失败')
    }

  } catch (error) {
    console.error('获取商品列表失败:', error)
    loadError.value = true
    goodsList.value = []
  } finally {
    isGoodsLoading.value = false
  }
}

// 事件处理
const handleProductClick = (goods, fromPopup = false) => {
  console.log('点击商品:', goods)
  
  // 如果是从弹窗中点击，先关闭弹窗
  if (fromPopup) {
    hideGoodsPopup()
  }
  
  goGoodsDetailPage(goods)
}

// 监听 videoData 变化
watch(() => props.videoData?.id, (newVideoId) => {
  if (newVideoId) {
    // 重置状态
    goodsList.value = []
    loadError.value = false
    // 获取新的商品数据

    fetchGoodsList(newVideoId)
  }
}, { immediate: true })

// 组件挂载时获取数据
onMounted(() => {
  if (props.videoData?.id) {
    fetchGoodsList(props.videoData.id)
  }
})
</script>

<style lang="scss" scoped>
.goods-container {
  width: 100%;
}

// 商品区域样式
.goods-area {
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5));
  border-radius: 20rpx;
  padding: 20rpx;
  backdrop-filter: blur(10rpx);
  border: 1rpx solid rgba(255, 255, 255, 0.1);
}

// 单个商品样式
.single-goods {
  .goods-item {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
    border-radius: 12rpx;
    padding: 12rpx;
    border: 1rpx solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    
    &:active {
      transform: scale(0.98);
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    }
    
    .goods-image {
      width: 80rpx;
      height: 80rpx;
      border-radius: 8rpx;
      background-color: rgba(255, 255, 255, 0.1);
      margin-right: 15rpx;
      flex-shrink: 0;
    }
    
    .goods-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      
      .goods-name {
        color: #fff;
        font-size: 26rpx;
        font-weight: 500;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
        overflow: hidden;
        margin-bottom: 8rpx;
      }
      
      .goods-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        
        .goods-price {
          display: flex;
          align-items: baseline;
          
          .price-symbol {
            color: #ff6b6b;
            font-size: 18rpx;
            font-weight: bold;
          }
          
          .price-value {
            color: #ff6b6b;
            font-size: 28rpx;
            font-weight: bold;
            margin-left: 2rpx;
          }
          
          .market-price {
            color: rgba(255, 255, 255, 0.5);
            font-size: 18rpx;
            text-decoration: line-through;
            margin-left: 8rpx;
          }
        }
        
        .goods-stock {
          color: rgba(255, 255, 255, 0.7);
          font-size: 18rpx;
          flex-shrink: 0;
        }
      }
    }
  }
}

// 多个商品缩略图样式
.multiple-goods {
  .goods-thumbnails {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.08));
    border-radius: 12rpx;
    padding: 12rpx 15rpx;
    border: 1rpx solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    
    &:active {
      transform: scale(0.98);
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    }
    
    .thumbnail-container {
      position: relative;
      width: 80rpx;
      height: 60rpx;
      margin-right: 15rpx;
      
      .thumbnail-item {
        position: absolute;
        width: 50rpx;
        height: 50rpx;
        border-radius: 8rpx;
        overflow: hidden;
        border: 2rpx solid rgba(255, 255, 255, 0.8);
        
        &:nth-child(1) { left: 0; top: 0; }
        &:nth-child(2) { left: 15rpx; top: 5rpx; }
        &:nth-child(3) { left: 30rpx; top: 10rpx; }
        &:nth-child(4) { left: 45rpx; top: 15rpx; }
        
        .thumbnail-image {
          width: 100%;
          height: 100%;
        }
      }
    }
    
    .goods-info {
      flex: 1;
      
      .goods-count {
        color: #fff;
        font-size: 26rpx;
        font-weight: 500;
        display: block;
        margin-bottom: 4rpx;
      }
      
      .goods-tip {
        color: rgba(255, 255, 255, 0.7);
        font-size: 20rpx;
      }
    }
    
    .arrow-icon {
      margin-left: 10rpx;
    }
  }
}

// 弹窗样式
.popup-container {
  background: #fff;
  border-radius: 20rpx 20rpx 0 0;
  max-height: 80vh;
  overflow: hidden;
  
  .popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx 30rpx 20rpx;
    border-bottom: 1rpx solid #eee;
    
    .popup-title {
      font-size: 32rpx;
      font-weight: bold;
      color: #333;
    }
    
    .close-btn {
      padding: 10rpx;
    }
  }
  
  .popup-content {
    max-height: 60vh;
    
    .goods-list {
      display: flex;
      flex-direction: column;
      gap: 15rpx;
      padding: 20rpx;
    }
    
    .goods-item {
      display: flex;
      align-items: flex-start;
      background: #f8f9fa;
      border-radius: 12rpx;
      padding: 20rpx;
      transition: all 0.3s ease;
      
      &:active {
        transform: scale(0.98);
        background: #e9ecef;
      }
      
      .goods-image {
        width: 120rpx;
        height: 120rpx;
        border-radius: 12rpx;
        background-color: #eee;
        margin-right: 20rpx;
        flex-shrink: 0;
      }
      
      .goods-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 120rpx;
        
        .goods-name {
          color: #333;
          font-size: 30rpx;
          font-weight: 500;
          line-height: 1.4;
          margin-bottom: auto;
          word-break: break-word;
          overflow-wrap: break-word;
          flex: 1;
        }
        
        .goods-bottom {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-top: 15rpx;
          
          .goods-price {
            display: flex;
            align-items: baseline;
            
            .price-symbol {
              color: #ff6b6b;
              font-size: 22rpx;
              font-weight: bold;
            }
            
            .price-value {
              color: #ff6b6b;
              font-size: 36rpx;
              font-weight: bold;
              margin-left: 2rpx;
            }
            
            .market-price {
              color: #999;
              font-size: 22rpx;
              text-decoration: line-through;
              margin-left: 12rpx;
            }
          }
          
          .goods-stock {
            color: #666;
            font-size: 22rpx;
            flex-shrink: 0;
          }
        }
      }
    }
  }
}

// 商品加载状态
.goods-loading {
  background-color: rgba(0, 0, 0, 0.6);
  padding: 20rpx;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  gap: 10rpx;

  .loading-icon {
    animation: spin 1s linear infinite;
  }

  .loading-text {
    color: #fff;
    font-size: 24rpx;
  }
}

// 旋转动画
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

// 加载错误状态
.goods-error {
  background-color: rgba(0, 0, 0, 0.6);
  padding: 20rpx;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  gap: 10rpx;

  .error-text {
    color: #fff;
    font-size: 24rpx;
  }
}
</style>
