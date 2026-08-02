<template>
    <view>
        <!-- 分类选择展示区域 -->
        <view class="category-section">
            <view class="option-item" @click="open">
                <view class="option-left">
                    <uni-icons type="bars" size="22" color="#666"></uni-icons>
                    <text class="option-text">分类</text>
                </view>
                <view class="option-right">
                    <text class="option-desc">{{ selectedCategoryName || '请选择' }}</text>
                    <uni-icons type="right" size="14" color="#999"></uni-icons>
                </view>
            </view>
        </view>

        <!-- 分类选择弹窗 -->
        <uni-popup ref="categoryPopup" type="bottom">
            <view class="category-popup">
                <view class="popup-header">
                    <text class="popup-title">选择分类</text>
                    <view class="popup-close" @click="close">
                        <uni-icons type="close" size="18" color="#666"></uni-icons>
                    </view>
                </view>

                <!-- 加载状态 -->
                <view v-if="loading" class="loading-container">
                    <uni-load-more status="loading"
                        content-text="{ contentText: { contentdown: '上拉显示更多', contentrefresh: '正在加载...', contentnomore: '没有更多数据了' } }"></uni-load-more>
                </view>

                <!-- 分类列表 -->
                <view v-else class="category-list">
                    <view v-for="item in categoryList" :key="item.id"
                        :class="['category-item', { selected: modelValue === item.id }]" @click="selectCategory(item)">
                        <text class="category-name">{{ item.name }}</text>
                        <uni-icons v-if="modelValue === item.id" type="checkmarkempty" size="18"
                            color="#ff2442"></uni-icons>
                    </view>
                </view>

                <!-- 空状态 -->
                <view v-if="!loading && categoryList.length === 0" class="empty-container">
                    <text class="empty-text">暂无分类数据</text>
                </view>
            </view>
        </uni-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { getVideoCategoryTree, getVideoCategoryList } from '@/video/api/home/videoCategory'

interface CategoryItem {
    id: string
    name: string
    sort?: number
}

// 定义props
const props = defineProps<{
    modelValue: string
}>()

// 定义事件
const emit = defineEmits<{
    'update:modelValue': [value: string]
    'change': [item: CategoryItem]
}>()

// 响应式数据
const categoryPopup = ref()
const categoryList = ref<CategoryItem[]>([])
const loading = ref(false)

// 计算当前选中的分类名称
const selectedCategoryName = computed(() => {
    const selectedItem = categoryList.value.find(item => item.id === props.modelValue)
    return selectedItem?.name || ''
})

// 获取分类数据
const fetchCategories = async () => {
    loading.value = true

    try {
        const response = await getVideoCategoryTree({
            type: 'short' // 短视频分类
        })

        if (response.code === 10000) {
            // 将树形结构转换为平铺列表
            const flattenCategories = flattenTree(response.data || [])
            categoryList.value = flattenCategories
        } else {
            uni.showToast({
                title: response.message || '获取分类失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('获取分类失败:', error)
        uni.showToast({
            title: '网络错误，请重试',
            icon: 'none'
        })
    } finally {
        loading.value = false
    }
}

// 将树形结构转换为平铺列表
const flattenTree = (tree: any[]): CategoryItem[] => {
    const result: CategoryItem[] = []
    
    const traverse = (nodes: any[]) => {
        nodes.forEach(node => {
            result.push({
                id: node.id,
                name: node.name,
                sort: node.sort,
            })
            
            // 如果有子分类，递归处理
            if (node.children && node.children.length > 0) {
                traverse(node.children)
            }
        })
    }
    
    traverse(tree)
    return result
}

// 打开弹窗
const open = () => {
    categoryPopup.value.open()
    // 每次打开时重新获取数据（可选，也可以只在组件挂载时获取一次）
    if (categoryList.value.length === 0) {
        fetchCategories()
    }
}

// 关闭弹窗
const close = () => {
    categoryPopup.value.close()
}

// 选择分类
const selectCategory = (item: CategoryItem) => {
    emit('update:modelValue', item.id)
    emit('change', item)
    close()
}

// 刷新分类数据
const refreshCategories = () => {
    fetchCategories()
}

// 组件挂载时获取数据
onMounted(() => {
    fetchCategories()
})

// 暴露方法给父组件
defineExpose({
    open,
    close,
    refreshCategories
})
</script>

<style lang="scss" scoped>
.category-section {
    padding: 20rpx 0;

    .option-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 30rpx 0;

        &:active {
            opacity: 0.6;
        }

        .option-left {
            display: flex;
            align-items: center;

            .option-text {
                font-size: 30rpx;
                color: #333;
                margin-left: 20rpx;
            }
        }

        .option-right {
            display: flex;
            align-items: center;

            .option-desc {
                font-size: 28rpx;
                color: #999;
                margin-right: 10rpx;
            }
        }
    }
}

.category-popup {
    background: #fff;
    border-radius: 24rpx 24rpx 0 0;
    max-height: 60vh;
}

.popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx 32rpx;
    border-bottom: 1rpx solid #f8f8f8;

    .popup-title {
        font-size: 32rpx;
        font-weight: 500;
        color: #333;
    }

    .popup-close {
        width: 60rpx;
        height: 60rpx;
        display: flex;
        align-items: center;
        justify-content: center;

        &:active {
            opacity: 0.6;
        }
    }
}

.loading-container {
    padding: 60rpx 32rpx;
    display: flex;
    justify-content: center;
    align-items: center;
}

.category-list {
    max-height: 50vh;
    overflow-y: auto;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx 32rpx;
    border-bottom: 1rpx solid #f8f8f8;
    transition: background-color 0.2s ease;

    &:active {
        background: #f8f8f8;
    }

    &.selected {
        background: rgba(255, 36, 66, 0.05);

        .category-name {
            color: #ff2442;
            font-weight: 500;
        }
    }

    &:last-child {
        border-bottom: none;
    }

    .category-name {
        font-size: 30rpx;
        color: #333;
        transition: color 0.2s ease;
    }
}

.empty-container {
    padding: 80rpx 32rpx;
    display: flex;
    justify-content: center;
    align-items: center;

    .empty-text {
        font-size: 28rpx;
        color: #999;
    }
}
</style>
