<template>
    <view class="lesson-list">
        <view class="list-container">
            <view class="list-header">
                <text class="header-title">课程目录</text>
                <text class="lesson-count">{{ lessonList.length }} 课时</text>
            </view>

            <view class="list-content">
                <view v-for="(item, index) in lessonList" :key="item.id" class="lesson-item"
                    :class="{ active: item.id === currentLessonId }" @click="handleLessonClick(item)">
                    <view class="lesson-left">
                        <view class="lesson-index">
                            <text class="index-number">{{ index + 1 }}</text>
                        </view>
                        <view class="lesson-info">
                            <text class="lesson-name">{{ item.lesson_name }}</text>
                            <view class="lesson-meta">
                                <text class="lesson-type">{{ item.lesson_type_desc }}</text>
                                <text class="lesson-status">
                                    {{ item.is_free_desc }}
                                </text>
                            </view>
                        </view>
                    </view>

                    <view class="lesson-right">
                        <view class="lesson-icon" v-if="item.lesson_type === 1">
                            <uni-icons type="videocam" size="16" color="#999" />
                        </view>
                        <view class="lesson-icon" v-else-if="item.lesson_type === 3">
                            <uni-icons type="headphones" size="16" color="#999" />
                        </view>
                        <view class="lesson-icon" v-else>
                            <uni-icons type="document" size="16" color="#999" />
                        </view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
const props = defineProps({
    lessonList: {
        type: Array,
        default: () => []
    },
    currentLessonId: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['lesson-change']);



const handleLessonClick = (lesson: any) => {
    emit('lesson-change', lesson.id);
};
</script>

<style lang="scss" scoped>
.lesson-list {
    margin: 20rpx;

    .list-container {
        background: #fff;
        border-radius: 20rpx;
        overflow: hidden;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.08);

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30rpx;
            border-bottom: 1rpx solid #f0f0f0;

            .header-title {
                font-size: 32rpx;
                font-weight: bold;
                color: #333;
            }

            .lesson-count {
                color: #666;
                font-size: 28rpx;
            }
        }

        .list-content {
            .lesson-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 30rpx;
                border-bottom: 1rpx solid #f8f9fa;
                cursor: pointer;
                transition: all 0.3s ease;

                &:last-child {
                    border-bottom: none;
                }

                &:hover {
                    background-color: #f8f9fa;
                }

                &.active {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #fff;

                    .lesson-name,
                    .lesson-type,
                    .lesson-status {
                        color: #fff;
                    }

                    .lesson-status.free {
                        background: rgba(255, 255, 255, 0.2);
                        color: #fff;
                    }
                }

                .lesson-left {
                    display: flex;
                    align-items: center;
                    flex: 1;

                    .lesson-index {
                        width: 60rpx;
                        height: 60rpx;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 30rpx;

                        .index-number {
                            color: #fff;
                            font-size: 24rpx;
                            font-weight: bold;
                        }
                    }

                    .lesson-info {
                        flex: 1;

                        .lesson-name {
                            font-size: 30rpx;
                            font-weight: 500;
                            color: #333;
                            margin-bottom: 10rpx;
                            display: block;
                        }

                        .lesson-meta {
                            display: flex;
                            gap: 20rpx;

                            .lesson-type,
                            .lesson-status {
                                font-size: 22rpx;
                                color: #666;
                            }
                        }
                    }
                }

                .lesson-right {
                    .lesson-icon {
                        color: #999;
                    }
                }
            }
        }
    }
}
</style>