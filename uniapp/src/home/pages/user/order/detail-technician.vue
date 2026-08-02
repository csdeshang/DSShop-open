<template>
    <view class="technician-detail">
        <view v-if="loading" class="loading">
            <text>加载中...</text>
        </view>
        
        <view v-else-if="technicianInfo" class="technician-info">
            <!-- 地图显示 -->
            <view class="map-container" v-if="technicianInfo.technician_latitude && technicianInfo.technician_longitude">
                <map 
                    :latitude="technicianInfo.technician_latitude" 
                    :longitude="technicianInfo.technician_longitude"
                    :markers="markers"
                    :show-location="false"
                    class="map"
                ></map>
            </view>
            
            <!-- 师傅基本信息 -->
            <view class="technician-header">
                <image class="avatar" :src="formatImageUrl(technicianInfo.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
                <view class="technician-basic">
                    <text class="name">{{ technicianInfo.name || '师傅' }}</text>
                    <text class="mobile">{{ technicianInfo.mobile || '' }}</text>
                </view>
                <view class="status">
                    <text class="status-text refresh-btn" @click="refreshLocation">
                        刷新位置
                    </text>
                    <text class="update-time" v-if="technicianInfo.technician_latitude && technicianInfo.technician_longitude">
                        {{ technicianInfo.technician_loc_time }}
                    </text>
                </view>
            </view>
        </view>
        
        <view v-else class="error">
            <text>获取师傅信息失败</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { getTblOrderTechnicianInfo } from '@/home/api/tbl-order/tblOrder'
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
const technicianInfo = ref<any>(null)

// 地图标记点
const markers = computed(() => {
    if (!technicianInfo.value?.technician_latitude || !technicianInfo.value?.technician_longitude) {
        return []
    }
    
    const markersList = []
    
    // 师傅位置
    markersList.push({
        id: 1,
        latitude: technicianInfo.value.technician_latitude,
        longitude: technicianInfo.value.technician_longitude,
        title: technicianInfo.value.name || '师傅',
        iconPath: '/static/images/map/technician.png',
        width: 30,
        height: 30,
        callout: {
            content: `${technicianInfo.value.name || '师傅'}`,
            color: '#333',
            fontSize: 12,
            borderRadius: 4,
            bgColor: '#fff',
            padding: 8,
            display: 'ALWAYS'
        }
    })
    
    // 收货地址位置
    if (props.orderDetail?.orderAddress?.reciver_latitude && props.orderDetail?.orderAddress?.reciver_longitude) {
        markersList.push({
            id: 2,
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

// 加载师傅信息
const loadTechnicianInfo = async () => {
    if (!props.orderDetail?.id) return
    
    loading.value = true
    try {
        const response = await getTblOrderTechnicianInfo(props.orderDetail.id)
        if (response.code === 10000) {
            technicianInfo.value = response.data
        } else {
            console.error('获取师傅信息失败:', response.message)
        }
    } catch (error) {
        console.error('获取师傅信息出错:', error)
    } finally {
        loading.value = false
    }
}

// 刷新位置
const refreshLocation = () => {
    loadTechnicianInfo()
}

// 组件挂载时加载数据
onMounted(() => {
    loadTechnicianInfo()
})
</script>

<style scoped lang="scss">
.technician-detail {
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

.technician-info {
    background: #fff;
    margin-bottom: 20rpx;
}

.technician-header {
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
    
    .technician-basic {
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