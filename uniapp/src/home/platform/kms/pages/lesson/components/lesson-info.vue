<template>
    <view class="lesson-info">
        <view class="info-container">
            <!-- 课时标题 -->
            <view class="lesson-title">{{ lessonDetail.lesson_name || '课时标题' }}</view>
            
            <!-- 课时标签 -->
            <view class="lesson-tags">
                <text class="tag type-tag">{{ lessonDetail.lesson_type_desc }}</text>
                <text class="tag status-tag">
                    {{ lessonDetail.is_free_desc }}
                </text>
            </view>
            
            <!-- 课时描述 -->
            <view class="lesson-desc" v-if="lessonDetail.lesson_desc">
                {{ lessonDetail.lesson_desc }}
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
const props = defineProps({
    lessonDetail: {
        type: Object,
        default: () => ({})
    }
});


</script>

<style lang="scss" scoped>
.lesson-info {
    margin: 20rpx;
    
    .info-container {
        background: #fff;
        border-radius: 20rpx;
        padding: 30rpx;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.08);
        
        .lesson-title {
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
            line-height: 1.4;
            margin-bottom: 20rpx;
        }
        
        .lesson-tags {
            display: flex;
            gap: 20rpx;
            margin-bottom: 20rpx;
            
            .tag {
                padding: 8rpx 20rpx;
                border-radius: 20rpx;
                font-size: 24rpx;
                
                &.type-tag {
                    background: #e3f2fd;
                    color: #1976d2;
                }
                
                &.status-tag {
                    background: #fff3e0;
                    color: #f57c00;
                }
            }
        }
        
        .lesson-desc {
            color: #666;
            font-size: 28rpx;
            line-height: 1.6;
        }
    }
}
</style> 