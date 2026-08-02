<template>
    <view class="ds-empty">
        <image :src="image || defaultImage" class="empty-image" mode="aspectFit" />
        <text class="empty-text">{{ text }}</text>
        <view class="empty-description" v-if="description">
            <text class="description-text">{{ description }}</text>
        </view>
        <view class="empty-action" v-if="showButton">
            <button class="action-button" :class="buttonType" @click="handleButtonClick">
                {{ buttonText }}
            </button>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref } from 'vue';

defineOptions({
  name: 'DsEmpty'
});

// 组件接收的属性，简化后只保留核心参数
const props = defineProps({
    // 空状态图片
    image: {
        type: String,
        default: ''
    },
    // 空状态文字
    text: {
        type: String,
        default: '暂无数据'
    },
    // 附加描述文字
    description: {
        type: String,
        default: ''
    },
    // 是否显示按钮
    showButton: {
        type: Boolean,
        default: false
    },
    // 按钮文字
    buttonText: {
        type: String,
        default: '点击刷新'
    },
    // 按钮类型：primary, secondary, plain
    buttonType: {
        type: String,
        default: 'primary'
    }
});

// 组件事件
const emit = defineEmits(['click']);

// 默认空状态图片
const defaultImage = ref('/static/images/empty/empty.png');

// 按钮点击事件
const handleButtonClick = () => {
    emit('click');
};
</script>

<style lang="scss" scoped>
.ds-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30rpx;
    margin-top: 200rpx;
    box-sizing: border-box;

    .empty-image {
        width: 240rpx;
        height: 240rpx;
        margin-bottom: 30rpx;
    }

    .empty-text {
        font-size: 32rpx;
        line-height: 1.5;
        text-align: center;
        margin-bottom: 16rpx;
        color: #999;
    }

    .empty-description {
        margin-bottom: 30rpx;

        .description-text {
            font-size: 28rpx;
            line-height: 1.5;
            text-align: center;
            color: #C8C9CC;
        }
    }

    .empty-action {
        margin-top: 30rpx;

        .action-button {
            min-width: 240rpx;
            height: 80rpx;
            line-height: 80rpx;
            font-size: 28rpx;
            border-radius: 40rpx;
            padding: 0 40rpx;

            &.primary {
                background-color: #FF0036;
                color: #fff;
            }

            &.secondary {
                background-color: #f8f8f8;
                color: #333;
                border: 1px solid #eee;
            }

            &.plain {
                background-color: #fff;
                color: #FF0036;
                border: 1px solid #FF0036;
            }
        }
    }
}
</style>
