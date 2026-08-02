<template>
    <view class="goods-page">
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="goods-list">
                <view class="goods-item" v-for="(item, index) in dataList" :key="index">
                    <view class="goods-card" :class="[item.technician_is_rel === 1 ? 'goods-related' : '']">
                        <view class="goods-image">
                            <image :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                            <view class="status-tag" v-if="item.technician_is_rel === 1">已关联</view>
                        </view>
                        <view class="goods-info">
                            <view class="goods-name">{{item.goods_name}}</view>
                            <view class="goods-desc">{{item.goods_advword || '暂无描述'}}</view>
                            <view class="goods-stats">
                                <text class="sales">销量 {{item.sales_num}}</text>
                                <text class="views">浏览 {{item.click_num}}</text>
                            </view>
                        </view>
                        <view class="goods-action">
                            <button class="action-btn" 
                                :class="[item.technician_is_rel === 1 ? 'related' : '']"
                                @click="handleRelationChange(item)">
                                {{item.technician_is_rel === 1 ? '取消关联' : '关联服务'}}
                            </button>
                        </view>
                    </view>
                </view>
                
            </view>
        </z-paging>
    </view>
</template>


<script setup lang="ts">
import { ref } from 'vue'
import { getTechnicianGoodsRelPages,updateTechnicianGoodsRel } from '@/technician/api/technicianGoods'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const paging = ref<any>(null)
const dataList = ref<any[]>([])

// 获取余额记录
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getTechnicianGoodsRelPages({
            page_current,
            page_size,
        })
        paging.value.complete(result.data.data)
    } catch (e) {
        console.error('获取余额记录失败', e)
        paging.value.complete(false)
    }
}

// 处理关联/取消关联商品
const handleRelationChange = async (item: any) => {
    try {
        uni.showLoading({
            title: '处理中...',
            mask: true
        });
        
        // 调用关联/取消关联接口，只需传递goods_id参数
        const result = await updateTechnicianGoodsRel(item.id);
        
        uni.hideLoading();
        
        if (result.code === 10000) {
            uni.showToast({
                title: item.technician_is_rel === 1 ? '已取消关联' : '已关联服务',
                icon: 'success'
            });
            
            // 刷新列表
            paging.value.reload();
        } else {
            uni.showToast({
                title: result.message || '操作失败',
                icon: 'none'
            });
        }
    } catch (error) {
        uni.hideLoading();
        console.error('关联/取消关联失败:', error);
        uni.showToast({
            title: '网络异常，请重试',
            icon: 'none'
        });
    }
}
</script>

<style lang="scss" scoped>
.goods-page {
    min-height: 100vh;
    background-color: #f5f5f5;
    

    
    .goods-list {
        padding: 20rpx;
    }
    
    .goods-item {
        margin-bottom: 20rpx;
    }
    
    .goods-card {
        display: flex;
        background-color: #fff;
        border-radius: 12rpx;
        overflow: hidden;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);
        
        &.goods-related {
            border-left: 6rpx solid #33a9ff;
        }
    }
    
    .goods-image {
        width: 200rpx;
        height: 200rpx;
        position: relative;
        
        image {
            width: 100%;
            height: 100%;
        }
        
        .status-tag {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #33a9ff;
            color: #fff;
            font-size: 20rpx;
            padding: 6rpx 10rpx;
            border-bottom-left-radius: 10rpx;
        }
    }
    
    .goods-info {
        flex: 1;
        padding: 20rpx;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        
        .goods-name {
            font-size: 28rpx;
            font-weight: bold;
            color: #333;
            margin-bottom: 10rpx;
        }
        
        .goods-desc {
            font-size: 24rpx;
            color: #666;
            margin-bottom: 10rpx;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .goods-stats {
            font-size: 22rpx;
            color: #999;
            
            .sales {
                margin-right: 20rpx;
            }
        }
    }
    
    .goods-action {
        padding: 20rpx;
        display: flex;
        align-items: flex-end;
        
        .action-btn {
            font-size: 24rpx;
            padding: 12rpx 20rpx;
            border-radius: 30rpx;
            line-height: 1;
            border: none;
            background-color: #33a9ff;
            color: #fff;
            transition: all 0.3s;
            
            &.related {
                background-color: #ff6b6b;
            }
            
            &:active {
                opacity: 0.8;
                transform: scale(0.98);
            }
        }
    }
    
    
}
</style>

