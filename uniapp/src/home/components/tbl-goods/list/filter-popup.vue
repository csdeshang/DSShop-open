<template>
    <uni-popup ref="popupRef" type="bottom" background-color="#f5f5f5" style="z-index: 9999;">
        <view class="filter-popup">
            <view class="filter-header">
                <text class="filter-title">筛选</text>
                <view class="filter-close" @click="close">
                    <uni-icons type="closeempty" size="24" color="#666" />
                </view>
            </view>

            <scroll-view class="filter-content" scroll-y>
                <view class="filter-section">
                    <view class="section-label">商品类型</view>
                    <view class="option-list">
                        <view class="option-item" :class="{ active: is_flashsale_goods === '1' }" @click="is_flashsale_goods = is_flashsale_goods === '1' ? '' : '1'">
                            <text>限时促销</text>
                        </view>
                        <view class="option-item" :class="{ active: is_wholesale_goods === '1' }" @click="is_wholesale_goods = is_wholesale_goods === '1' ? '' : '1'">
                            <text>批发</text>
                        </view>
                        <view class="option-item" :class="{ active: is_userdiscount_goods === '1' }" @click="is_userdiscount_goods = is_userdiscount_goods === '1' ? '' : '1'">
                            <text>会员价</text>
                        </view>
                        <view class="option-item" :class="{ active: is_distributor_goods === '1' }" @click="is_distributor_goods = is_distributor_goods === '1' ? '' : '1'">
                            <text>分销商品</text>
                        </view>
                    </view>
                </view>

                <view class="filter-section">
                    <view class="section-label">价格区间</view>
                    <view class="price-range">
                        <input class="price-input" type="digit" v-model="minPrice" placeholder="最低价" />
                        <text class="price-sep">-</text>
                        <input class="price-input" type="digit" v-model="maxPrice" placeholder="最高价" />
                    </view>
                </view>

                <view class="filter-section">
                    <view class="section-label">评分区间</view>
                    <view class="price-range">
                        <input class="price-input" type="digit" v-model="minScore" placeholder="最低评分" />
                        <text class="price-sep">-</text>
                        <input class="price-input" type="digit" v-model="maxScore" placeholder="最高评分" />
                    </view>
                    <view class="section-hint">评分范围 0-5 分</view>
                </view>
            </scroll-view>

            <view class="filter-footer">
                <view class="btn-reset" @click="handleReset">重置</view>
                <view class="btn-confirm" @click="handleConfirm">确定</view>
            </view>
        </view>
    </uni-popup>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

interface FilterPayload {
    is_flashsale_goods: '' | '1'
    is_wholesale_goods: '' | '1'
    is_userdiscount_goods: '' | '1'
    is_distributor_goods: '' | '1'
    min_price: string
    max_price: string
    min_goods_avg_score: string
    max_goods_avg_score: string
}

const props = defineProps<{
    params: Record<string, any>
}>()

const emit = defineEmits<{
    (e: 'confirm', payload: FilterPayload): void
}>()

const popupRef = ref<{ open: () => void; close: () => void } | null>(null)
const is_flashsale_goods = ref<'' | '1'>('')
const is_wholesale_goods = ref<'' | '1'>('')
const is_userdiscount_goods = ref<'' | '1'>('')
const is_distributor_goods = ref<'' | '1'>('')
const minPrice = ref('')
const maxPrice = ref('')
const minScore = ref('')
const maxScore = ref('')

function handleReset() {
    is_flashsale_goods.value = ''
    is_wholesale_goods.value = ''
    is_userdiscount_goods.value = ''
    is_distributor_goods.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    minScore.value = ''
    maxScore.value = ''
}

function toPayload(): FilterPayload {
    return {
        is_flashsale_goods: is_flashsale_goods.value,
        is_wholesale_goods: is_wholesale_goods.value,
        is_userdiscount_goods: is_userdiscount_goods.value,
        is_distributor_goods: is_distributor_goods.value,
        min_price: (minPrice.value || '').trim(),
        max_price: (maxPrice.value || '').trim(),
        min_goods_avg_score: (minScore.value || '').trim(),
        max_goods_avg_score: (maxScore.value || '').trim(),
    }
}

function selectionFromParams(p: Record<string, any>) {
    is_flashsale_goods.value = (p.is_flashsale_goods === 1 || p.is_flashsale_goods === '1') ? '1' : ''
    is_wholesale_goods.value = (p.is_wholesale_goods === 1 || p.is_wholesale_goods === '1') ? '1' : ''
    is_userdiscount_goods.value = (p.is_userdiscount_goods === 1 || p.is_userdiscount_goods === '1') ? '1' : ''
    is_distributor_goods.value = (p.is_distributor_goods === 1 || p.is_distributor_goods === '1') ? '1' : ''
    minPrice.value = p.min_price != null && p.min_price !== '' ? String(p.min_price) : ''
    maxPrice.value = p.max_price != null && p.max_price !== '' ? String(p.max_price) : ''
    minScore.value = p.min_goods_avg_score != null && p.min_goods_avg_score !== '' ? String(p.min_goods_avg_score) : ''
    maxScore.value = p.max_goods_avg_score != null && p.max_goods_avg_score !== '' ? String(p.max_goods_avg_score) : ''
}

function handleConfirm() {
    emit('confirm', toPayload())
    close()
}

function open() {
    selectionFromParams(props.params ?? {})
    popupRef.value?.open()
}

function close() {
    popupRef.value?.close()
}

defineExpose({ open, close })
</script>

<style lang="scss" scoped>
.filter-popup {
    background: #fff;
    border-radius: 24rpx 24rpx 0 0;
    max-height: 70vh;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    overflow: hidden;
}

.filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 28rpx 32rpx;
    border-bottom: 1rpx solid #eee;

    .filter-title {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
    }

    .filter-close {
        padding: 8rpx;
    }
}

.filter-content {
    flex: 1;
    padding: 24rpx 32rpx;
    max-height: 50vh;
    box-sizing: border-box;
    width: 100%;
    overflow-x: hidden;
    margin-bottom:30rpx;
}

.filter-section {
    .section-label {
        font-size: 28rpx;
        color: #666;
        margin-bottom: 20rpx;
    }

    &:not(:first-child) .section-label {
        margin-top: 32rpx;
    }

    .option-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20rpx;
    }

    .option-item {
        padding: 16rpx 28rpx;
        background: #f5f5f5;
        border-radius: 8rpx;
        font-size: 26rpx;
        color: #333;

        &.active {
            background: #fff0ed;
            color: #e1251b;
        }
    }
}

.price-range {
    display: flex;
    align-items: center;
    gap: 16rpx;
    min-width: 0;

    .price-input {
        flex: 1;
        min-width: 0;
        height: 72rpx;
        padding: 0 24rpx;
        background: #f5f5f5;
        border-radius: 8rpx;
        font-size: 28rpx;
        color: #333;
        box-sizing: border-box;
    }

    .price-sep {
        color: #999;
        font-size: 28rpx;
    }
}

.section-hint {
    margin-top: 12rpx;
    font-size: 24rpx;
    color: #999;
}

.filter-footer {
    display: flex;
    padding: 24rpx 32rpx;
    gap: 24rpx;
    border-top: 1rpx solid #eee;
    padding-bottom: calc(24rpx + env(safe-area-inset-bottom));

    .btn-reset,
    .btn-confirm {
        flex: 1;
        height: 80rpx;
        line-height: 80rpx;
        text-align: center;
        font-size: 30rpx;
        border-radius: 40rpx;
    }

    .btn-reset {
        background: #f5f5f5;
        color: #666;
    }

    .btn-confirm {
        background: #e1251b;
        color: #fff;
    }
}
</style>
