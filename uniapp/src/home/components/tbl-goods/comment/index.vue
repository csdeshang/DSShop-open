<template>
    <view class="comment-page">
        <!-- 评论列表 -->
    <z-paging ref="paging" v-model="dataList" @query="queryList">
            <!-- 评分概览区域 - 移动到z-paging内部 -->
            <view class="rating-overview">
                <view class="overview-header">
                    <text class="overview-title">商品评价 ({{ totalCount }})</text>
                </view>
                
                <!-- 评价筛选标签 -->
                <view class="filter-tags">
                    <view 
                        v-for="(tag, i) in filterTags" 
                        :key="i"
                        class="tag" 
                        :class="{ active: activeTagIndex === i }"
                        @click="changeFilter(i)"
                    >
                        {{ tag.name }}
                    </view>
                </view>
            </view>
            
            <view class="comment-list" v-if="dataList.length > 0">
                <view v-for="comment in dataList" :key="comment.id" class="comment-item">
                    <!-- 用户信息 -->
                    <view class="comment-user">
                        <image class="user-avatar" :src="formatImageUrl(comment.user.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
                        <text class="user-name">{{ comment.is_anonymous ? '匿名用户' : (comment.user?.nickname || comment.user?.username || `用户${comment.user_id}`) }}</text>
                        <view class="comment-date">{{ formatDate(comment.create_at) }}</view>
                    </view>
                    
                    <!-- 商品规格 -->
                    <view class="comment-sku" v-if="comment.sku_name">
                        <text>{{ comment.sku_name }}</text>
                    </view>
                    
                    <!-- 评论内容 -->
                    <view class="comment-content">
                        <text>{{ comment.content }}</text>
                    </view>
                    
                    <!-- 评论图片 -->
                    <view class="comment-images" v-if="comment.images">
                        <view 
                            v-for="(image, imageIndex) in parseImages(comment.images)" 
                            :key="imageIndex" 
                            class="image-item"
                            @click="previewImage(comment.images, imageIndex)"
                        >
                            <image :src="formatImageUrl(image, ThumbnailPresets.small)" mode="aspectFill"></image>
                        </view>
                    </view>
                    
                    <!-- 评分信息 -->
                    <view class="comment-rating">
                        <view class="rating-item">
                            <text class="rating-label">商品评分</text>
                            <uni-rate :value="comment.goods_score" size="14" readonly />
                        </view>
                        <view class="rating-item" v-if="comment.describe_score">
                            <text class="rating-label">描述相符</text>
                            <uni-rate :value="comment.describe_score" size="14" readonly />
                        </view>
                        <view class="rating-item" v-if="comment.logistics_score">
                            <text class="rating-label">物流服务</text>
                            <uni-rate :value="comment.logistics_score" size="14" readonly />
                        </view>
                    </view>
                    
                    <!-- 商家回复 -->
                    <view class="comment-reply" v-if="comment.is_reply && comment.reply_content">
                        <text class="reply-label">商家回复：</text>
                        <text class="reply-content">{{ comment.reply_content }}</text>
                    </view>
                </view>
            </view>
    </z-paging>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getTblGoodsCommentPages } from '@/home/api/tbl-goods/tblGoodsComment';
import { onLoad } from '@dcloudio/uni-app';

// 从页面参数中获取商品ID
const goodsId = ref<string | number>('');

// 查询参数 - 移到onLoad之前
const params = ref({
    goods_id: '',
    goods_score: '',
    filter_type: 'all' // 全部、有图、好评、中评、差评
});

// 分页相关
const paging = ref<any>(null);
const dataList = ref<any[]>([]);

// 评价统计
const totalCount = ref(0);

// 筛选标签
const filterTags = ref([
    { name: '全部', type: 'all' },
    { name: '有图', type: 'image' },
    { name: '好评', type: 'good' },
    { name: '中评', type: 'medium' },
    { name: '差评', type: 'bad' }
]);
const activeTagIndex = ref(0);

// 在页面加载时从链接获取商品ID - 修复options可能为undefined的问题
onLoad((options?: any) => {
    if (options && options.goods_id) {
        goodsId.value = options.goods_id;
        params.value.goods_id = options.goods_id;
        
        // 如果分页组件已经初始化，则重新加载数据
        if (paging.value) {
            paging.value.reload();
        }
    } else {
        uni.showToast({
            title: '加载失败，请返回重试',
            icon: 'none'
        });
    }
});

// 更改筛选条件
const changeFilter = (index: number) => {
    if (activeTagIndex.value === index) return;
    
    activeTagIndex.value = index;
    params.value.filter_type = filterTags.value[index].type;
    
    // 重新加载数据
    if (paging.value) {
        paging.value.reload();
    }
};

// 查询评论列表
const queryList = async (pageCurrent: number, pageSize: number) => {
    // 检查是否有商品ID
    if (!params.value.goods_id) {
        if (paging.value) {
            paging.value.complete([]);
        }
        return;
    }
    
    try {
        const result = await getTblGoodsCommentPages({
            page_current: pageCurrent,
            page_size: pageSize,
            ...params.value
        });
        
        // 首次加载时更新总数
        if (pageCurrent === 1 && result.data && result.data.total) {
            totalCount.value = result.data.total;
        }
        
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('加载评论失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
    }
};

// 格式化日期
const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};

// 解析图片列表
const parseImages = (images: string): string[] => {
    if (!images) return [];
    return typeof images === 'string' ? images.split(',') : Array.isArray(images) ? images : [];
};

// 预览图片
const previewImage = (images: string | string[], currentIndex: number) => {
    const imageList = typeof images === 'string' ? images.split(',') : images;
    const formattedUrls = imageList.map(image => formatImageUrl(image, ThumbnailPresets.original));
    
    uni.previewImage({
        current: currentIndex,
        urls: formattedUrls
    });
};
</script>

<style lang="scss" scoped>
.comment-page {
    min-height: 100vh;
    background-color: #f7f8fa;
}

.rating-overview {
    background-color: #fff;
    padding: 30rpx;
    margin-bottom: 20rpx;
    
    .overview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30rpx;
        
        .overview-title {
            font-size: 32rpx;
            font-weight: 500;
            color: #333;
        }
    }
    
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 10rpx;
        
        .tag {
            padding: 10rpx 24rpx;
            margin-right: 20rpx;
            margin-bottom: 20rpx;
            background-color: #f5f5f5;
            border-radius: 30rpx;
            font-size: 26rpx;
            color: #666;
            
            &.active {
                background-color: #fff0ed;
                color: #FF6034;
                font-weight: 500;
                border: 1rpx solid #FF6034;
            }
        }
    }
}

.comment-list {
    padding: 0 30rpx;
    background-color: #fff;
    
    .comment-item {
        padding: 30rpx 0;
        border-bottom: 1rpx solid #f5f5f5;
        
        &:last-child {
            border-bottom: none;
        }
        
        .comment-user {
            display: flex;
            align-items: center;
            margin-bottom: 20rpx;
            
            .user-avatar {
                width: 70rpx;
                height: 70rpx;
                border-radius: 50%;
                background-color: #f5f5f5;
                margin-right: 16rpx;
            }
            
            .user-name {
                flex: 1;
                font-size: 28rpx;
                color: #333;
                font-weight: 500;
            }
            
            .comment-date {
                font-size: 24rpx;
                color: #999;
            }
        }
        
        .comment-sku {
            display: inline-block;
            font-size: 24rpx;
            color: #666;
            background-color: #f5f5f5;
            padding: 6rpx 16rpx;
            border-radius: 6rpx;
            margin-bottom: 16rpx;
        }
        
        .comment-content {
            font-size: 28rpx;
            color: #333;
            line-height: 1.6;
            margin-bottom: 20rpx;
        }
        
        .comment-images {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20rpx;
            
            .image-item {
                width: 200rpx;
                height: 200rpx;
                margin-right: 16rpx;
                margin-bottom: 16rpx;
                border-radius: 8rpx;
                overflow: hidden;
                
                &:nth-child(3n) {
                    margin-right: 0;
                }
                
                image {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
            }
        }
        
        .comment-rating {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20rpx;
            
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
        
        .comment-reply {
            background-color: #f8f8f8;
            padding: 20rpx;
            border-radius: 8rpx;
            
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
    }
}


</style>
