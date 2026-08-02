<template>
  <uni-popup ref="popupRef" type="bottom" :safe-area="true">
    <view class="map-container">
      <!-- 头部 -->
      <view class="map-header">
        <view class="header-left" @click="close">
          <uni-icons type="left" size="20" color="#333"></uni-icons>
          <text class="back-text">返回</text>
        </view>
        <text class="header-title">选择位置</text>
        <view class="header-right" @click="confirmSelect">
          <text class="confirm-text">确定</text>
        </view>
      </view>

      <!-- 搜索框 -->
      <view class="search-container">
        <view class="search-box">
          <uni-icons type="search" size="16" color="#999"></uni-icons>
          <input class="search-input" placeholder="搜索地点" v-model="searchKeyword" @input="onSearchInput" />
          <view v-if="searchKeyword" class="clear-btn" @click="clearSearch">
            <uni-icons type="clear" size="14" color="#999"></uni-icons>
          </view>
        </view>
      </view>

      <!-- 地图区域 -->
      <view class="map-wrapper">
        <map :latitude="mapCenter.latitude" :longitude="mapCenter.longitude" :scale="mapScale" :markers="markers"
          :show-location="true" :enable-3D="false" :enable-overlooking="false" :enable-rotate="false" @tap="onMapTap"
          @regionchange="onRegionChange" class="map-view">
          <!-- 使用默认的中心标记点，不需要自定义cover-view -->
        </map>

        <!-- 中心点标记图标（覆盖在地图上方） -->
        <view class="center-marker">
          <view class="marker-icon"></view>
        </view>
      </view>

      <!-- 位置列表 -->
      <view class="location-list">
        <view class="list-header">
          <text class="list-title">{{ searchKeyword ? '搜索结果' : '附近位置' }}</text>
          <view class="location-btn" @click="getCurrentLocation">
            <uni-icons type="location" size="16" color="#67c23a"></uni-icons>
            <text class="btn-text">获取当前位置</text>
          </view>
        </view>

        <scroll-view scroll-y class="location-scroll">
          <!-- 位置列表 -->
          <view class="location-results">
            <view v-for="(item, index) in locationList" :key="`location-${index}`" class="location-item"
              :class="{ active: selectedLocation?.id === item.id }" @click="selectLocation(item)">
              <view class="location-info">
                <text class="location-name">{{ item.name }}</text>
                <text class="location-address">{{ item.address }}</text>
                <!-- <text class="location-distance" v-if="item.distance">{{ item.distance }}m</text> -->
              </view>
              <!-- 固定宽度的图标区域 -->
              <view class="selected-icon-area">
                <view v-if="selectedLocation?.id === item.id" class="selected-icon">
                  <uni-icons type="checkmarkempty" size="20" color="#67c23a"></uni-icons>
                </view>
              </view>
            </view>
          </view>

          <!-- 空状态 -->
          <view v-if="!loading && locationList.length === 0" class="empty-state">
            <text class="empty-text">{{ searchKeyword ? '未找到相关位置' : '暂无位置信息' }}</text>
          </view>
        </scroll-view>
      </view>
    </view>
  </uni-popup>
</template>

<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue'
import { debounce } from '@/utils/util'
import { 
  getAroundAddressList, 
} from '@/api/system/sysLbs'
import { getCurrentPosition } from '@/utils/location'

// 位置信息接口
interface LocationItem {
  id: string
  name: string
  address: string
  latitude: number
  longitude: number
  distance?: number
  city?: string
  province?: string
  district?: string
  street?: string
  adcode?: string
  citycode?: string
}

// 打开地图选择器的位置参数接口（只需要经纬度）
interface OpenLocationParams {
  latitude: number
  longitude: number
}




// Events 定义
const emit = defineEmits<{
  confirm: [location: LocationItem]
  cancel: []
}>()

// 弹窗引用
const popupRef = ref()

// 地图中心点
const mapCenter = reactive({
  latitude: 39.908692,  // 默认北京
  longitude: 116.397477
})

// 地图缩放级别
const mapScale = ref(16)

// 地图标记点
const markers = ref([])

// 搜索关键词
const searchKeyword = ref('')

// 加载状态
const loading = ref(false)

// 位置列表（统一管理搜索结果和附近位置）
const locationList = ref<LocationItem[]>([])

// 当前选中的位置
const selectedLocation = ref<LocationItem | null>(null)

/**
 * 打开地图选择器
 * @param location 可选的初始位置（只需要经纬度）
 */
const open = (location?: OpenLocationParams) => {
  if (location && location.latitude && location.longitude) {
    mapCenter.latitude = location.latitude
    mapCenter.longitude = location.longitude
    selectedLocation.value = null // 重置选中状态，让用户重新选择
  } else {
    // 获取当前位置
    getCurrentLocation()
  }
  
  popupRef.value.open()
  fetchLocationList()
}

/**
 * 关闭地图选择器
 */
const close = () => {
  popupRef.value.close()
  resetData()
  emit('cancel')
}

/**
 * 重置数据
 */
const resetData = () => {
  searchKeyword.value = ''
  locationList.value = []
  selectedLocation.value = null
}

/**
 * 获取当前位置
 */
const getCurrentLocation = async () => {
  try {
    const position: any = await getCurrentPosition()
    mapCenter.latitude = position.latitude
    mapCenter.longitude = position.longitude
    fetchLocationList()
  } catch (err) {
    console.error('获取位置失败:', err)
    uni.showToast({
      title: '获取位置失败',
      icon: 'none'
    })
  }
}

/**
 * 地图点击事件
 */
const onMapTap = (e: any) => {
  const { latitude, longitude } = e.detail
  mapCenter.latitude = latitude
  mapCenter.longitude = longitude
  
  // 重新获取位置，让用户从列表中选择
  fetchLocationList()
}

/**
 * 地图区域变化事件
 */
const onRegionChange = (e: any) => {
  if (e.detail.type === 'end') {
    const { centerLocation } = e.detail
    if (centerLocation) {
      mapCenter.latitude = centerLocation.latitude
      mapCenter.longitude = centerLocation.longitude
      // 如果没有搜索关键词，则重新获取位置
      if (!searchKeyword.value) {
        fetchLocationList()
      }
    }
  }
}

/**
 * 搜索输入事件
 */
const onSearchInput = (e: any) => {
  const value = e.detail.value
  if (value.length > 0) {
    debouncedFetchLocations()
  } else {
    // 清空搜索关键词时，重新获取附近位置
    fetchLocationList()
  }
}

/**
 * 获取位置列表（统一方法）
 */
const fetchLocationList = async () => {
  if (!mapCenter.latitude || !mapCenter.longitude) return
  
  loading.value = true
  
  try {
    const params: any = {
      latitude: mapCenter.latitude,
      longitude: mapCenter.longitude
    }
    
    // 如果有搜索关键词，添加keyword参数
    if (searchKeyword.value.trim()) {
      params.keyword = searchKeyword.value
    }
    
    const result = await getAroundAddressList(params)
    
    if (result.code === 10000 && result.data) {
      // 转换API返回的数据格式
      locationList.value = result.data.map((item: any, index: number) => ({
        id: `location_${index}`,
        name: item.name,
        address: item.address,
        latitude: parseFloat(item.latitude),
        longitude: parseFloat(item.longitude),
        distance: item.distance,
        city: item.city,
        province: item.province,
        district: item.district,
        street: item.street,
        adcode: item.adcode,
        citycode: item.citycode
      }))
      
      // 如果没有选中位置，默认选中第一个
      if (!selectedLocation.value && locationList.value.length > 0) {
        selectedLocation.value = locationList.value[0]
      }
    } else {
      locationList.value = []
      console.error('获取位置失败:', result.message)
    }
  } catch (error) {
    console.error('获取位置失败:', error)
    locationList.value = []
    uni.showToast({
      title: searchKeyword.value ? '搜索失败' : '获取位置失败',
      icon: 'none'
    })
  } finally {
    loading.value = false
  }
}

// 防抖获取位置
const debouncedFetchLocations = debounce(fetchLocationList, 500)

/**
 * 清除搜索
 */
const clearSearch = () => {
  searchKeyword.value = ''
  fetchLocationList() // 清除搜索后重新获取附近位置
}


/**
 * 选择位置
 */
const selectLocation = (location: LocationItem) => {
  selectedLocation.value = location
  mapCenter.latitude = location.latitude
  mapCenter.longitude = location.longitude
}

/**
 * 确认选择
 */
const confirmSelect = () => {
  if (!selectedLocation.value) {
    uni.showToast({
      title: '请选择位置',
      icon: 'none'
    })
    return
  }

  // 触发确认事件，返回选中的位置
  emit('confirm', selectedLocation.value)

  close()
}

// 监听搜索关键词变化
watch(() => searchKeyword.value, (newVal) => {
  if (!newVal) {
    fetchLocationList() // 清空关键词时重新获取附近位置
  }
})

// 暴露方法
defineExpose({
  open,
  close
})
</script>

<style scoped lang="scss">
.map-container {
  width: 100%;
  height: 100vh;
  background-color: #fff;
  display: flex;
  flex-direction: column;
  /* 小程序适配 */
  /* #ifdef MP-WEIXIN */
  height: calc(100vh - env(safe-area-inset-top) - env(safe-area-inset-bottom));
  padding-top: env(safe-area-inset-top);
  /* #endif */

  .map-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20rpx 30rpx;
    border-bottom: 1rpx solid #eee;
    background-color: #fff;
    /* 小程序适配 */
    /* #ifdef MP-WEIXIN */
    padding-top: calc(20rpx + env(safe-area-inset-top));
    /* #endif */

    .header-left {
      display: flex;
      align-items: center;

      .back-text {
        margin-left: 8rpx;
        font-size: 28rpx;
        color: #333;
      }
    }

    .header-title {
      font-size: 32rpx;
      font-weight: 600;
      color: #333;
    }

    .header-right {
      .confirm-text {
        font-size: 28rpx;
        color: #67c23a;
      }
    }
  }

  .search-container {
    padding: 10rpx 30rpx;
    background-color: #f8f8f8;

    .search-box {
      display: flex;
      align-items: center;
      background-color: #fff;
      border-radius: 50rpx;
      padding: 20rpx 30rpx;
      box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.1);

      .search-input {
        flex: 1;
        margin-left: 20rpx;
        font-size: 28rpx;
        color: #333;
      }

      .clear-btn {
        margin-left: 20rpx;
        padding: 10rpx;
      }
    }
  }

  .map-wrapper {
    height: 500rpx; // 固定地图高度，不再使用flex: 1
    position: relative;

    .map-view {
      width: 100%;
      height: 100%;
    }

    .center-marker {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -100%);
      z-index: 999;
      pointer-events: none; // 避免阻挡地图事件

      .marker-icon {
        width: 40rpx;
        height: 40rpx;
        background-color: #ff6b6b;
        border: 4rpx solid #fff;
        border-radius: 50%;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.3);
        position: relative;

        // 添加一个小尖角
        &::after {
          content: '';
          position: absolute;
          top: 100%;
          left: 50%;
          transform: translateX(-50%);
          width: 0;
          height: 0;
          border-left: 8rpx solid transparent;
          border-right: 8rpx solid transparent;
          border-top: 12rpx solid #ff6b6b;
        }
      }
    }
  }

  .location-list {
    flex: 1; // 使用剩余空间
    background-color: #fff;
    border-top: 1rpx solid #eee;
    min-height: 700rpx; // 保证最小高度

    .list-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20rpx 30rpx;
      border-bottom: 1rpx solid #f5f5f5;

      .list-title {
        font-size: 30rpx;
        font-weight: 600;
        color: #333;
      }

      .location-btn {
        display: flex;
        align-items: center;

        .btn-text {
          margin-left: 6rpx;
          font-size: 26rpx;
          color: #67c23a;
        }
      }
    }

    .location-scroll {
      height: calc(100% - 100rpx); // 减少header高度，增加列表空间
      min-height: 650rpx; // 保证最小滚动高度
    }

    .location-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20rpx 30rpx;
      border-bottom: 1rpx solid #f5f5f5;

      &.active {
        background-color: #f0f9ff;
      }

      .location-info {
        flex: 1;
        margin-right: 20rpx; // 确保与右侧有固定间距

        .location-name {
          display: block;
          font-size: 28rpx;
          font-weight: 500;
          color: #333;
          margin-bottom: 4rpx;
          line-height: 1.3;
        }

        .location-address {
          display: block;
          font-size: 24rpx;
          color: #666;
          line-height: 1.3;
        }

        .location-distance {
          font-size: 22rpx;
          color: #999;
        }
      }

      // 固定宽度的图标区域
      .selected-icon-area {
        width: 60rpx; // 固定宽度
        height: 60rpx; // 固定高度
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        .selected-icon {
          // 移除额外的margin
        }
      }
    }

    .empty-state {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 400rpx;

      .empty-text {
        font-size: 28rpx;
        color: #999;
      }
    }
  }
}
</style>