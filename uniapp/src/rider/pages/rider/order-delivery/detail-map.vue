<template>
    <view class="map-detail">
        <view v-if="loading" class="loading">
            <text>加载中...</text>
        </view>
        
        <view v-else-if="orderDelivery" class="map-container">
            <!-- 地图显示 -->
            <view class="map-wrapper" v-if="hasLocationData">
                <map 
                    :latitude="mapCenter.latitude" 
                    :longitude="mapCenter.longitude"
                    :markers="markers"
                    :show-location="false"
                    class="map"
                    @markertap="onMarkerTap"
                ></map>
            </view>
            
            <!-- 无位置数据提示 -->
            <view v-else class="no-location">
                <text>暂无位置信息</text>
            </view>
        </view>
        
        <view v-else class="error">
            <text>获取订单信息失败</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

// Props定义
const props = defineProps({
    orderDelivery: {
        type: Object,
        required: true
    }
})

// 响应式数据
const loading = ref(false)

// 计算地图中心点
const mapCenter = computed(() => {
    if (!props.orderDelivery?.orderAddress) {
        return { latitude: 39.908823, longitude: 116.397470 } // 默认北京
    }
    
    const lat = parseFloat(props.orderDelivery.orderAddress.reciver_latitude) || 39.908823
    const lng = parseFloat(props.orderDelivery.orderAddress.reciver_longitude) || 116.397470
    
    return { latitude: lat, longitude: lng }
})

// 是否有位置数据
const hasLocationData = computed(() => {
    return props.orderDelivery?.orderAddress?.reciver_latitude && 
           props.orderDelivery?.orderAddress?.reciver_longitude
})

// 地图标记点
const markers = computed(() => {
    const markersList = []
    
    // 收货地址位置
    if (props.orderDelivery?.orderAddress?.reciver_latitude && 
        props.orderDelivery?.orderAddress?.reciver_longitude) {
        markersList.push({
            id: 1,
            latitude: parseFloat(props.orderDelivery.orderAddress.reciver_latitude),
            longitude: parseFloat(props.orderDelivery.orderAddress.reciver_longitude),
            title: props.orderDelivery.orderAddress.reciver_name || '收货地址',
            iconPath: '/static/images/map/reciver.png',
            width: 30,
            height: 30,
            callout: {
                content: `${props.orderDelivery.orderAddress.reciver_name || '收货地址'}`,
                color: '#333',
                fontSize: 12,
                borderRadius: 4,
                bgColor: '#fff',
                padding: 8,
                display: 'ALWAYS'
            }
        })
    }
    
    // 取货地址位置（如果有的话）
    if (props.orderDelivery?.orderAddress?.shipper_latitude && 
        props.orderDelivery?.orderAddress?.shipper_longitude) {
        markersList.push({
            id: 2,
            latitude: parseFloat(props.orderDelivery.orderAddress.shipper_latitude),
            longitude: parseFloat(props.orderDelivery.orderAddress.shipper_longitude),
            title: props.orderDelivery.orderAddress.shipper_name || '取货地址',
            iconPath: '/static/images/map/shop.png',
            width: 30,
            height: 30,
            callout: {
                content: `${props.orderDelivery.orderAddress.shipper_name || '取货地址'}`,
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

// 标记点击事件
const onMarkerTap = (e: any) => {
    console.log('标记点击:', e.detail)
}
</script>

<style scoped lang="scss">
.map-detail {
    background: #f8f8f8;
}

.loading, .error, .no-location {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60rpx 20rpx;
    color: #999;
}

.map-container {
    background: #fff;
}

.map-wrapper {
    width: 100%;
    
    .map {
        width: 100%;
        height: 500rpx;
    }
}
</style>
