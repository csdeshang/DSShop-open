<template>
    <view class="category-page">
        <!-- 分类列表 -->
        <view v-if="categoryList.length > 0" class="category-container">
            <view class="category-item" v-for="category in categoryList" :key="category.id">
                <!-- 一级分类 -->
                <view class="category-main" @click="goToArticleList(category)">
                    <view class="category-name">{{ category.name }}</view>
                </view>

                <!-- 二级分类网格 -->
                <view v-if="category.children && category.children.length > 0" class="sub-category-grid">
                    <view class="sub-category-item" v-for="subCategory in category.children" :key="subCategory.id"
                        @click="goToArticleList(subCategory)">
                        <view class="sub-category-name">{{ subCategory.name }}</view>
                    </view>
                </view>
            </view>
        </view>

        <!-- 空状态 -->
        <view v-else class="empty-state">
            <view class="empty-text">暂无分类数据</view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { getSysArticleCategoryTree } from '@/api/system/sysArticle'
import { onLoad } from '@dcloudio/uni-app'
import { ref } from 'vue'

// 定义分类数据类型接口
interface CategoryInfo {
    id: number
    pid: number
    name: string
    image: string
    description: string
    sort: number
    is_show: number
    create_at: string
    update_at: string
    children?: CategoryInfo[]
}

const categoryList = ref<CategoryInfo[]>([])

// 获取分类列表
const fetchCategoryList = async () => {
    try {
        uni.showLoading({ title: '加载中' })
        const result = await getSysArticleCategoryTree({})
        if (result.code === 10000) {
            categoryList.value = result.data
        } else {
            uni.showToast({ title: '获取分类列表失败', icon: 'none' })
        }
    } catch (error) {
        console.error('获取分类列表出错：', error)
        uni.showToast({ title: '获取分类列表出错', icon: 'none' })
    } finally {
        uni.hideLoading()
    }
}

// 跳转到文章列表页面
const goToArticleList = (category: CategoryInfo) => {
    uni.navigateTo({
        url: `/shared/pages/system/article/list?cid=${category.id}`
    })
}

// 页面加载
onLoad(() => {
    fetchCategoryList()
})
</script>

<style lang="scss" scoped>
.category-page {
    min-height: 100vh;
    background-color: #f8f9fa;

    .category-container {
        padding: 20rpx;

        .category-item {
            background-color: #ffffff;
            border-radius: 16rpx;
            margin-bottom: 24rpx;
            overflow: hidden;
            box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;

            &:hover {
                transform: translateY(-2rpx);
                box-shadow: 0 6rpx 20rpx rgba(0, 0, 0, 0.12);
            }

            .category-main {
                padding: 32rpx;
                border-bottom: 2rpx solid #f0f2f5;

                &:active {
                    background-color: #f8f9fa;
                }

                .category-name {
                    font-size: 36rpx;
                    font-weight: 600;
                    color: #1a1a1a;
                    line-height: 1.5;
                }
            }

            .sub-category-grid {
                background-color: #fafbfc;
                padding: 24rpx;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16rpx;

                .sub-category-item {
                    background: #ffffff;
                    border-radius: 12rpx;
                    padding: 20rpx 12rpx;
                    text-align: center;
                    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
                    transition: all 0.3s ease;

                    &:active {
                        transform: scale(0.95);
                        background-color: #f0f8ff;
                    }

                    .sub-category-name {
                        font-size: 26rpx;
                        color: #333;
                        font-weight: 500;
                        line-height: 1.4;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                }
            }
        }
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 400rpx;

        .empty-text {
            font-size: 28rpx;
            color: #999;
        }
    }
}
</style>