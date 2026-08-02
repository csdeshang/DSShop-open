<template>
    <!-- 商品关联的师傅 -->
    <view class="goods-technician">
        <view class="goods-technician-header">
            <view class="goods-technician-title">师傅</view>
            <view class="goods-technician-more" @click="handleViewMore">
                <text>更多</text>
                <uni-icons type="right" size="16" color="#999"></uni-icons>
            </view>
        </view>
        <view v-if="technicianList.length === 0" class="empty-technician">暂无相关师傅</view>
        <view v-else class="technician-list">
            <scroll-view scroll-x class="technician-scroll">
                <view class="technician-row">
                    <view v-for="item in technicianList" :key="item.id" class="technician-item" @click="goToPage({url:'/home/pages/technician/detail/index',params:{id:item.id}})">
                        <view class="technician-avatar">
                            <image :src="formatImageUrl(item.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
                        </view>
                        <view class="technician-info">
                            <view class="technician-name">
                                {{ item.name }}
                                <text class="technician-gender" :class="item.gender === 1 ? 'male' : (item.gender === 2 ? 'female' : '')">
                                    {{ item.gender === 1 ? '♂' : (item.gender === 2 ? '♀' : '') }}
                                </text>
                            </view>
                            <view class="technician-desc">
                                <text class="work-years">{{ item.work_years || 0 }}年经验</text>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { getTechnicianList } from '@/home/api/technician/technician'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    }
});

const technicianList = ref<any[]>([])

// 获取师傅列表
const fetchTechnicianList = async (goods_id: number) => {
  try {
    const res = await getTechnicianList({ goods_id: goods_id })
    technicianList.value = res.data
  } catch (error) {
    console.error(error)
  }
}

// 查看更多师傅
const handleViewMore = () => {
  // 跳转到师傅列表页面
  uni.navigateTo({
    url: `/home/pages/technician/list?goods_id=${props.goodsDetail.id || ''}`
  })
}

// 监听 goods_id 的变化并更新 searchParams
watch(
    () => props.goodsDetail.id,
    (newGoodsId) => {
        if (newGoodsId) {
            fetchTechnicianList(newGoodsId); // 重新获取数据
        }
    }
);
</script>

<style lang="scss" scoped>
.goods-technician {
    background: #ffffff;
    padding: 30rpx;
    border-radius: 12rpx;
    margin-bottom: 20rpx;
    
    .goods-technician-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20rpx;
    }
    
    .goods-technician-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        position: relative;
        padding-left: 20rpx;
        
        &:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6rpx;
            height: 30rpx;
            background: #33a9ff;
            border-radius: 6rpx;
        }
    }
    
    .goods-technician-more {
        font-size: 26rpx;
        color: #999;
        display: flex;
        align-items: center;
    }
    
    .empty-technician {
        text-align: center;
        padding: 40rpx 0;
        color: #999;
        font-size: 28rpx;
    }
    
    .technician-list {
        .technician-scroll {
            width: 100%;
            white-space: nowrap;
        }
        
        .technician-row {
            display: flex;
            flex-direction: row;
            padding: 10rpx 0;
        }
        
        .technician-item {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            margin-right: 30rpx;
            width: 150rpx;
            
            &:last-child {
                margin-right: 0;
            }
            
            .technician-avatar {
                width: 120rpx;
                height: 120rpx;
                border-radius: 50%;
                overflow: hidden;
                margin-bottom: 12rpx;
                
                image {
                    width: 100%;
                    height: 100%;
                }
            }
            
            .technician-info {
                width: 100%;
                text-align: center;
                
                .technician-name {
                    font-size: 28rpx;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 6rpx;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    
                    .technician-gender {
                        font-size: 22rpx;
                        margin-left: 6rpx;
                        
                        &.male {
                            color: #33a9ff;
                        }
                        
                        &.female {
                            color: #ff6699;
                        }
                    }
                }
                
                .technician-desc {
                    font-size: 22rpx;
                    color: #999;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    
                    .work-years {
                        color: #666;
                    }
                }
            }
        }
    }
}
</style>
