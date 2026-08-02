<template>
    <!-- 分类标签栏 -->
    <scroll-view scroll-x class="category-tabs" show-scrollbar="false">
        <view class="category-tab add-category" @click="handleAddCategory">
             新增分类
        </view>

        <view class="category-tab" :class="{ active: searchParams.cid === 0 }" @click="handleChangeCate(0)">
            全部
        </view>
        <view v-for="cate in categoryList" :key="cate.id" class="category-tab"
            :class="{ active: searchParams.cid === cate.id }" @click="handleChangeCate(cate.id)">
            {{ cate.name }}
        </view>
        
    </scroll-view>

    <!-- 分类表单弹窗 -->
    <uni-popup ref="categoryPopup" type="dialog">
        <uni-popup-dialog :title="formMode === 'add' ? '新建分类' : '编辑分类'" :before-close="true"
            @confirm="submitCategoryForm" @close="closeCategoryForm">
            <input type="text" v-model="categoryForm.name" placeholder="请输入分类名称" class="input-field" />
        </uni-popup-dialog>
    </uni-popup>
</template>

<script setup lang="ts" name="ManageCategory">
import { ref, reactive, onMounted, watch } from 'vue'
import {
    getAttachmentCateList,
    createAttachmentCate,
    updateAttachmentCate,
    deleteAttachmentCate
} from '@/api/attachment'

// 定义分类接口
interface Category {
    id: number;
    name: string;
    [key: string]: any;
}

const props = defineProps({
    searchParams: {
        type: Object,
        required: true,
        default: () => ({
            type: '',
            cid: 0,
            keywords: ''
        })
    }
})

const emit = defineEmits(['update:search-params', 'refresh-list'])

// 分类列表
const categoryList = ref<Category[]>([])

// 弹窗引用
const categoryPopup = ref<any>(null)

// 分类表单
const formMode = ref<'add' | 'edit'>('add')
const categoryForm = reactive({
    id: 0,
    name: ''
})

// 监听挂载
onMounted(() => {
    fetchCategoryList(props.searchParams.type)
})

// 监听 searchParams.type 变化时重新获取分类列表
watch(
    () => props.searchParams.type,
    (newType, oldType) => {
        // 如果 type 变化，则重新获取分类列表
        if (newType !== oldType) {
            fetchCategoryList(newType)
        }
    }
)

// 获取分类列表
const fetchCategoryList = async (type: string) => {
    try {
        const res = await getAttachmentCateList({ type: type })
        if (res.code == 10000) {
            categoryList.value = res.data
        } else {
            uni.showToast({
                title: res.message || '获取分类失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('获取分类失败', error)
    }
}

// 处理分类切换
const handleChangeCate = (cateId: number) => {
    emit('update:search-params', { ...props.searchParams, cid: cateId })
}

// 处理添加分类
const handleAddCategory = () => {
    formMode.value = 'add'
    categoryForm.id = 0
    categoryForm.name = ''
    categoryPopup.value.open()
}

// 关闭分类表单
const closeCategoryForm = () => {
    categoryPopup.value.close()
}

// 提交分类表单
const submitCategoryForm = async () => {
    if (!categoryForm.name.trim()) {
        uni.showToast({
            title: '分类名称不能为空',
            icon: 'none'
        })
        return
    }

    try {
        if (formMode.value === 'add') {
            await createAttachmentCate({ name: categoryForm.name, type: props.searchParams.type })
            uni.showToast({
                title: '添加成功',
                icon: 'success'
            })
        } else {
            await updateAttachmentCate({
                id: categoryForm.id,
                name: categoryForm.name,
                type: props.searchParams.type
            })
            uni.showToast({
                title: '更新成功',
                icon: 'success'
            })
        }
        await fetchCategoryList(props.searchParams.type)
        emit('refresh-list')
        categoryPopup.value.close()
    } catch (error) {
        console.error('操作失败', error)
        uni.showToast({
            title: '操作失败',
            icon: 'none'
        })
    }
}

// 显示编辑分类表单
const showEditCategory = (category: Category) => {
    formMode.value = 'edit'
    categoryForm.id = category.id
    categoryForm.name = category.name
    categoryPopup.value.open()
}

// 确认删除分类
const confirmDeleteCategory = (category: Category) => {
    uni.showModal({
        title: '提示',
        content: `确定要删除分类"${category.name}"吗？`,
        success: async (res) => {
            if (res.confirm) {
                try {
                    await deleteAttachmentCate(category.id)
                    uni.showToast({
                        title: '删除成功',
                        icon: 'success'
                    })

                    // 如果删除的是当前选中的分类，则切换到全部
                    if (props.searchParams.cid === category.id) {
                        handleChangeCate(0)
                    }

                    await fetchCategoryList(props.searchParams.type)
                    emit('refresh-list')
                } catch (error) {
                    console.error('删除失败', error)
                    uni.showToast({
                        title: '删除失败',
                        icon: 'none'
                    })
                }
            }
        }
    })
}


</script>

<style lang="scss" scoped>
.category-tabs {
    background-color: #fff;
    white-space: nowrap;
    border-bottom: 1rpx solid #eee;
    padding: 10rpx 20rpx;

    .category-tab {
        display: inline-block;
        padding: 20rpx 30rpx;
        font-size: 28rpx;
        color: #666;
        position: relative;

        &.active {
            color: #007aff;
            font-weight: 500;

            &:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 30rpx;
                right: 30rpx;
                height: 4rpx;
                background-color: #007aff;
                border-radius: 2rpx;
            }
        }

        &.add-category {
            padding: 20rpx;
        }
    }
}

.input-field {
    height: 70rpx;
    border: 1rpx solid #eee;
    border-radius: 8rpx;
    padding: 0 20rpx;
    width: 100%;
    font-size: 28rpx;
}
</style>
