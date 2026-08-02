<template>
    <view class="quick-store-header">
        <!-- 顶部导航栏 -->
        <view class="header-nav">
            <view class="nav-left" @click="goBack">
                <uni-icons type="left" size="22" color="#333"></uni-icons>
            </view>
            <view class="nav-right" @click="goToPage({ url: '/home/pages/index', type: 'reLaunch' })">
                <uni-icons type="shop" size="24" color="#333"></uni-icons>
            </view>
        </view>
        
        <!-- 店铺信息卡片 -->
        <view class="store-info-card">
            <view class="store-basic-info">
                <image 
                    :src="formatImageUrl(storeInfo.store_logo || '', ThumbnailPresets.compact, 'store_logo')" 
                    class="store-logo" 
                    mode="aspectFill"
                />
                <view class="store-details">
                    <text class="store-name">{{ storeInfo.store_name }}</text>
                    <view class="store-meta">
                        <view class="rating-info">
                            <uni-rate :value="avgScore" readonly size="12"></uni-rate>
                            <text class="rating-text">{{ avgScore.toFixed(1) }}</text>
                            <text class="order-count">({{ storeInfo.sales_num || 0 }}单)</text>
                        </view>
                        <view class="delivery-info">
                            <text class="business-status">{{ storeInfo.platform === 'food' ? '外卖配送' : '商城配送' }}</text>
                        </view>
                    </view>
                </view>
                <view class="store-status">
                    <view 
                        class="status-badge" 
                        :class="{ 'open': storeInfo.store_business_status === 1, 'closed': storeInfo.store_business_status !== 1 }"
                    >
                        {{ storeInfo.store_business_status === 1 ? '营业中' : '休息中' }}
                    </view>
                </view>
            </view>
            
            <view class="store-intro" v-if="storeInfo.store_introduction">
                <text class="intro-text">{{ storeInfo.store_introduction }}</text>
            </view>
            
            <view class="store-tags">
                <view class="tag platform-tag">
                    {{ platformName }}
                </view>
                <view class="tag" v-if="storeInfo.is_recommend">
                    推荐店铺
                </view>
                <view class="tag activity-tag" v-if="storeInfo.platform === 'mall'">
                    正品保障
                </view>
                <view class="tag activity-tag" v-if="storeInfo.platform === 'food'">
                    食品安全
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

interface StoreInfo {
    id?: number
    platform?: string
    store_name?: string
    store_logo?: string
    store_introduction?: string
    store_business_status?: number
    avg_describe_score?: string
    avg_logistics_score?: string
    avg_service_score?: string
    sales_num?: number
    is_recommend?: number
    is_enabled?: number
}

const props = defineProps<{
    storeInfo: StoreInfo
}>()

// 计算平均评分
const avgScore = computed(() => {
    const descScore = parseFloat(props.storeInfo.avg_describe_score || '0')
    const logisticsScore = parseFloat(props.storeInfo.avg_logistics_score || '0')
    const serviceScore = parseFloat(props.storeInfo.avg_service_score || '0')
    
    if (descScore === 0 && logisticsScore === 0 && serviceScore === 0) {
        return 4.5 // 默认评分
    }
    
    return (descScore + logisticsScore + serviceScore) / 3
})

// 平台名称
const platformName = computed(() => {
    const platformMap: Record<string, string> = {
        'mall': '商城',
        'food': '外卖',
        'house': '房产',
        'kms': '知识付费'
    }
    return platformMap[props.storeInfo.platform || 'mall'] || '商城'
})

// 返回上一页
const goBack = () => {
    uni.navigateBack()
}


</script>

<style lang="scss" scoped>
.quick-store-header {
    background: #fff;
    margin-bottom: 20rpx;
}

.header-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 80rpx;
    padding: 0 30rpx;
    background: #fff;
    border-bottom: 1rpx solid #f0f0f0;
    
    .nav-left, .nav-right {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60rpx;
        height: 60rpx;
        cursor: pointer;
        border-radius: 50%;
        transition: background-color 0.2s;
        
        &:hover {
            background-color: #f5f5f5;
        }
    }
}

.store-info-card {
    padding: 30rpx;
    background: #fff;
    border-radius: 20rpx 20rpx 0 0;
    position: relative;
    z-index: 2;
}

.store-basic-info {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20rpx;
    
    .store-logo {
        width: 120rpx;
        height: 120rpx;
        border-radius: 12rpx;
        margin-right: 20rpx;
        border: 2rpx solid #fff;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.1);
    }
    
    .store-details {
        flex: 1;
        
        .store-name {
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
            margin-bottom: 10rpx;
            display: block;
        }
        
        .store-meta {
            .rating-info {
                display: flex;
                align-items: center;
                margin-bottom: 8rpx;
                
                .rating-text {
                    font-size: 24rpx;
                    color: #ff6600;
                    margin-left: 8rpx;
                }
                
                .order-count {
                    font-size: 22rpx;
                    color: #999;
                    margin-left: 8rpx;
                }
            }
            
            .delivery-info {
                .business-status {
                    font-size: 22rpx;
                    color: #666;
                }
            }
        }
    }
    
    .store-status {
        .status-badge {
            padding: 8rpx 16rpx;
            border-radius: 20rpx;
            font-size: 22rpx;
            
            &.open {
                background: #e6f7e6;
                color: #52c41a;
            }
            
            &.closed {
                background: #fff2f0;
                color: #ff4d4f;
            }
        }
    }
}

.store-intro {
    padding: 15rpx 20rpx;
    background: #f8f9fa;
    border-radius: 8rpx;
    margin-bottom: 20rpx;
    
    .intro-text {
        font-size: 24rpx;
        color: #666;
        line-height: 1.5;
    }
}

.store-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 15rpx;
    
    .tag {
        padding: 8rpx 16rpx;
        font-size: 22rpx;
        border-radius: 16rpx;
        background: #f0f0f0;
        color: #666;
        
        &.platform-tag {
            background: #e6f7ff;
            color: #1890ff;
        }
        
        &.activity-tag {
            background: #fff2f0;
            color: #ff4d4f;
        }
    }
}
</style> 