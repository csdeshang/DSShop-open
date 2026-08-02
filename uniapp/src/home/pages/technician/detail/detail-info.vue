<template>
    <view class="technician-info">
        <view class="info-container">
            <!-- 师傅头像和基本信息 -->
            <view class="technician-header">
                <view class="avatar-container">
                    <image class="avatar" :src="formatImageUrl(technicianDetail.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
                </view>
                <view class="header-info">
                    <view class="name-row">
                        <text class="name">{{ technicianDetail.name }}</text>
                        <view class="service-tag">可服务</view>
                    </view>
                    <view class="stats-row">
                        <text class="stat-item">{{ technicianDetail.work_years }}年经验</text>
                        <text class="stat-item">{{ technicianDetail.visit_count }}浏览</text>
                        <text class="stat-item">{{ technicianDetail.service_count }}单</text>
                    </view>
                    <view class="score-row">
                        <uni-rate :value="Number(technicianDetail.avg_score)" size="18" readonly allow-half ></uni-rate>
                        <text class="score-value">{{ technicianDetail.avg_score || '0.00' }}</text>
                    </view>
                </view>
            </view>

            <!-- 师傅介绍 -->
            <view class="description-container" v-if="technicianDetail.description">
                <text class="description-text">{{ technicianDetail.description }}</text>
            </view>

            <!-- 服务保障 -->
            <view class="service-guarantee">
                <view class="guarantee-title">服务保障</view>
                <view class="guarantee-items">
                    <view class="guarantee-item">
                        <uni-icons type="checkmarkempty" size="18" color="#07c160"></uni-icons>
                        <text>实名认证</text>
                    </view>
                    <view class="guarantee-item">
                        <uni-icons type="checkmarkempty" size="18" color="#07c160"></uni-icons>
                        <text>资质证书</text>
                    </view>
                    <view class="guarantee-item">
                        <uni-icons type="checkmarkempty" size="18" color="#07c160"></uni-icons>
                        <text>爽约包退</text>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';

const props = defineProps({
    technicianDetail: {
        type: Object,
        default: () => ({})
    }
});

</script>

<style lang="scss" scoped>
.technician-info {
    background-color: #f5f7fa;
    padding: 20rpx;

    .info-container {
        background-color: #fff;
        border-radius: 20rpx;
        overflow: hidden;
        box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
        padding-bottom: 30rpx;
    
        // 师傅头像和基本信息
        .technician-header {
            display: flex;
            padding: 30rpx;
            
            .avatar-container {
                width: 120rpx;
                height: 120rpx;
                margin-right: 30rpx;
                
                .avatar {
                    width: 120rpx;
                    height: 120rpx;
                    border-radius: 50%;
                    border: 4rpx solid #fff;
                    box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.1);
                }
            }
            
            .header-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
                
                .name-row {
                    display: flex;
                    align-items: center;
                    margin-bottom: 16rpx;
                    
                    .name {
                        font-size: 36rpx;
                        font-weight: bold;
                        color: #333;
                        margin-right: 20rpx;
                    }
                    
                    .service-tag {
                        background-color: #4cd964;
                        color: #fff;
                        font-size: 22rpx;
                        padding: 4rpx 12rpx;
                        border-radius: 6rpx;
                    }
                }
                
                .stats-row {
                    display: flex;
                    margin-bottom: 16rpx;
                    
                    .stat-item {
                        font-size: 24rpx;
                        color: #666;
                        margin-right: 20rpx;
                        
                        &:last-child {
                            margin-right: 0;
                        }
                    }
                }
                
                .score-row {
                    display: flex;
                    align-items: center;
                    
                    .score-value {
                        margin-left: 10rpx;
                        font-size: 24rpx;
                        color: #FF9500;
                    }
                }
            }
        }
        
        .description-container {
            padding: 0 30rpx 20rpx;
            
            .description-text {
                font-size: 26rpx;
                color: #666;
                line-height: 1.6;
            }
        }
        
        .service-guarantee {
            margin: 0 30rpx;
            padding: 20rpx;
            background-color: #f8f8f8;
            border-radius: 12rpx;
            
            .guarantee-title {
                font-size: 28rpx;
                font-weight: bold;
                color: #333;
                margin-bottom: 16rpx;
            }
            
            .guarantee-items {
                display: flex;
                flex-wrap: wrap;
                
                .guarantee-item {
                    display: flex;
                    align-items: center;
                    margin-right: 30rpx;
                    margin-bottom: 10rpx;
                    
                    text {
                        font-size: 24rpx;
                        color: #666;
                        margin-left: 6rpx;
                    }
                }
            }
        }
    }
}
</style>
