<template>
    <uni-popup ref="popup" type="bottom" :safe-area="false">
        <view class="goods-spec-popup" v-if="visible && goods">
            <view class="popup-header">
                <text class="header-title">选择规格</text>
                <uni-icons type="close" size="24" @click="handleClose"></uni-icons>
            </view>
            
            <view class="goods-info">
                <image 
                    :src="formatImageUrl(selectedSku?.sku_image || goods.cover_image, ThumbnailPresets.small, 'goods')" 
                    class="goods-image" 
                    mode="aspectFill"
                />
                <view class="info-content">
                    <text class="goods-name">{{ goods.goods_name }}</text>
                    <text class="goods-price">¥{{ selectedSku?.sku_price || goods.goodsSku.sku_price }}</text>
                    <text class="goods-stock" v-if="selectedSku">库存: {{ selectedSku.sku_stock }}件</text>
                </view>
            </view>
            
            <!-- SKU选择列表 -->
            <view class="sku-section">
                <text class="section-title">请选择规格</text>
                <view class="sku-list">
                    <view 
                        v-for="sku in goods.goodsSkuList" 
                        :key="sku.id"
                        class="sku-item"
                        :class="{ 
                            'selected': selectedSkuId === sku.id,
                            'disabled': sku.sku_stock <= 0
                        }"
                        @click="selectSku(sku)"
                    >
                        <view class="sku-info">
                            <text class="sku-name">{{ sku.sku_name || '默认规格' }}</text>
                            <text class="sku-price">¥{{ sku.sku_price }}</text>
                        </view>
                        <text class="sku-stock">库存: {{ sku.sku_stock }}</text>
                    </view>
                </view>
            </view>
            
            <view class="quantity-section">
                <text class="quantity-label">数量</text>
                <view class="quantity-control">
                    <view class="minus-btn" @click="decreaseQuantity">
                        <uni-icons type="minus" size="22" color="#ff6600"></uni-icons>
                    </view>
                    <text class="quantity">{{ quantity }}</text>
                    <view class="plus-btn" @click="increaseQuantity">
                        <uni-icons type="plus" size="22" color="#ff6600"></uni-icons>
                    </view>
                </view>
            </view>
            
            <view class="action-buttons">
                <button class="confirm-btn" @click="handleConfirm" :disabled="!canConfirm">
                    确认添加
                </button>
            </view>
        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

interface GoodsSku {
    id: number
    goods_id: number
    sku_name: string
    sku_image: string
    sku_price: number
    market_price: number
    sku_stock: number
    is_default: number
}

interface Goods {
    id: number
    goods_name: string
    cover_image: string
    goodsSku: GoodsSku
    goodsSkuList: GoodsSku[]
}

interface Props {
    visible: boolean
    goods: Goods | null
}

interface Emits {
    (e: 'close'): void
    (e: 'confirm', data: { goods: Goods, selectedSpec: GoodsSku, quantity: number }): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const popup = ref()
const quantity = ref(1)
const selectedSkuId = ref<number | null>(null)

// 当前选中的SKU
const selectedSku = computed(() => {
    if (!props.goods || !selectedSkuId.value) return null
    return props.goods.goodsSkuList.find(sku => sku.id === selectedSkuId.value) || null
})

// 是否可以确认
const canConfirm = computed(() => {
    return selectedSku.value && quantity.value > 0 && 
           quantity.value <= selectedSku.value.sku_stock
})

// 监听visible变化
watch(() => props.visible, (newVal) => {
    if (newVal) {
        popup.value?.open()
        resetState()
    } else {
        popup.value?.close()
    }
})

const resetState = () => {
    quantity.value = 1
    selectedSkuId.value = null
    
    // 如果只有一个SKU，自动选中
    if (props.goods?.goodsSkuList && props.goods.goodsSkuList.length === 1) {
        selectedSkuId.value = props.goods.goodsSkuList[0].id
    }
}

const handleClose = () => {
    emit('close')
}

const selectSku = (sku: GoodsSku) => {
    if (sku.sku_stock <= 0) return
    
    if (selectedSkuId.value === sku.id) {
        selectedSkuId.value = null
    } else {
        selectedSkuId.value = sku.id
        // 重置数量，确保不超过库存
        if (quantity.value > sku.sku_stock) {
            quantity.value = Math.min(1, sku.sku_stock)
        }
    }
}

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--
    }
}

const increaseQuantity = () => {
    const maxStock = selectedSku.value?.sku_stock || 999
    if (quantity.value < maxStock) {
        quantity.value++
    }
}

const handleConfirm = () => {
    if (!canConfirm.value || !props.goods || !selectedSku.value) return
    
    emit('confirm', {
        goods: props.goods,
        selectedSpec: selectedSku.value,
        quantity: quantity.value
    })
}
</script>

<style lang="scss" scoped>
.goods-spec-popup {
    background: #fff;
    border-radius: 20rpx 20rpx 0 0;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    padding-bottom: 140rpx; /* 为底部结算栏预留空间 */
}

.popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx;
    border-bottom: 1rpx solid #f0f0f0;
    
    .header-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
    }
}

.goods-info {
    display: flex;
    padding: 30rpx;
    align-items: flex-start;
    
    .goods-image {
        width: 160rpx;
        height: 160rpx;
        border-radius: 12rpx;
        margin-right: 20rpx;
        flex-shrink: 0;
    }
    
    .info-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10rpx;
        
        .goods-name {
            font-size: 30rpx;
            font-weight: bold;
            color: #333;
            line-height: 1.4;
        }
        
        .goods-price {
            font-size: 36rpx;
            font-weight: bold;
            color: #ff6600;
        }
        
        .goods-stock {
            font-size: 24rpx;
            color: #666;
        }
    }
}

.sku-section {
    padding: 0 30rpx;
    flex: 1;
    overflow-y: auto;
    
    .section-title {
        font-size: 28rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 20rpx;
        display: block;
    }
}

.sku-list {
    display: flex;
    flex-direction: column;
    gap: 20rpx;
}

.sku-item {
    padding: 20rpx;
    border: 2rpx solid #e0e0e0;
    border-radius: 12rpx;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    &.selected {
        border-color: #ff6600;
        background: #fff7e6;
    }
    
    &.disabled {
        background: #f5f5f5;
        border-color: #e0e0e0;
        opacity: 0.6;
        
        .sku-info {
            .sku-name, .sku-price {
                color: #ccc;
            }
        }
        
        .sku-stock {
            color: #ccc;
        }
    }
}

.sku-info {
    display: flex;
    flex-direction: column;
    gap: 8rpx;
    flex: 1;
    
    .sku-name {
        font-size: 28rpx;
        font-weight: bold;
        color: #333;
    }
    
    .sku-price {
        font-size: 32rpx;
        font-weight: bold;
        color: #ff6600;
    }
}

.sku-stock {
    font-size: 24rpx;
    color: #666;
    margin-left: 20rpx;
}

.quantity-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx;
    border-top: 1rpx solid #f0f0f0;
    
    .quantity-label {
        font-size: 28rpx;
        color: #333;
    }
    
    .quantity-control {
        display: flex;
        align-items: center;
        gap: 30rpx;
        
        .minus-btn, .plus-btn {
            width: 60rpx;
            height: 60rpx;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .minus-btn {
            // 去掉背景和边框
        }
        
        .plus-btn {
            // 去掉背景
        }
        
        .quantity {
            font-size: 32rpx;
            font-weight: bold;
            color: #333;
            min-width: 60rpx;
            text-align: center;
        }
    }
}

.action-buttons {
    padding: 30rpx;
    
    .confirm-btn {
        width: 100%;
        height: 80rpx;
        background: #ff6600;
        color: #fff;
        font-size: 30rpx;
        font-weight: bold;
        border-radius: 40rpx;
        border: none;
        
        &:disabled {
            background: #ccc;
        }
    }
}
</style> 