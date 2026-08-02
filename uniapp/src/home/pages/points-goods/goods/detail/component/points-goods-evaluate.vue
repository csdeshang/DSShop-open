<template>
    <view class="goods-comment" v-if="hasComments">
        <view class="comment-header">
            <text class="comment-title">商品评价 ({{ goodsDetail.evaluate_num || 0 }})</text>
            <view class="comment-score">
                <text class="score-value">{{ goodsDetail.avg_evaluate_score || '5.0' }}</text>
                <uni-rate :value="Number(goodsDetail.avg_evaluate_score) || 5" size="16" readonly />
            </view>
            <view @click="navigateToAllComments" class="view-all">
                查看全部 <uni-icons type="right" size="14" color="#999"></uni-icons>
            </view>
        </view>

        <!-- 评论列表 -->
        <view class="comment-list">
            <view v-for="(comment, index) in displayComments" :key="comment.id" class="comment-item">
                <view class="comment-user">
                    <image class="user-avatar" :src="formatImageUrl(comment.user.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
                    <text class="user-name">{{ comment.is_anonymous ? '匿名用户' : (comment.user?.nickname || comment.user?.username || `用户${comment.user_id}`) }}</text>
                    <view class="comment-date">{{ formatDate(comment.create_at) }}</view>
                </view>
                
                <view class="comment-content">
                    <text>{{ comment.evaluate_content }}</text>
                </view>
                
                <view class="comment-images" v-if="comment.evaluate_images">
                    <view 
                        v-for="(image, imageIndex) in parseCommentImages(comment.evaluate_images)" 
                        :key="imageIndex" 
                        class="image-item"
                        @click="previewImage(comment.evaluate_images, imageIndex)"
                    >
                        <image :src="formatImageUrl(image, ThumbnailPresets.small)" mode="aspectFill"></image>
                    </view>
                </view>
                
                <view class="comment-reply" v-if="comment.reply_content">
                    <text class="reply-label">商家回复：</text>
                    <text class="reply-content">{{ comment.reply_content }}</text>
                    <view class="reply-time" v-if="comment.reply_time">
                        {{ formatDate(comment.reply_time) }}
                    </view>
                </view>
                
                <view class="comment-rating">
                    <view class="rating-item">
                        <text class="rating-label">商品评分</text>
                        <uni-rate :value="comment.evaluate_score" size="12" readonly />
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import { getPointsGoodsEvaluateList } from '@/home/api/points-goods/pointsGoodsEvaluate';

const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    },
    platform: {
        type: String,
        default: ''
    }
});

// 评价数据
const evaluateList = ref<any[]>([]);
const loading = ref(false);

// 计算是否有评论
const hasComments = computed(() => {
    return props.goodsDetail.evaluate_num > 0;
});


// 获取显示的评论列表(最多显示2条)
const displayComments = computed(() => {
    return evaluateList.value.slice(0, 2);
});

// 获取评价数据
const loadEvaluateData = async () => {
    if (!props.goodsDetail.id) return;
    
    loading.value = true;
    try {
        const res = await getPointsGoodsEvaluateList({
            goods_id: props.goodsDetail.id,
            limit: 10,
            is_show: '1'
        });
        
        evaluateList.value = res.data || [];
    } catch (error) {
        console.error('获取评价数据失败:', error);
    } finally {
        loading.value = false;
    }
};

// 监听商品详情变化
watch(() => props.goodsDetail.id, (newId) => {
    if (newId) {
        loadEvaluateData();
    }
}, { immediate: true });

// 格式化日期
const formatDate = (dateString: string | number): string => {
    if (!dateString) return '';
    
    const date = new Date(typeof dateString === 'number' ? dateString * 1000 : dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};

// 解析评论图片列表
const parseCommentImages = (images: string): string[] => {
    if (!images) return [];
    return typeof images === 'string' ? images.split(',') : images;
};

// 预览图片
const previewImage = (images: string, currentIndex: number) => {
    const imageList = typeof images === 'string' ? images.split(',') : images;
    const formattedUrls = imageList.map(image => formatImageUrl(image, ThumbnailPresets.original));
    uni.previewImage({
        current: formattedUrls[currentIndex],
        urls: formattedUrls
    });
};

// 跳转到全部评论页面
const navigateToAllComments = () => {
    if (!props.goodsDetail.id) return;
    
    uni.navigateTo({
        url: `/home/pages/points-goods/evaluate/index?goods_id=${props.goodsDetail.id}`
    });
};
</script>

<style lang="scss" scoped>
.goods-comment {
    margin: 20rpx;
    padding: 30rpx;
    background-color: #fff;
    border-radius: 16rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}

.comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 24rpx;
    border-bottom: 1rpx solid #f5f5f5;
    
    .comment-title {
        font-size: 30rpx;
        font-weight: 500;
        color: #333;
    }
    
    .comment-score {
        display: flex;
        align-items: center;
        
        .score-value {
            font-size: 34rpx;
            font-weight: bold;
            color: #FF6034;
            margin-right: 10rpx;
        }
    }
    
    .view-all {
        display: flex;
        align-items: center;
        font-size: 26rpx;
        color: #999;
    }
}

.comment-list {
    .comment-item {
        padding: 30rpx 0;
        border-bottom: 1rpx solid #f5f5f5;
        
        &:last-child {
            border-bottom: none;
        }
        
        .comment-user {
            display: flex;
            align-items: center;
            margin-bottom: 16rpx;
            
            .user-avatar {
                width: 60rpx;
                height: 60rpx;
                border-radius: 50%;
                background-color: #f5f5f5;
                margin-right: 16rpx;
            }
            
            .user-name {
                font-size: 28rpx;
                color: #333;
                margin-right: auto;
            }
            
            .comment-date {
                font-size: 24rpx;
                color: #999;
            }
        }
        
        .comment-content {
            font-size: 28rpx;
            color: #333;
            line-height: 1.5;
            margin-bottom: 16rpx;
        }
        
        .comment-images {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 16rpx;
            
            .image-item {
                width: 160rpx;
                height: 160rpx;
                margin-right: 16rpx;
                margin-bottom: 16rpx;
                border-radius: 8rpx;
                overflow: hidden;
                
                image {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
            }
        }
        
        .comment-reply {
            background-color: #f8f8f8;
            padding: 16rpx;
            border-radius: 8rpx;
            margin-bottom: 16rpx;
            
            .reply-label {
                font-size: 26rpx;
                color: #666;
                font-weight: 500;
            }
            
            .reply-content {
                font-size: 26rpx;
                color: #666;
                margin-top: 8rpx;
            }
            
            .reply-time {
                font-size: 24rpx;
                color: #999;
                margin-top: 8rpx;
            }
        }
        
        .comment-rating {
            display: flex;
            flex-wrap: wrap;
            
            .rating-item {
                margin-right: 30rpx;
                margin-bottom: 10rpx;
                display: flex;
                align-items: center;
                
                .rating-label {
                    font-size: 24rpx;
                    color: #999;
                    margin-right: 8rpx;
                }
            }
        }
    }
}
</style>
