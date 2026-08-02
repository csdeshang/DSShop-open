<template>
    <!-- 当前位置信息 -->
    <view class="location-card">
        <view class="location-header">
            <view class="location-title">
                <uni-icons type="location" size="18" color="#4CAF50"></uni-icons>
                <text>当前位置</text>
            </view>
            <view class="location-refresh" @click="refreshLocation">
                <uni-icons type="refreshempty" size="16" color="#999" :class="{ 'rotating': locationLoading }"></uni-icons>
            </view>
        </view>
        
        <view class="location-content">
            <view class="location-address" v-if="lbsStore.address">
                {{ lbsStore.address }}
            </view>
            <view class="location-placeholder" v-else>
                {{ locationLoading ? '正在获取位置...' : '位置信息获取失败，点击右上角刷新' }}
            </view>
            
            <view class="location-details" v-if="lbsStore.city">
                <text class="location-city">{{ lbsStore.city }}</text>
                <text class="location-district" v-if="lbsStore.district">{{ lbsStore.district }}</text>
                <text class="location-coords" v-if="lbsStore.latitude && lbsStore.longitude">
                    {{ lbsStore.latitude.toFixed(4) }}, {{ lbsStore.longitude.toFixed(4) }}
                </text>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLbsStore } from '@/stores/modules/lbs'

const lbsStore = useLbsStore()
const locationLoading = ref(false)

// 获取位置信息
const getLocationInfo = async () => {
    try {
        locationLoading.value = true
        // 检查位置信息是否过期（1天过期时间），如过期则更新位置信息
        await lbsStore.getLocation(true, 24 * 60 * 60)
    } catch (error) {
        console.error('获取位置失败:', error)
    } finally {
        locationLoading.value = false
    }
}

// 刷新位置信息
const refreshLocation = async () => {
    try {
        locationLoading.value = true
        // 强制刷新位置信息
        await lbsStore.getLocation(false)
        uni.showToast({
            title: '位置已刷新',
            icon: 'success'
        })
    } catch (error) {
        console.error('刷新位置失败:', error)
        uni.showToast({
            title: '位置刷新失败',
            icon: 'none'
        })
    } finally {
        locationLoading.value = false
    }
}

// 组件挂载时自动获取位置
onMounted(() => {
    getLocationInfo()
})

// 暴露方法给父组件调用（如果需要）
defineExpose({
    getLocationInfo,
    refreshLocation
})
</script>

<style lang="scss" scoped>
// 位置信息卡片
.location-card {
    margin: 0 20rpx 20rpx;
    background: #fff;
    border-radius: 16rpx;
    padding: 24rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.06);
    border: 1rpx solid #f0f0f0;

    .location-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16rpx;

        .location-title {
            display: flex;
            align-items: center;
            font-size: 28rpx;
            font-weight: 600;
            color: #333;

            uni-icons {
                margin-right: 8rpx;
            }
        }

        .location-refresh {
            padding: 8rpx;
            border-radius: 50%;
            transition: all 0.3s;

            &:active {
                background: #f5f5f5;
            }

            .rotating {
                animation: rotate 1s linear infinite;
            }
        }
    }

    .location-content {
        .location-address {
            font-size: 26rpx;
            color: #333;
            line-height: 1.5;
            margin-bottom: 12rpx;
        }

        .location-placeholder {
            font-size: 26rpx;
            color: #999;
            line-height: 1.5;
            margin-bottom: 12rpx;
        }

        .location-details {
            display: flex;
            flex-wrap: wrap;
            gap: 16rpx;
            font-size: 22rpx;

            .location-city {
                color: #4CAF50;
                font-weight: 500;
            }

            .location-district {
                color: #666;
            }

            .location-coords {
                color: #999;
                font-family: monospace;
            }
        }
    }
}

// 旋转动画
@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
