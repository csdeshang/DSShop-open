<template> 
    <view class="recommend-container">
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="content-list">
                <view 
                    v-for="item in dataList" 
                    :key="item.id" 
                    class="content-card"
                    @click="handleCardClick(item)"
                >
                    <!-- 封面图片 -->
                    <view class="cover-wrapper">
                        <image 
                            :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium)" 
                            class="cover-image"
                            mode="aspectFill"
                        />
                        <!-- 类型标识 -->
                        <view v-if="item.type === 1" class="type-tag video-tag">
                            <uni-icons type="videocam-filled" size="14" color="#fff"></uni-icons>
                        </view>
                        <view v-else class="type-tag image-tag">
                            <uni-icons type="image-filled" size="14" color="#fff"></uni-icons>
                        </view>
                    </view>
                    
                    <!-- 内容信息 -->
                    <view class="content-info">
                        <text class="content-title">{{ item.title }}</text>
                        <view class="blogger-info">
                            <image 
                                :src="formatImageUrl(item.blogger.avatar, ThumbnailPresets.small, 'avatar')" 
                                class="blogger-avatar"
                                mode="aspectFill"
                            />
                            <text class="blogger-name">{{ item.blogger.blogger_name }}</text>
                        </view>
                        <view class="interaction-bar">
                            <view class="interaction-item">
                                <uni-icons type="heart-filled" size="18" color="#999"></uni-icons>
                                <text class="count">{{ formatCount(item.like_count) }}</text>
                            </view>
                            <view class="interaction-item">
                                <uni-icons type="chat-filled" size="18" color="#999"></uni-icons>
                                <text class="count">{{ formatCount(item.comment_count) }}</text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { getVideoShortPages } from '@/video/api/home/videoShort'
import { ref } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

const paging = ref<any>(null)
const dataList = ref<any[]>([])

// 获取列表数据
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getVideoShortPages({
            page_current,
            page_size,
        })
        paging.value.complete(result.data.data)
    } catch (e) {
        console.error('获取数据失败', e)
        paging.value.complete(false)
    }
}

// 格式化数字显示
const formatCount = (count: number) => {
    if (count >= 10000) {
        return `${(count / 10000).toFixed(1)}w`
    } else if (count >= 1000) {
        return `${(count / 1000).toFixed(1)}k`
    }
    return count.toString()
}

// 点击卡片
const handleCardClick = (item: any) => {
    // 根据类型跳转到不同页面
    if (item.type === 1) {
        // 视频详情页  specified_id 指定为当前视频id
        uni.navigateTo({
            url: `/video/pages/short/detail/video?specified_id=${item.id}`
        })
    } else {
        // 图文详情页
        uni.navigateTo({
            url: `/video/pages/short/detail/image?id=${item.id}`
        })
    }
}

interface Props {
    isActive: boolean
}

const props = defineProps<Props>()

// 可以根据isActive状态来控制数据加载
// watch(() => props.isActive, (active) => {
//     if (active) {
//         // 激活时加载数据
//         console.log('推荐tab激活，开始加载数据')
//     } else {
//         // 非激活时可以暂停视频播放等
//         console.log('推荐tab未激活，暂停相关操作')
//     }
// })
</script>

<style lang="scss" scoped>
.recommend-container {
    height: 100%;
    background: #f8f8f8;
}

.content-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 16rpx;
    gap: 16rpx;
}

.content-card {
    width: calc(50% - 8rpx);
    background: #fff;
    border-radius: 20rpx;
    overflow: hidden;
    margin-bottom: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
    
    &:active {
        transform: scale(0.98);
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.12);
    }
}

.cover-wrapper {
    position: relative;
    width: 100%;
    
    .cover-image {
        width: 100%;
        height: 300rpx;
        background: #f0f0f0;
        border-radius: 16rpx 16rpx 0 0;
    }
    
    .type-tag {
        position: absolute;
        top: 12rpx;
        right: 12rpx;
        border-radius: 12rpx;
        padding: 6rpx 10rpx;
        backdrop-filter: blur(8rpx);
        display: flex;
        align-items: center;
        justify-content: center;
        
        &.video-tag {
            background: rgba(0, 0, 0, 0.4);
        }
        
        &.image-tag {
            background: rgba(0, 0, 0, 0.4);
        }
    }
}

.content-info {
    padding: 20rpx;
}

.content-title {
    font-size: 28rpx;
    color: #333;
    line-height: 1.4;
    margin-bottom: 16rpx;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    word-break: break-all;
    font-weight: 500;
}

.blogger-info {
    display: flex;
    align-items: center;
    margin-bottom: 16rpx;
    
    .blogger-avatar {
        width: 36rpx;
        height: 36rpx;
        border-radius: 50%;
        margin-right: 12rpx;
        background: #f0f0f0;
        border: 2rpx solid #fff;
        box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.1);
    }
    
    .blogger-name {
        font-size: 24rpx;
        color: #666;
        flex: 1;
        font-weight: 400;
    }
}

.interaction-bar {
    display: flex;
    align-items: center;
    gap: 32rpx;
    
    .interaction-item {
        display: flex;
        align-items: center;
        gap: 8rpx;
        
        .count {
            font-size: 22rpx;
            color: #999;
            font-weight: 500;
        }
    }
}

// 响应式适配
@media screen and (max-width: 750rpx) {
    .content-card {
        width: calc(50% - 6rpx);
        
        .cover-image {
            height: 260rpx;
        }
    }
}

// 大屏适配
@media screen and (min-width: 1200rpx) {
    .content-list {
        justify-content: flex-start;
        gap: 20rpx;
    }
    
    .content-card {
        width: calc(33.333% - 14rpx);
    }
}
</style>