<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <view class="article-list">
                <view class="article-item" v-for="article in dataList" :key="article.id" @click="goToDetail(article.id)">
                    <view class="item-header">
                        <view class="article-title">{{ article.title }}</view>
                    </view>

                    <view class="item-body">
                        <view class="info-row">
                            <text class="info-item">{{ getTotalViews(article) }}次浏览</text>
                            <text class="info-item" v-if="article.publish_author">{{ article.publish_author }}</text>
                            <text class="info-item">{{ article.publish_time }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getSysArticleList } from '@/api/system/sysArticle'

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

const paging = ref(null)
const dataList = ref<ArticleInfo[]>([])
const cid = ref<number | null>(null)

const queryList = async (page_current: number, page_size: number) => {
    try {
        const params: any = {
            page_current,
            page_size,
        }

        // 如果有分类ID，添加到参数中
        if (cid.value) {
            params.cid = cid.value
        }

        const result = await getSysArticleList(params)
        paging.value.complete(result.data || [])
    } catch (e) {
        console.log('下拉加载', e)
        paging.value.complete(false)
    }
}

// 获取总浏览量
const getTotalViews = (article: ArticleInfo) => {
    return (article.virtual_views || 0) + (article.actual_views || 0)
}

// 跳转到文章详情
const goToDetail = (articleId: number) => {
    uni.navigateTo({
        url: `/shared/pages/system/article/detail?id=${articleId}`
    })
}

// 页面加载时获取参数
onLoad((options) => {
    if (options?.cid) {
        cid.value = Number(options.cid)
    }
})
</script>

<style scoped lang="scss">
.article-list {
    padding: 20rpx;
}

.article-item {
    background: #fff;
    border-radius: 12rpx;
    padding: 24rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

    .item-header {
        margin-bottom: 20rpx;

        .article-title {
            font-size: 32rpx;
            font-weight: 500;
            color: #333;
            line-height: 1.4;
        }
    }

    .item-body {
        .info-row {
            display: flex;
            align-items: center;
            gap: 24rpx;
            font-size: 26rpx;

            .info-item {
                color: #8c8c8c;
                font-weight: 400;
                white-space: nowrap;

                &:first-child {
                    color: #007aff;
                    background: #f0f8ff;
                    padding: 4rpx 12rpx;
                    border-radius: 12rpx;
                    font-weight: 500;
                }
            }
        }
    }
}
</style>