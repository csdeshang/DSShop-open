<template>
    <view class="image-detail-page">
        <!-- 图片轮播区域 -->
        <view class="image-section">
            <swiper class="image-swiper" :indicator-dots="true" :autoplay="false" :circular="false"
                indicator-color="rgba(255,255,255,0.5)" indicator-active-color="#fff" @change="onSwiperChange">
                <swiper-item v-for="(image, index) in detail.images_url" :key="index">
                    <image :src="formatImageUrl(image, ThumbnailPresets.large)" class="detail-image" mode="aspectFill"
                        @tap="previewImage(index)" />
                    <!-- 图片序号 -->
                    <view class="image-index">{{ currentImageIndex + 1 }}/{{ detail.images_url?.length || 0 }}</view>
                </swiper-item>
            </swiper>

            <!-- 返回按钮 -->
            <view class="nav-back" @tap="goBack">
                <uni-icons type="back" size="20" color="#fff"></uni-icons>
            </view>

            <!-- 分享按钮 -->
            <view class="nav-share" @tap="handleTopShare">
                <uni-icons type="redo" size="18" color="#fff"></uni-icons>
            </view>
        </view>

        <!-- 内容区域 -->
        <view class="content-section">
            <!-- 用户信息 -->
            <view class="user-info">
                <view class="user-avatar-wrap" @tap="goToPage({ url : `/video/pages/space/index?id=${detail.blogger?.id}` })">
                    <image :src="formatImageUrl(detail.blogger.avatar, ThumbnailPresets.small, 'avatar')" class="user-avatar" mode="aspectFill" />
                </view>
                <view class="user-details" @tap="goToPage({ url : `/video/pages/space/index?id=${detail.blogger?.id}` })">
                    <view class="user-name">{{ detail.blogger?.blogger_name || '用户' }}</view>
                    <view class="publish-time">{{ formatTime(detail.create_at) }}</view>
                </view>
                <BloggerFollow :bloggerId="detail.blogger?.id" :isFollowed="detail.blogger?.is_followed || false"
                    theme="light" size="normal" />
            </view>

            <!-- 标题和内容 -->
            <view class="content-info">
                <view class="content-title">{{ detail.title }}</view>
                <view class="content-desc">{{ detail.description }}</view>

                <!-- 话题标签 -->
                <view class="tags" v-if="detail.tags && detail.tags.length > 0">
                    <text v-for="tag in detail.tags" :key="tag" class="tag">#{{ tag }}</text>
                </view>
            </view>


            <!-- 互动按钮 -->
            <view class="action-section">
                <view class="action-item">
                    <VideoLike :contentId="detail.id" :contentType="'short'" :likeCount="detail.like_count || 0"
                        :isLiked="detail.is_liked" theme="light" :size="22" />
                </view>

                <view class="action-item">
                    <VideoCollect :contentId="detail.id" :contentType="'short'"
                        :collectCount="detail.collect_count || 0" :isCollected="detail.is_collected" theme="light"
                        :size="22" />
                </view>

                <view class="action-item" @tap="showAllComments">
                    <uni-icons type="chat" color="#666" size="22"></uni-icons>
                    <text class="action-text">{{ detail.comment_count || 0 }}</text>
                </view>

                <view class="action-item">
                    <VideoShare :contentId="detail.id" :contentType="'short'" :shareTitle="detail.title || '精彩内容分享'"
                        :shareDesc="detail.description || ''"
                        :shareImage="formatImageUrl(detail.cover_image || detail.images_url?.[0] || '', ThumbnailPresets.large)" theme="light"
                        :size="22" />
                </view>
            </view>

            <!-- 关联商品 -->
            <view class="goods-section" v-if="detail.goods_list && detail.goods_list.length > 0">
                <view class="section-title">
                    <uni-icons type="shop" size="16" color="#ff2442"></uni-icons>
                    <text>相关商品</text>
                </view>
                <view class="goods-list">
                    <view v-for="goods in detail.goods_list" :key="goods.id" class="goods-item"
                        @tap="goGoodsDetailPage(goods)">
                        <image :src="formatImageUrl(goods.cover_image, ThumbnailPresets.medium, 'goods')" class="goods-image" mode="aspectFill" />
                        <view class="goods-info">
                            <text class="goods-name">{{ goods.goods_name }}</text>
                            <text class="goods-price">¥{{ goods.goodsSku?.sku_price }}</text>
                        </view>
                    </view>
                </view>
            </view>


        </view>

        <!-- 相关推荐 -->
        <view class="recommend-section" v-if="recommendList.length > 0">
            <view class="section-title">
                <text>相关推荐</text>
            </view>
            <view class="recommend-grid">
                <view v-for="item in recommendList" :key="item.id" class="recommend-item" @tap="goToDetail(item)">
                    <image :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium)" class="recommend-image" mode="aspectFill" />
                    <view class="recommend-info">
                        <text class="recommend-title">{{ item.title }}</text>
                        <view class="recommend-stats">
                            <uni-icons type="heart" size="12" color="#999"></uni-icons>
                            <text class="stat-text">{{ item.like_count || 0 }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </view>

        <!-- 评论弹窗 -->
        <uni-popup ref="commentPopup" type="bottom">
            <CommentList :contentType="'short'" :contentId="currentVideoId" :commentCount="detail.comment_count || 0"
                @close="closeComments" @commentCountChanged="updateCommentCount" :videoBloggerId="detail.blogger_id" />
        </uni-popup>

        <!-- 底部导航 -->
        <VideoBottomNav 
            current="discover"
            theme="light"
        />
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, onUnmounted } from 'vue'
import { getVideoShortDetail } from '@/video/api/home/videoShort'
import { createVideoViewLog } from '@/video/api/user/videoViewLog'
import { onLoad, onUnload } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { goGoodsDetailPage} from '@/utils/platform'
 
// @ts-ignore
import CommentList from '@/video/components/comment/list.vue'
// @ts-ignore
import VideoLike from '@/video/components/interaction/video-like.vue'
// @ts-ignore
import VideoCollect from '@/video/components/interaction/video-collect.vue'
// @ts-ignore
import VideoShare from '@/video/components/interaction/video-share.vue'
// @ts-ignore
import BloggerFollow from '@/video/components/interaction/blogger-follow.vue'
// @ts-ignore
import VideoBottomNav from '@/video/components/video-bottom-nav/index.vue'


// 页面数据
const detail = ref<any>({})
const recommendList = ref<any[]>([])
const commentPopup = ref<any>(null)

// 评论相关数据
const currentVideoId = ref<any>(null)

// 交互状态
const currentImageIndex = ref(0)

// ✨ 浏览记录相关状态
const viewLogCache = ref(new Set()) // 缓存已记录的视频ID，避免重复请求
const viewLogDebounceMap = ref(new Map()) // 防抖处理

// ✨ 记录视频浏览
const recordVideoView = async (videoData: any) => {
    try {
        // 检查是否已记录过
        if (viewLogCache.value.has(videoData.id)) {
            return
        }

        // 防抖处理 - 如果500ms内重复调用同一视频，则取消之前的请求
        const videoId = videoData.id
        if (viewLogDebounceMap.value.has(videoId)) {
            clearTimeout(viewLogDebounceMap.value.get(videoId))
        }

        const timeoutId = setTimeout(async () => {
            try {
                await createVideoViewLog({
                    content_type: 'short',
                    content_id: videoData.id
                })
                
                // 记录到缓存，避免重复请求
                viewLogCache.value.add(videoData.id)
                
                console.log(`✅ 已记录图片内容 ${videoData.id} 的浏览记录`)
            } catch (error) {
                console.error('记录浏览记录失败:', error)
                // 记录失败不影响用户体验，静默处理
            } finally {
                viewLogDebounceMap.value.delete(videoId)
            }
        }, 500) // 500ms 防抖

        viewLogDebounceMap.value.set(videoId, timeoutId)
    } catch (error) {
        console.error('准备记录浏览记录时出错:', error)
    }
}

// 获取详情数据
const fetchVideoShortDetail = async (id: string) => {
    try {
        const res = await getVideoShortDetail(id)
        detail.value = res.data

        console.log('详情数据:', detail.value)

        // 关键：获取详情后立即记录浏览
        if (detail.value) {
            recordVideoView(detail.value)
        }

        // 可以根据分类或标签获取相关推荐
        fetchRecommendList(detail.value.cid)

    } catch (error) {
        console.error('获取详情失败', error)
        uni.showToast({
            title: '获取详情失败',
            icon: 'error'
        })
    }
}

// 轮播图切换
const onSwiperChange = (e: any) => {
    currentImageIndex.value = e.detail.current
}

// 预览图片
const previewImage = (index: number) => {
    const urls = detail.value.images_url?.map((url: string) => formatImageUrl(url, ThumbnailPresets.original)) || []
    uni.previewImage({
        urls,
        current: index
    })
}

// 返回上一页
const goBack = () => {
    uni.navigateBack()
}

// 顶部分享按钮
const handleTopShare = () => {
    // 显示分享菜单
    uni.showActionSheet({
        itemList: ['分享到微信好友', '分享到朋友圈', '复制链接'],
        success: (res) => {
            switch (res.tapIndex) {
                case 0:
                case 1:
                    uni.showToast({
                        title: '请使用底部分享功能',
                        icon: 'none'
                    })
                    break
                case 2:
                    // 复制链接
                    const shareUrl = `https://your-domain.com/video/pages/short/detail/image?id=${detail.value.id}`
                    uni.setClipboardData({
                        data: shareUrl,
                        success: () => {
                            uni.showToast({
                                title: '链接已复制',
                                icon: 'success'
                            })
                        }
                    })
                    break
            }
        }
    })
}

// 显示全部评论（弹窗）
const showAllComments = () => {
    // 设置当前评论的内容信息
    currentVideoId.value = detail.value.id

    // 打开评论弹窗
    commentPopup.value?.open()
}

// 关闭评论弹窗
const closeComments = () => {
    commentPopup.value?.close()
    currentVideoId.value = null
}

// 评论数量变化的回调
const updateCommentCount = (newCount: number) => {
    if (detail.value) {
        detail.value.comment_count = newCount
    }
}

// 格式化时间
const formatTime = (time: string) => {
    if (!time) return ''
    const date = new Date(time)
    const now = new Date()
    const diff = now.getTime() - date.getTime()

    const minutes = Math.floor(diff / (1000 * 60))
    const hours = Math.floor(diff / (1000 * 60 * 60))
    const days = Math.floor(diff / (1000 * 60 * 60 * 24))

    if (minutes < 1) return '刚刚'
    if (minutes < 60) return `${minutes}分钟前`
    if (hours < 24) return `${hours}小时前`
    if (days < 30) return `${days}天前`

    return date.toLocaleDateString()
}




// 跳转到其他详情
const goToDetail = (item: any) => {
    uni.navigateTo({
        url: `/video/pages/short/detail/image?id=${item.id}`
    })
}

// 获取推荐内容
const fetchRecommendList = async (cid?: number) => {
    try {
        // 这里应该调用推荐API，根据分类或其他条件获取推荐内容
        // const res = await getRecommendVideoShorts({ cid, limit: 6 })
        // recommendList.value = res.data.data || []

        // 暂时使用空数组，实际开发时替换为真实API调用
        recommendList.value = []
    } catch (error) {
        console.error('获取推荐失败', error)
    }
}

// ✨ 清理浏览记录相关资源
const cleanupViewLog = () => {
    // 清理所有防抖定时器
    viewLogDebounceMap.value.forEach(timeoutId => {
        clearTimeout(timeoutId)
    })
    viewLogDebounceMap.value.clear()
}

onLoad((options) => {
    const id = options?.id
    if (id) {
        fetchVideoShortDetail(id)
    }
})

// ✨ 页面卸载时清理资源
onUnload(() => {
    cleanupViewLog()
})
</script>

<style lang="scss" scoped>
.image-detail-page {
    background: #fff;
    min-height: 100vh;
    padding-bottom: calc(100rpx + constant(safe-area-inset-bottom)); /* 为底部导航预留空间 */
    padding-bottom: calc(100rpx + env(safe-area-inset-bottom));
}

.image-section {
    position: relative;
    height: 60vh;
    background: #000;

    .image-swiper {
        width: 100%;
        height: 100%;

        .detail-image {
            width: 100%;
            height: 100%;
        }
    }

    .image-index {
        position: absolute;
        top: 40rpx;
        right: 32rpx;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        padding: 8rpx 16rpx;
        border-radius: 20rpx;
        font-size: 24rpx;
    }

    .nav-back {
        position: absolute;
        top: 40rpx;
        left: 32rpx;
        width: 64rpx;
        height: 64rpx;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-share {
        position: absolute;
        top: 120rpx;
        right: 32rpx;
        width: 56rpx;
        height: 56rpx;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}

.content-section {
    padding: 32rpx;
}

.user-info {
    display: flex;
    align-items: center;
    margin-bottom: 24rpx;

    .user-avatar-wrap {
        margin-right: 24rpx;

        .user-avatar {
            width: 80rpx;
            height: 80rpx;
            border-radius: 50%;
        }
    }

    .user-details {
        flex: 1;

        .user-name {
            font-size: 30rpx;
            font-weight: 600;
            color: #333;
            margin-bottom: 8rpx;
        }

        .publish-time {
            font-size: 24rpx;
            color: #999;
        }
    }
}

.content-info {
    margin-bottom: 32rpx;

    .content-title {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
        line-height: 1.5;
        margin-bottom: 16rpx;
    }

    .content-desc {
        font-size: 28rpx;
        color: #666;
        line-height: 1.6;
        margin-bottom: 16rpx;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 16rpx;

        .tag {
            background: #f0f7ff;
            color: #1890ff;
            padding: 8rpx 16rpx;
            border-radius: 20rpx;
            font-size: 24rpx;
        }
    }
}

.goods-section {
    margin-bottom: 32rpx;

    .section-title {
        display: flex;
        align-items: center;
        margin-bottom: 20rpx;
        font-size: 28rpx;
        font-weight: 600;
        color: #333;

        text {
            margin-left: 8rpx;
        }
    }

    .goods-list {
        display: flex;
        flex-direction: column;
        gap: 20rpx;
    }

    .goods-item {
        display: flex;
        align-items: center;
        background: #f8f8f8;
        border-radius: 12rpx;
        overflow: hidden;
        padding: 20rpx;

        .goods-image {
            width: 120rpx;
            height: 120rpx;
            border-radius: 8rpx;
            margin-right: 20rpx;
            flex-shrink: 0;
        }

        .goods-info {
            flex: 1;

            .goods-name {
                font-size: 28rpx;
                color: #333;
                margin-bottom: 12rpx;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                line-height: 1.4;
            }

            .goods-price {
                font-size: 30rpx;
                color: #ff2442;
                font-weight: 600;
            }
        }
    }
}

.action-section {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 32rpx 0;
    border-top: 1rpx solid #f0f0f0;
    border-bottom: 1rpx solid #f0f0f0;
    margin-bottom: 32rpx;

    .action-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8rpx;

        .action-text {
            font-size: 24rpx;
            color: #666;

            &.active {
                color: #ff2442;
            }
        }
    }
}

.recommend-section {
    padding: 32rpx;
    padding-bottom: calc(32rpx + 100rpx); /* 额外增加底部导航高度 */
    background: #f8f8f8;

    .section-title {
        font-size: 30rpx;
        font-weight: 600;
        color: #333;
        margin-bottom: 24rpx;
    }

    .recommend-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20rpx;
    }

    .recommend-item {
        background: #fff;
        border-radius: 12rpx;
        overflow: hidden;

        .recommend-image {
            width: 100%;
            height: 300rpx;
        }

        .recommend-info {
            padding: 16rpx;

            .recommend-title {
                font-size: 26rpx;
                color: #333;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                margin-bottom: 8rpx;
            }

            .recommend-stats {
                display: flex;
                align-items: center;
                gap: 8rpx;

                .stat-text {
                    font-size: 22rpx;
                    color: #999;
                }
            }
        }
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
