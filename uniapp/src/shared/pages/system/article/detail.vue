<template>
    <view class="article-detail">
        <view v-if="articleInfo" class="article-card">
            <view class="article-title">{{ articleInfo.title }}</view>
            <view class="article-meta">
                <text class="article-time">{{ articleInfo.publish_time }}</text>
                <text class="article-author" v-if="articleInfo.publish_author">{{ articleInfo.publish_author }}</text>
                <text class="article-views">{{ totalViews }}次浏览</text>
            </view>
            <view class="article-content" v-html="articleInfo.content"></view>
        </view>

        <view v-else class="loading-state">
            <text>加载中...</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getSysArticleDetail } from '@/api/system/sysArticle'
import { onLoad } from '@dcloudio/uni-app'
import { ref, computed } from 'vue'

// 定义文章数据类型接口
interface ArticleInfo {
    id: number
    cid: number
    title: string
    image: string
    content: string
    publish_author: string
    publish_time: string
    virtual_views: number
    actual_views: number
    sort: number
    is_show: number
    create_at: string
    update_at: string
}

const articleInfo = ref<ArticleInfo | null>(null)

// 计算总浏览量
const totalViews = computed(() => {
    if (!articleInfo.value) return 0
    return (articleInfo.value.virtual_views || 0) + (articleInfo.value.actual_views || 0)
})

const fetchArticleDetail = async (id: string | number) => {
    try {
        uni.showLoading({ title: '加载中' })
        const result = await getSysArticleDetail(Number(id))
        if (result.code === 10000) {
            articleInfo.value = result.data
        } else {
            uni.showToast({ title: '获取文章详情失败', icon: 'none' })
        }
    } catch (error) {
        console.error('获取文章详情出错：', error)
        uni.showToast({ title: '获取文章详情出错', icon: 'none' })
    } finally {
        uni.hideLoading()
    }
}

onLoad((options) => {
    if (options?.id) {
        fetchArticleDetail(options.id)
    } else {
        uni.showToast({ title: '参数错误', icon: 'none' })
        setTimeout(() => {
            uni.navigateBack()
        }, 1500)
    }
})
</script>

<style lang="scss" scoped>
.article-detail {
    padding: 30rpx;
    min-height: 100vh;
    background-color: #ffffff;

    .article-card {
        .article-title {
            font-size: 36rpx;
            font-weight: 600;
            color: #333;
            margin-bottom: 20rpx;
            line-height: 1.4;
        }

        .article-meta {
            margin-bottom: 40rpx;
            display: flex;
            flex-wrap: wrap;
            gap: 20rpx;

            .article-time,
            .article-author,
            .article-views {
                font-size: 24rpx;
                color: #999;
            }

            .article-author {
                color: #666;
            }
        }

        .article-content {
            font-size: 30rpx;
            color: #333;
            line-height: 1.8;
            padding-bottom: 50rpx;

            :deep(img) {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 20rpx 0;
                border-radius: 8rpx;
            }

            :deep(p) {
                margin-bottom: 20rpx;
            }

            :deep(h1), :deep(h2), :deep(h3) {
                color: #333;
                margin: 30rpx 0 20rpx;
                font-weight: 600;
            }
        }
    }

    .loading-state {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200rpx;
        color: #999;
        font-size: 28rpx;
    }
}
</style>
