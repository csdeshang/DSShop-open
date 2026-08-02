<template>
    <!-- 文件类型筛选 -->
    <view class="file-type-filter">
        <view class="type-item" :class="{ active: searchParams.type === 'image' }" @click="handleChangeType('image')">
            图片
        </view>
        <view class="type-item" :class="{ active: searchParams.type === 'video' }" @click="handleChangeType('video')">
            视频
        </view>
        <!--
        <view class="type-item" :class="{ active: searchParams.type === 'audio' }" @click="handleChangeType('audio')">
            音频
        </view>
        <view class="type-item" :class="{ active: searchParams.type === 'file' }" @click="handleChangeType('file')">
            文件
        </view>
        -->
    </view>
</template>

<script setup lang="ts" name="ManageType">
import { watch } from 'vue'

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

const emit = defineEmits(['update:search-params'])

// 处理类型变更
const handleChangeType = (type: string) => {
    emit('update:search-params', { ...props.searchParams, type })
}
</script>

<style lang="scss" scoped>
.file-type-filter {
    display: flex;
    background-color: #fff;
    border-bottom: 1rpx solid #eee;
    padding: 20rpx 30rpx;
    
    .type-item {
        padding: 10rpx 20rpx;
        margin-right: 20rpx;
        font-size: 26rpx;
        color: #666;
        background-color: #f5f5f5;
        border-radius: 30rpx;
        
        &.active {
            color: #fff;
            background-color: #007aff;
        }
    }
}
</style>


