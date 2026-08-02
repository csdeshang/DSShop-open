<template>
    <view>
        <!-- 商品选择器 -->
        <view class="goods-selector">
            <!-- 标题区域 -->
            <view class="section-title">
                <text class="title-text">{{ title }}</text>
                <text class="title-desc">{{ description }}</text>
                <text v-if="limit > 0 && selectedGoods.length > 0" class="limit-info">
                    ({{ selectedGoods.length }}/{{ limit }})
                </text>
            </view>

            <!-- 选择商品按钮 -->
            <view v-if="selectedGoods.length === 0" class="add-goods-btn" @click="open">
                <uni-icons type="plus" size="16" color="#ff2442"></uni-icons>
                <text>添加商品</text>
            </view>

            <!-- 已选择的商品 -->
            <view v-else class="selected-goods">
                <view v-for="(goods, index) in selectedGoods" :key="goods.id" class="goods-card"
                    :class="[goods._isSimple ? 'simple-mode' : '']">
                    <!-- 简约模式 -->
                    <template v-if="goods._isSimple">
                        <view class="simple-goods-info">
                            <uni-icons type="shop" size="16" color="#999"></uni-icons>
                            <text class="simple-goods-name">{{ goods.goods_name }}</text>
                            <text class="simple-goods-id">(ID: {{ goods.id }})</text>
                        </view>
                    </template>
                    <!-- 完整模式 -->
                    <template v-else>
                        <image :src="formatImageUrl(goods.cover_image, ThumbnailPresets.medium, 'goods')" class="goods-thumb" mode="aspectFill">
                        </image>
                        <view class="goods-info">
                            <text class="goods-name">{{ goods.goods_name }}</text>
                            <text class="goods-price">¥{{ goods.goodsSku.sku_price }}</text>
                        </view>
                    </template>

                    <view class="remove-btn" @click="removeGoods(index)">
                        <uni-icons type="clear" size="14" color="#999"></uni-icons>
                    </view>
                </view>

                <!-- 添加更多商品 -->
                <view class="add-more-btn" @click="open">
                    <uni-icons type="plus" size="14" color="#ff2442"></uni-icons>
                    <text>添加更多</text>
                </view>
            </view>
        </view>

        <!-- 商品选择器弹窗 -->
        <uni-popup ref="popup" type="bottom">
            <view class="goods-select-container">

                <view class="header">
                    <text class="title">选择商品</text>
                    <text class="close-btn" @click="close">取消</text>
                </view>

                <!-- 搜索栏 -->
                <view class="search-section">
                    <view class="search-box">
                        <uni-icons type="search" size="16" color="#999"></uni-icons>
                        <input v-model="searchText" placeholder="搜索商品名称" class="search-input" @input="onSearchInput" />
                    </view>
                </view>

                <!-- 商品列表 -->
                <z-paging ref="paging" v-model="dataList" @query="queryList" class="goods-list" :fixed="false">

                    <template #default>
                        <view v-for="item in dataList" :key="item.id" class="goods-item" @click="selectGoods(item)">
                            <view class="goods-info">
                                <image :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium, 'goods')" class="goods-image" mode="aspectFill">
                                </image>
                                <view class="goods-details">
                                    <text class="goods-name">{{ item.goods_name }}</text>
                                    <text class="goods-price">¥{{ item.goodsSku.sku_price }}</text>
                                    <text class="goods-stock">库存：{{ item.goodsSku.sku_stock }}</text>
                                </view>
                            </view>
                            <view class="select-icon">
                                <uni-icons v-if="isSelected(item)" type="checkbox-filled" size="20"
                                    color="#ff2442"></uni-icons>
                                <uni-icons v-else type="circle" size="20" color="#ddd"></uni-icons>
                            </view>
                        </view>
                    </template>

                    <template #empty>
                        <view class="empty-state">
                            <uni-icons type="shop" size="40" color="#ccc"></uni-icons>
                            <text class="empty-text">暂无商品</text>
                        </view>
                    </template>
                </z-paging>

                <!-- 底部确认区域 -->
                <view class="footer">
                    <view class="selected-info">
                        <text class="selected-count">
                            已选择 {{ selectedGoods.length }}{{ props.limit > 0 ? `/${props.limit}` : '' }} 件商品
                        </text>
                    </view>
                    <button class="confirm-btn" @click="confirmSelect" :disabled="selectedGoods.length === 0">
                        确认选择
                    </button>
                </view>
            </view>
        </uni-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, watch } from "vue"

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'


import { getTblGoodsPages } from "@/api/select"


const paging = ref<any>(null)
const dataList = ref<any[]>([])
const selectedGoods = ref<any[]>([])
const searchText = ref('')
const searchTimer = ref<any>(null)
const popup = ref<any>(null)

const props = defineProps({
    // v-model:goods-ids 支持（商品ID数组）
    goodsIds: {
        type: Array,
        default: () => []
    },
    // 商品类型
    is_distributor_goods: {
        type: String,
        default: ''
    },
    // 最大选择数量，0表示无限制
    limit: {
        type: Number,
        default: 0
    },
    // 标题
    title: {
        type: String,
        default: '关联商品'
    },
    // 描述
    description: {
        type: String,
        default: '（可选）'
    }
})

const emit = defineEmits(['update:goodsIds', 'change'])

// 查询参数 
const params = ref({
    is_distributor_goods: props.is_distributor_goods,
    keyword: ''
});

// 打开弹窗
const open = () => {
    popup.value?.open()
}

// 关闭弹窗
const close = () => {
    popup.value?.close()
}

// 搜索输入处理
const onSearchInput = () => {
    if (searchTimer.value) {
        clearTimeout(searchTimer.value)
    }
    searchTimer.value = setTimeout(() => {
        params.value.keyword = searchText.value
        paging.value?.reload()
    }, 500)
}

// 检查商品是否已选择
const isSelected = (item: any) => {
    return selectedGoods.value.some(goods => goods.id === item.id)
}

// 选择商品
const selectGoods = (item: any) => {
    const index = selectedGoods.value.findIndex(goods => goods.id === item.id)

    if (index > -1) {
        // 已选中，取消选择
        selectedGoods.value.splice(index, 1)
    } else {
        // 未选中，检查是否达到限制
        if (props.limit > 0 && selectedGoods.value.length >= props.limit) {
            uni.showToast({
                title: `最多只能选择${props.limit}个商品`,
                icon: 'none'
            })
            return
        }
        // 添加到选择列表
        selectedGoods.value.push(item)
    }
}

// 移除商品
const removeGoods = (index: number) => {
    selectedGoods.value.splice(index, 1)
    updateValue()
}

// 确认选择
const confirmSelect = () => {
    // 移除简约模式标记，使用完整的商品信息
    selectedGoods.value = selectedGoods.value.map(item => {
        const { _isSimple, ...cleanItem } = item
        return cleanItem
    })
    updateValue()
    close()
}

// 更新值并触发事件
const updateValue = () => {
    emit('update:goodsIds', selectedGoods.value.map(item => item.id))
    emit('change', selectedGoods.value)
}

// 获取商品列表
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getTblGoodsPages({
            page_current,
            page_size,
            ...params.value
        })
        paging.value.complete(result.data.data)
    } catch (e) {
        console.error('获取商品列表失败', e)
        paging.value.complete(false)
    }
}

// 获取选中的商品ID数组
const getSelectedGoodsIds = () => {
    return selectedGoods.value.map(item => item.id)
}

// 监听 goodsIds 变化，初始化简约显示模式
watch(() => props.goodsIds, (newIds) => {
    if (newIds && newIds.length > 0) {
        // 如果当前selectedGoods为空但有goodsIds，说明是编辑模式初始化
        if (selectedGoods.value.length === 0) {
            // 创建简约商品对象用于显示
            selectedGoods.value = newIds.map((id, index) => ({
                id: id,
                goods_name: `商品${index + 1}`,
                cover_image: '',
                goodsSku: { sku_price: '--' },
                _isSimple: true // 标记为简约模式
            }))
        }
    } else if (newIds && newIds.length === 0) {
        // 如果goodsIds被清空，也清空selectedGoods
        selectedGoods.value = []
    }
}, { immediate: true })

// 暴露方法给父组件
defineExpose({
    open,
    close,
    getSelectedGoodsIds,
    selectedGoods
})
</script>

<style lang="scss" scoped>
.goods-selector {
    padding: 30rpx 0;
    border-bottom: 1rpx solid #f8f8f8;

    .section-title {
        display: flex;
        align-items: center;
        margin-bottom: 20rpx;

        .title-text {
            font-size: 30rpx;
            color: #666;
        }

        .title-desc {
            font-size: 24rpx;
            color: #999;
            margin-left: 8rpx;
        }

        .limit-info {
            font-size: 24rpx;
            color: #999;
            margin-left: 8rpx;
        }
    }

    .add-goods-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24rpx;
        background: rgba(255, 36, 66, 0.05);
        border: 2rpx dashed #ff2442;
        border-radius: 12rpx;
        color: #ff2442;
        font-size: 28rpx;

        text {
            margin-left: 8rpx;
        }
    }

    .selected-goods {
        .goods-card {
            display: flex;
            align-items: center;
            padding: 20rpx;
            background: #f8f8f8;
            border-radius: 12rpx;
            margin-bottom: 16rpx;

            &.simple-mode {
                padding: 16rpx 20rpx;
                background: #f0f7ff;
                border: 1rpx solid #e1f0ff;

                .simple-goods-info {
                    display: flex;
                    align-items: center;
                    flex: 1;

                    .simple-goods-name {
                        font-size: 28rpx;
                        color: #333;
                        margin-left: 8rpx;
                        margin-right: 8rpx;
                    }

                    .simple-goods-id {
                        font-size: 24rpx;
                        color: #999;
                    }
                }
            }

            .goods-thumb {
                width: 80rpx;
                height: 80rpx;
                border-radius: 8rpx;
                margin-right: 16rpx;
            }

            .goods-info {
                flex: 1;
                display: flex;
                flex-direction: column;

                .goods-name {
                    font-size: 28rpx;
                    color: #333;
                    margin-bottom: 8rpx;
                    line-height: 1.4;
                    word-break: break-word;
                }

                .goods-price {
                    font-size: 24rpx;
                    color: #ff2442;
                    font-weight: 600;
                }
            }

            .remove-btn {
                padding: 8rpx;
                margin-left: 16rpx;
            }
        }

        .add-more-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20rpx;
            background: rgba(255, 36, 66, 0.05);
            border: 1rpx dashed #ff2442;
            border-radius: 8rpx;
            color: #ff2442;
            font-size: 24rpx;

            text {
                margin-left: 6rpx;
            }
        }
    }
}

.goods-select-container {
    background: #fff;
    border-radius: 24rpx 24rpx 0 0;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 32rpx 32rpx 20rpx;
    border-bottom: 1rpx solid #f0f0f0;

    .title {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
    }

    .close-btn {
        font-size: 28rpx;
        color: #666;
        padding: 8rpx 16rpx;
    }
}

.search-section {
    padding: 20rpx 32rpx;

    .search-box {
        display: flex;
        align-items: center;
        background: #f8f8f8;
        border-radius: 24rpx;
        padding: 16rpx 24rpx;

        .search-input {
            flex: 1;
            margin-left: 16rpx;
            font-size: 28rpx;
            color: #333;
        }
    }
}

.goods-list {
    height: 800rpx;
}

.goods-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24rpx 32rpx;
    border-bottom: 1rpx solid #f0f0f0;

    &:last-child {
        border-bottom: none;
    }

    .goods-info {
        display: flex;
        align-items: center;
        flex: 1;

        .goods-image {
            width: 120rpx;
            height: 120rpx;
            border-radius: 12rpx;
            margin-right: 24rpx;
        }

        .goods-details {
            flex: 1;
            display: flex;
            flex-direction: column;

            .goods-name {
                font-size: 30rpx;
                color: #333;
                font-weight: 500;
                margin-bottom: 8rpx;
                line-height: 1.4;
                word-break: break-word;
            }

            .goods-price {
                font-size: 28rpx;
                color: #ff2442;
                font-weight: 600;
                margin-bottom: 4rpx;
            }

            .goods-stock {
                font-size: 24rpx;
                color: #999;
            }
        }
    }

    .select-icon {
        padding: 16rpx;
    }
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100rpx 0;

    .empty-text {
        font-size: 28rpx;
        color: #999;
        margin-top: 20rpx;
    }
}

.footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24rpx 32rpx;
    border-top: 1rpx solid #f0f0f0;
    background: #fff;

    .selected-info {
        .selected-count {
            font-size: 28rpx;
            color: #666;
        }
    }

    .confirm-btn {
        background: linear-gradient(135deg, #ff2442 0%, #ff4757 100%);
        color: #fff;
        border-radius: 24rpx;
        padding: 16rpx 32rpx;
        font-size: 28rpx;
        font-weight: 500;
        border: none;

        &[disabled] {
            background: #ddd;
            color: #999;
        }

        &::after {
            border: none;
        }
    }
}
</style>
