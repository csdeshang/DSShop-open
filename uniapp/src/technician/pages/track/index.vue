<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="track-list">
                <view class="track-item" v-for="item in dataList" :key="item.id">
                    <view class="item-header">
                        <view class="type-badge">
                            位置记录
                        </view>
                        <view class="time">
                            {{ item.create_at }}
                        </view>
                    </view>
                    
                    <view class="item-body">
                        <view class="info-row">
                            <text class="label">位置：</text>
                            <text class="value">{{ item.address || '未知位置' }}</text>
                        </view>
                        
                        <view class="info-row">
                            <text class="label">经度：</text>
                            <text class="value">{{ item.longitude }}</text>
                        </view>
                        
                        <view class="info-row">
                            <text class="label">纬度：</text>
                            <text class="value">{{ item.latitude }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { getTechnicianTrackPages } from "@/technician/api/technicianTrack"


const paging = ref<any>(null)
const dataList = ref<any[]>([])




// 获取轨迹记录
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getTechnicianTrackPages({
            page_current,
            page_size,
        })
        paging.value.complete(result.data.data)
    } catch (e) {
        console.error('获取轨迹记录失败', e)
        paging.value.complete(false)
    }
}


</script>

<style scoped lang="scss">
.track-list {
    padding: 20rpx;
}

.track-item {
    background: #fff;
    border-radius: 12rpx;
    padding: 24rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
    
    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20rpx;
        padding-bottom: 16rpx;
        border-bottom: 1rpx solid #f5f5f5;
        
        .type-badge {
            font-size: 26rpx;
            padding: 4rpx 12rpx;
            border-radius: 20rpx;
            background: #e8f5e9;
            color: #4CAF50;
        }
        
        .time {
            font-size: 26rpx;
            color: #999;
        }
    }
    
    .item-body {
        .info-row {
            display: flex;
            margin-bottom: 12rpx;
            font-size: 26rpx;
            
            .label {
                color: #999;
                margin-right: 10rpx;
                min-width: 80rpx;
            }
            
            .value {
                color: #333;
                flex: 1;
                word-break: break-all;
            }
        }
    }
}
</style>
