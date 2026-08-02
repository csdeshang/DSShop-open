<template>
    <view class="rider-detail">
        <view v-if="loading" class="loading">
            <text>加载中...</text>
        </view>
        
        <view v-else-if="riderInfo" class="rider-info">
            <!-- 地图显示 -->
            <view class="map-container" v-if="riderInfo.rider_latitude && riderInfo.rider_longitude">
                <map 
                    :latitude="riderInfo.rider_latitude" 
                    :longitude="riderInfo.rider_longitude"
                    :markers="markers"
                    :show-location="false"
                    class="map"
                ></map>
            </view>
            
            <!-- 配送员基本信息 -->
            <view class="rider-header">
                <image class="avatar" :src="formatImageUrl(riderInfo.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"/>
                <view class="rider-basic">
                    <text class="name">{{ riderInfo.name || '配送员' }}</text>
                    <text class="mobile">{{ riderInfo.mobile || '' }}</text>
                </view>
                <view class="status">
                    <text class="status-text refresh-btn" @click="refreshLocation">
                        刷新位置
                    </text>
                    <text class="update-time" v-if="riderInfo.rider_latitude && riderInfo.rider_longitude">
                        {{ riderInfo.rider_loc_time }}
                    </text>
                </view>
            </view>
        </view>
        
        <view v-else class="error">
            <text>获取配送员信息失败</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { getTblOrderRiderInfo } from '@/home/api/tbl-order/tblOrder'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// Props定义
const props = defineProps({
    orderDetail: {
        type: Object,
        required: true
    }
})

// 响应式数据
const loading = ref(false)
const riderInfo = ref<any>(null)

// 地图标记点
const markers = computed(() => {
    if (!riderInfo.value?.rider_latitude || !riderInfo.value?.rider_longitude) {
        return []
    }
    
    const markersList = []
    
    // 配送员位置
    markersList.push({
        id: 1,
        latitude: riderInfo.value.rider_latitude,
        longitude: riderInfo.value.rider_longitude,
        title: riderInfo.value.name || '配送员',
        iconPath: '/static/images/map/rider.png',
        width: 30,
        height: 30,
        callout: {
            content: `${riderInfo.value.name || '配送员'}`,
            color: '#333',
            fontSize: 12,
            borderRadius: 4,
            bgColor: '#fff',
            padding: 8,
            display: 'ALWAYS'
        }
    })
    
    // 发货地址位置（店铺位置）
    if (props.orderDetail?.orderAddress?.shipper_latitude && props.orderDetail?.orderAddress?.shipper_longitude) {
        markersList.push({
            id: 2,
            latitude: props.orderDetail.orderAddress.shipper_latitude,
            longitude: props.orderDetail.orderAddress.shipper_longitude,
            title: props.orderDetail.orderAddress.shipper_name || '发货地址',
            iconPath: '/static/images/map/store.png',
            width: 30,
            height: 30,
            callout: {
                content: `${props.orderDetail.orderAddress.shipper_name || '发货地址'}`,
                color: '#333',
                fontSize: 12,
                borderRadius: 4,
                bgColor: '#fff',
                padding: 8,
                display: 'ALWAYS'
            }
        })
    }
    
    // 收货地址位置
    if (props.orderDetail?.orderAddress?.reciver_latitude && props.orderDetail?.orderAddress?.reciver_longitude) {
        markersList.push({
            id: 3,
            latitude: props.orderDetail.orderAddress.reciver_latitude,
            longitude: props.orderDetail.orderAddress.reciver_longitude,
            title: props.orderDetail.orderAddress.reciver_name || '收货地址',
            iconPath: '/static/images/map/reciver.png',
            width: 30,
            height: 30,
            callout: {
                content: props.orderDetail.orderAddress.reciver_name || '收货地址',
                color: '#333',
                fontSize: 12,
                borderRadius: 4,
                bgColor: '#fff',
                padding: 8,
                display: 'ALWAYS'
            }
        })
    }
    
    return markersList
})

// 加载骑手信息
const loadRiderInfo = async () => {
    if (!props.orderDetail?.id) return
    
    loading.value = true
    try {
        const response = await getTblOrderRiderInfo(props.orderDetail.id)
        if (response.code === 10000) {
            riderInfo.value = response.data
        } else {
            console.error('获取配送员信息失败:', response.message)
        }
    } catch (error) {
        console.error('获取配送员信息出错:', error)
    } finally {
        loading.value = false
    }
}

// 刷新位置
const refreshLocation = () => {
    loadRiderInfo()
}

// 组件挂载时加载数据
onMounted(() => {
    loadRiderInfo()
})
</script>

<style scoped lang="scss">
.rider-detail {
    background: #f8f8f8;
}

.loading, .error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60rpx 20rpx;
    color: #999;
}

.rider-info {
    background: #fff;
    margin-bottom: 20rpx;
}

.rider-header {
    display: flex;
    align-items: center;
    margin-bottom: 24rpx;
    padding: 24rpx 24rpx 20rpx 24rpx;
    border-bottom: 1rpx solid #f0f0f0;
    
    .avatar {
        width: 80rpx;
        height: 80rpx;
        border-radius: 50%;
        margin-right: 20rpx;
    }
    
    .rider-basic {
        flex: 1;
        
        .name {
            display: block;
            font-size: 32rpx;
            font-weight: 600;
            color: #333;
            margin-bottom: 8rpx;
        }
        
        .mobile {
            font-size: 26rpx;
            color: #666;
        }
    }
    
    .status {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        
        .status-text {
            padding: 8rpx 16rpx;
            border-radius: 20rpx;
            font-size: 24rpx;
            color: #007aff;
            background: rgba(0, 122, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 8rpx;
            
            &:active {
                opacity: 0.7;
                transform: scale(0.95);
            }
        }
        
        .update-time {
            font-size: 22rpx;
            color: #999;
        }
    }
}

.map-container {
    width: 100%;
    
    .map {
        width: 100%;
        height: 500rpx;
    }
}
</style>