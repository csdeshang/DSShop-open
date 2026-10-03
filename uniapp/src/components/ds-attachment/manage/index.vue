<template>
    <uni-popup ref="popupRef" type="bottom" @change="onPopupChange" style="z-index: 999;">
        <view class="attachment-manager">
            <!-- 头部 -->
            <view class="header">
                <view class="header-left">
                    <text class="cancel-btn" @click="close">取消</text>
                </view>
                <view class="header-title">文件管理</view>
                <view class="header-right">
                    <text v-if="isSelectMode" class="done-btn" @click="toggleSelectMode">完成</text>
                    <text v-else class="select-btn" @click="toggleSelectMode">选择</text>
                </view>
            </view>


            <!-- 文件类型筛选组件 -->
            <manage-type :search-params="searchParams" @update:search-params="updateSearchParams" />

            <!-- 分类组件：暂不展示，默认 cid=0（全部），不影响列表/上传 -->
            <!--
            <manage-category ref="categoryRef" :search-params="searchParams"
                @update:search-params="updateSearchParams" />
            -->

            <!-- 文件列表组件：小程序自定义组件宿主需在父级分配 flex 高度 -->
            <manage-list class="manage-list-wrap" ref="fileListRef" :is-select-mode="isSelectMode"
                :selected-files="selectedFiles" :search-params="searchParams" @select="handleFileSelect"
                @selected-files-change="updateSelectedFiles" @delete="handleDeleteSuccess" />
        </view>
    </uni-popup>
</template>

<script setup lang="ts" name="DsAttachmentManage">
import { ref, reactive } from 'vue'
import ManageType from './manage-type.vue'
// import ManageCategory from './manage-category.vue'
import ManageList from './manage-list.vue'

// 定义组件事件
const emit = defineEmits(['select', 'upload'])


const props = defineProps({
    type: {
        type: String,
        default: ''
    }
})


// 弹窗引用
const popupRef = ref<any>(null)
// const categoryRef = ref<any>(null)
const fileListRef = ref<any>(null)


// 搜索参数
const searchParams = reactive({
    keywords: '',
    cid: 0,
    type: props.type
})

// 选择模式
const isSelectMode = ref(false)
const selectedFiles = ref<number[]>([])

// 打开弹窗
const open = () => {
    popupRef.value.open()
}

// 关闭弹窗
const close = () => {
    popupRef.value.close()
}

// 弹窗状态变化
const onPopupChange = (e: { show: boolean }) => {
    if (!e.show) {
        // 弹窗关闭时，重置状态
        isSelectMode.value = false
        selectedFiles.value = []
    }
}

// 更新搜索参数
const updateSearchParams = (newParams: any) => {
    Object.assign(searchParams, newParams)
}

// 文件选择处理
const handleFileSelect = (file: any) => {
    if (!isSelectMode.value) {
        emit('select', file)
        close()
    }
}

// 切换选择模式
const toggleSelectMode = () => {
    isSelectMode.value = !isSelectMode.value
    if (!isSelectMode.value) {
        selectedFiles.value = []
    }
}

// 更新选中文件
const updateSelectedFiles = (files: number[]) => {
    selectedFiles.value = files
}

// 删除成功处理
const handleDeleteSuccess = () => {
    selectedFiles.value = []
}

// 暴露方法
defineExpose({
    open
})
</script>

<style lang="scss" scoped>
.attachment-manager {
    width: 100%;
    height: 95vh;
    background-color: #f7f7f7;
    border-top-left-radius: 20rpx;
    border-top-right-radius: 20rpx;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;

    .header {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 30rpx;
        background-color: #fff;

        .header-left,
        .header-right {
            width: 120rpx;
        }

        .header-title {
            font-size: 32rpx;
            font-weight: 500;
            text-align: center;
            flex: 1;
        }

        .cancel-btn,
        .select-btn,
        .done-btn {
            font-size: 28rpx;
            color: #007aff;
        }

        .done-btn {
            font-weight: 500;
        }
    }

    /* 小程序中自定义组件宿主节点必须在此拿到剩余高度，内部 height:100% / z-paging 才生效 */
    .manage-list-wrap {
        flex: 1;
        min-height: 0;
        height: 0;
        overflow: hidden;
    }
}
</style>