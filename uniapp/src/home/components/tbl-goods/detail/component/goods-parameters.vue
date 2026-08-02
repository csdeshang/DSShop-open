<template>
    <view class="goods-parameters-container" v-if="parsedParameters && parsedParameters.length > 0">
        <!-- 触发按钮 -->
        <view class="params-trigger" @click="openPopup">
            <view class="trigger-content">
                <text class="trigger-label">商品参数</text>
                <uni-icons type="right" size="16" color="#999"></uni-icons>
            </view>
        </view>
        
        <!-- 弹窗 -->
        <uni-popup ref="popup" type="bottom">
            <view class="popup-content">
                <view class="popup-header">
                    <text class="popup-title">商品参数</text>
                    <view class="popup-close" @click="closePopup">
                        <uni-icons type="closeempty" size="24" color="#999"></uni-icons>
                    </view>
                </view>
                <scroll-view scroll-y class="popup-body">
                    <view class="parameters-list">
                        <view class="parameter-item" v-for="(item, index) in parsedParameters" :key="index">
                            <text class="parameter-label">{{ item.name }}</text>
                            <text class="parameter-value">{{ item.value }}</text>
                        </view>
                    </view>
                </scroll-view>
            </view>
        </uni-popup>
    </view>
</template>

<script lang="ts" setup>
import { computed, ref } from 'vue';

const props = defineProps({
    goodsDetail: {
        type: Object,
        required: true
    }
});

// 使用any类型避免类型错误
const popup = ref<any>(null);

// 解析商品参数
const parsedParameters = computed(() => {
    if (!props.goodsDetail || !props.goodsDetail.goods_parameters) {
        return [];
    }
    
    try {
        const parameters = JSON.parse(props.goodsDetail.goods_parameters);
        // 按照sort排序
        return parameters.sort((a: { sort: number }, b: { sort: number }) => a.sort - b.sort);
    } catch (error) {
        console.error('解析商品参数失败:', error);
        return [];
    }
});

// 打开弹窗
const openPopup = () => {
    popup.value?.open('bottom');
};

// 关闭弹窗
const closePopup = () => {
    popup.value?.close();
};
</script>

<style lang="scss" scoped>
.goods-parameters-container {
    margin: 20rpx;
}

.params-trigger {
    background-color: #fff;
    border-radius: 16rpx;
    padding: 30rpx;
    box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
    
    .trigger-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        
        .trigger-label {
            font-size: 28rpx;
            color: #333;
            font-weight: 500;
        }
        

    }
}

.popup-content {
    background-color: #fff;
    border-top-left-radius: 24rpx;
    border-top-right-radius: 24rpx;
    padding-bottom: 40rpx;
    min-height: 35vh;
    
    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30rpx;
        border-bottom: 1rpx solid #f5f5f5;
        
        .popup-title {
            font-size: 32rpx;
            color: #333;
            font-weight: 500;
        }
        
        .popup-close {
            padding: 10rpx;
        }
    }
    
    .popup-body {
        max-height: 70vh;
        padding: 0 30rpx;
        
        .parameters-list {
            .parameter-item {
                display: flex;
                padding: 24rpx 0;
                border-bottom: 1rpx solid #f5f5f5;
                
                &:last-child {
                    border-bottom: none;
                }
                
                .parameter-label {
                    width: 240rpx;
                    color: #999;
                    font-size: 28rpx;
                    flex-shrink: 0;
                }
                
                .parameter-value {
                    flex: 1;
                    color: #333;
                    font-size: 28rpx;
                }
            }
        }
    }
}
</style>