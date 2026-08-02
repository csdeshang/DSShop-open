<template>
    <view class="goods-comment" v-if="hasComments">
        <view class="comment-header">
            <text class="comment-title">商品评价 ({{ goodsDetail.evaluate_num }})</text>
            <view class="comment-score">
                <text class="score-value">{{ avgScore }}</text>
                <uni-rate :value="Number(avgGoodsScore)" size="16" readonly />
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
                    <text class="user-name">{{ comment.is_anonymous ? '匿名用户' : (comment.user?.nickname || comment.user?.username || `用户${comment.user_id}`)}}</text>
                    <view class="comment-date">{{ formatDate(comment.create_at) }}</view>
                </view>
                
                <view class="comment-content">
                    <text>{{ comment.content }}</text>
                </view>
                
                <view class="comment-images" v-if="comment.images && comment.images.length > 0">
                    <view 
                        v-for="(image, imageIndex) in parseCommentImages(comment.images)" 
                        :key="imageIndex" 
                        class="image-item"
                        @click="previewImage(comment.images, imageIndex)"
                    >
                        <image :src="formatImageUrl(image, ThumbnailPresets.small)" mode="aspectFill"></image>
                    </view>
                </view>
                
                <view class="comment-reply" v-if="comment.is_reply && comment.reply_content">
                    <text class="reply-label">商家回复：</text>
                    <text class="reply-content">{{ comment.reply_content }}</text>
                </view>
                
                <view class="comment-rating">
                    <view class="rating-item">
                        <text class="rating-label">商品评分</text>
                        <uni-rate :value="comment.goods_score" size="12" readonly />
                    </view>
                    <view class="rating-item">
                        <text class="rating-label">描述相符</text>
                        <uni-rate :value="comment.describe_score" size="12" readonly />
                    </view>
                    <view class="rating-item">
                        <text class="rating-label">物流服务</text>
                        <uni-rate :value="comment.logistics_score" size="12" readonly />
                    </view>
                </view>
            </view>
        </view>
    </view>
    


</template>

<script lang="ts" setup>
import { computed, ref } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image';


const props = defineProps({
    goodsDetail: {
        type: Object,
        default: () => ({})
    }
});

// 计算是否有评论
const hasComments = computed(() => {
    return props.goodsDetail.goodsCommentList && props.goodsDetail.goodsCommentList.length > 0;
});


// 计算平均评分
const avgGoodsScore = computed(() => {
    return props.goodsDetail.avg_goods_score || 5;
});

// 格式化平均评分显示
const avgScore = computed(() => {
    const score = Number(avgGoodsScore.value);
    return score.toFixed(1);
});

// 获取显示的评论列表(最多显示2条)
const displayComments = computed(() => {
    if (!props.goodsDetail.goodsCommentList) return [];
    return props.goodsDetail.goodsCommentList.slice(0, 2);
});

// 格式化日期
const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
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
        current: currentIndex,
        urls: formattedUrls
    });
};

// 跳转到全部评论页面
const navigateToAllComments = () => {
    if (!props.goodsDetail.id) return;
    
    const platform = props.goodsDetail.platform || 'mall';
    uni.navigateTo({
        url: `/home/platform/${platform}/pages/goods/comment/index?goods_id=${props.goodsDetail.id}`
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
