<template>
    <view class="follow-container">
        <view class="content">
            短视频 {{ isActive ? '(激活)' : '(未激活)' }}
        </view>
    </view>
</template>

<script setup lang="ts">
interface Props {
    isActive: boolean
}

const props = defineProps<Props>()

// 可以根据isActive状态来控制数据加载
// watch(() => props.isActive, (active) => {
//     if (active) {
//         // 激活时加载数据
//         console.log('短视频tab激活，开始加载数据')
//     } else {
//         // 非激活时可以暂停视频播放等
//         console.log('短视频tab未激活，暂停相关操作')
//     }
// })
</script>

<style scoped>
.follow-container {
    height: 100%;
    padding: 20rpx;
}

.content {
    text-align: center;
    margin-top: 100rpx;
    font-size: 32rpx;
    color: #333;
}
</style>