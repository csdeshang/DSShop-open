<template>

    <view class="cart-container">
        <view v-if="loading" class="loading">加载中...</view>
        <view v-else-if="cartItems.length === 0">
            <DsEmpty text="购物车是空的" />
        </view>
        <view v-else>

            <view class="cart-header">
                <view class="cart-header-left">
                    购物车商品数量：{{ totalItemCount }}
                </view>
                <view @click="toggleManageMode" class="manage-button">
                    {{ isManageMode ? '取消' : '管理' }}
                </view>
            </view>


            <view class="mb-300">
                <uni-swipe-action>
                    <uni-swipe-action-item v-for="(item, itemIndex) in cartItems" :key="item.id || itemIndex">
                        <view class="cart-item">

                            <uni-icons type="checkbox-filled" class="checkbox" size="24"
                                :class="{ active: item.selected }"
                                @click="toggleItemSelection(itemIndex)"></uni-icons>
                            <view @click="goGoodsDetailPage(item.goods)">
                                <image class="item-image"
                                    :src="formatImageUrl(item.goodsSku.sku_image || item.goods.cover_image, ThumbnailPresets.medium, 'goods')"
                                    mode="aspectFill" />
                            </view>

                            <view class="item-info">
                                <text class="item-name" @click="goGoodsDetailPage(item.goods)">
                                    <text v-if="item.promotion_type" class="promotion-type">{{
                                        item.promotion_type_desc }}</text>
                                    {{ item.goods.goods_name }}
                                </text>
                                <text class="item-name">{{ item.goodsSku.sku_name }}</text>

                                <view class="item-price-control">
                                    <view class="price-group">
                                        <text class="item-price promotion-price">¥{{ item.promotion_price }}</text>
                                        <text
                                            v-if="item.promotion_price && item.promotion_price !== item.goodsSku.sku_price"
                                            class="original-price">¥{{ item.goodsSku.sku_price }}</text>
                                    </view>
                                    <view class="quantity-control">
                                        <text class="minus"
                                            @click="handleQuantityUpdate(itemIndex, 'dec')">-</text>
                                        <input type="number" v-model="item.quantity" class="input"
                                            @input="handleQuantityInput(itemIndex, $event)" />
                                        <text class="plus"
                                            @click="handleQuantityUpdate(itemIndex, 'inc')">+</text>
                                    </view>
                                </view>
                            </view>
                        </view>
                        <template v-slot:right>
                            <view @click="removeItem(itemIndex)" class="delete-item"><text>删除</text>
                            </view>
                        </template>
                    </uni-swipe-action-item>
                </uni-swipe-action>
            </view>

            <!-- 购物车底部占位-->
            <view class="cart-footer-placeholder"></view>
            <view class="cart-footer">
                <view class="footer-content">
                    <!-- 全选区域 -->
                    <view @click="toggleAllSelection" class="select-area">
                        <view class="select-all">
                            <uni-icons type="checkbox-filled" class="checkbox" size="24"
                                :class="{ active: isAllSelected }"></uni-icons>
                            <text class="select-text">全选({{ selectedItemCount }})</text>
                        </view>
                    </view>

                    <!-- 操作按钮区域 -->
                    <view class="action-buttons">
                        <template v-if="isManageMode">
                            <button class="ds-btn-md" @click="deleteSelectedItems">
                                删除
                            </button>
                        </template>
                        <template v-else>
                            <view class="price-info">
                                <text class="price-label">总价：</text>
                                <text class="price-amount">¥{{ totalPrice }}</text>
                            </view>
                            <button class="ds-btn-md" :disabled="selectedItemCount === 0" @click="checkout">
                                结算
                            </button>
                        </template>
                    </view>
                </view>
            </view>


        </view>
    </view>


</template>



<script lang="ts" setup>
import { ref, computed, reactive } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import { goGoodsDetailPage } from '@/utils/platform';

import DsEmpty from '@/components/ds-empty/index.vue';

// 引入购物车接口(根据platform 引入对应的接口)
import { getTblCartList, deleteTblCart, updateTblCart } from '@/home/api/tbl-cart/tblCart'; // 引入获取购物车数据的API


const loading = ref(true); // 加载状态
const cartItems = reactive<any[]>([]); // 购物车数据
const isManageMode = ref(false); // 管理模式状态


const props = defineProps({
    platform: {
        type: String,
        default: 'mall'
    }
});




// 计算总价
const totalPrice = computed(() => {
    return cartItems.reduce((total, item) => {
        if (item.selected) {
            return total + (parseFloat(item.promotion_price) * item.quantity);
        }
        return total;
    }, 0).toFixed(2);
});

// 计算购物车商品总数量
const totalItemCount = computed(() => {
    return cartItems.length;
});

// 计算已选商品数量
const selectedItemCount = computed(() => {
    return cartItems.filter(item => item.selected).length;
});

// 获取购物车数据
const loadCartData = async () => {
    loading.value = true;
    try {
        const response = await getTblCartList({}, props.platform);
        const items = Array.isArray(response.data) ? response.data : Object.values(response.data || {});
        cartItems.splice(0, cartItems.length, ...items.map((item: any) => ({ ...item, selected: true })));
    } catch (error) {
        console.error('获取购物车数据失败:', error);
        uni.showToast({
            title: '加载购物车失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

// 切换管理模式
const toggleManageMode = () => {
    isManageMode.value = !isManageMode.value;
};

// 切换商品选择状态
const toggleItemSelection = (itemIndex: number) => {
    const item = cartItems[itemIndex];
    item.selected = !item.selected;
};

// 切换全选状态
const toggleAllSelection = () => {
    const newSelectionState = !isAllSelected.value;
    cartItems.forEach(item => {
        item.selected = newSelectionState;
    });
};

// 检查是否所有商品都被选中
const isAllSelected = computed(() => {
    return cartItems.length > 0 && cartItems.every(item => item.selected);
});

// 删除选中的商品
const deleteSelectedItems = async () => {
    try {
        const selectedIds = cartItems
            .filter(item => item.selected)
            .map(item => item.id);

        if (selectedIds.length === 0) {
            uni.showToast({
                title: '请先选择商品',
                icon: 'none'
            });
            return;
        }

        const response = await deleteTblCart({ cart_ids: selectedIds }, props.platform);
        if (response.code === 10000) {
            for (let i = cartItems.length - 1; i >= 0; i--) {
                if (cartItems[i].selected) {
                    cartItems.splice(i, 1);
                }
            }

            uni.showToast({
                title: '删除成功',
                icon: 'success'
            });
        } else {
            uni.showToast({
                title: '删除失败',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('批量删除失败:', error);
        uni.showToast({
            title: '删除失败',
            icon: 'error'
        });
    }
};



import { debounce } from '@/utils/util';

const handleQuantityInput = debounce((itemIndex: number, event: any) => {
    const newValue = event.detail.value?.trim();
    const item = cartItems[itemIndex];

    const quantity = Math.max(1, Math.floor(newValue));

    item.quantity = quantity;
    handleQuantityUpdate(itemIndex, '');
}, 500);



// 统一处理数量更新
const handleQuantityUpdate = async (itemIndex: number, delta: string) => {
    const item = cartItems[itemIndex];

    let newQuantity = parseInt(item.quantity);


    if (delta === 'inc') {
        newQuantity = item.quantity + 1;
    } else if (delta === 'dec') {
        newQuantity = item.quantity - 1;
    }


    // 验证数量范围
    if (newQuantity < 1) {
        uni.showToast({
            title: '商品数量不能少于1',
            icon: 'none'
        });
        return;
    }

    if (newQuantity > item.goodsSku.sku_stock) {
        uni.showToast({
            title: `库存不足，已设置为最大可用数量：${item.goodsSku.sku_stock}`,
            icon: 'none'
        });
        newQuantity = item.goodsSku.sku_stock;
        item.quantity = newQuantity;
    }


    try {
        const response = await updateTblCart({
            cart_id: item.id,
            quantity: newQuantity
        }, props.platform);

        if (response.code === 10000) {
            item.quantity = newQuantity;
        } else {
            item.quantity = newQuantity;
            throw new Error(response.message || '更新失败');
        }
    } catch (error) {
        uni.showToast({
            title: '更新数量失败',
            icon: 'none'
        });
    }
};

const removeItem = async (itemIndex: number) => {
    try {
        const item = cartItems[itemIndex];
        const response = await deleteTblCart({ cart_ids: [item.id] }, props.platform);
        if (response.code === 10000) {
            cartItems.splice(itemIndex, 1);

            uni.showToast({
                title: '删除成功',
                icon: 'success'
            });
        } else {
            uni.showToast({
                title: '删除失败',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('删除失败:', error);
        uni.showToast({
            title: '删除失败',
            icon: 'error'
        });
    }
};


// 结算
const checkout = () => {
    const selectedCartIds = cartItems
        .filter(item => item.selected)
        .map(item => item.id);
    uni.navigateTo({
        url: `/home/platform/${props.platform}/pages/order/checkout?cart_ids=${selectedCartIds.join(',')}`
    });
};

// 页面显示时执行，确保每次返回页面都刷新数据
onShow(() => {
    loadCartData();
});
</script>

<style lang="scss" scoped>
.cart-container {
    background-color: #f8f8f8;
    padding: 20rpx;
    min-height: 80vh;
}

.loading {
    text-align: center;
    padding: 40rpx;
    color: #999;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx;
    background: #fff;
    border-radius: 12rpx;
    margin-bottom: 20rpx;

    .manage-button {
        color: #FF6034;
        font-size: 28rpx;
    }
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 24rpx;
    background: #fff;
    margin-bottom: 2rpx;

    .checkbox {
        margin-right: 16rpx;
        color: #ccc;

        &.active {
            color: #FF6034;
        }
    }

    .item-image {
        width: 160rpx;
        height: 160rpx;
        border-radius: 12rpx;
        margin-right: 20rpx;
    }

    .item-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;

        .item-name {
            font-size: 28rpx;
            color: #333;
            margin-bottom: 8rpx;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            .promotion-type {
                font-size: 22rpx;
                color: #FF6034;
                margin-right: 8rpx;
            }
        }

        .item-price-control {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16rpx;

            .price-group {
                display: flex;
                align-items: baseline;

                .promotion-price {
                    font-size: 32rpx;
                    color: #FF6034;
                    font-weight: bold;
                }

                .original-price {
                    font-size: 24rpx;
                    color: #999;
                    text-decoration: line-through;
                    margin-left: 12rpx;
                }
            }

            .quantity-control {
                display: flex;
                align-items: center;

                .minus,
                .plus {
                    width: 48rpx;
                    height: 48rpx;
                    line-height: 48rpx;
                    text-align: center;
                    background: #f5f5f5;
                    border-radius: 8rpx;
                    font-size: 28rpx;
                }

                .input {
                    width: 72rpx;
                    height: 48rpx;
                    text-align: center;
                    margin: 0 8rpx;
                    font-size: 26rpx;
                }
            }
        }
    }
}

.delete-item {
    background: #FF6034;
    color: #fff;
    width: 140rpx;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-footer-placeholder {
    height: 140rpx;
}

.cart-footer {
    position: fixed;
    left: 0;
    right: 0;
    bottom: calc(100rpx + env(safe-area-inset-bottom)); // 添加安全区域
    background: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.08);
    padding: 20rpx 24rpx;
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));

    .footer-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .select-area {
        .select-all {
            display: flex;
            align-items: center;

            .checkbox {
                color: #ccc;
                margin-right: 8rpx;

                &.active {
                    color: #FF6034;
                }
            }

            .select-text {
                font-size: 26rpx;
                color: #333;
            }
        }
    }

    .action-buttons {
        display: flex;
        align-items: center;

        .price-info {
            margin-right: 20rpx;

            .price-label {
                font-size: 26rpx;
                color: #333;
            }

            .price-amount {
                font-size: 32rpx;
                color: #FF6034;
                font-weight: bold;
            }
        }
    }
}

.mb-300 {
    margin-bottom: 40rpx;
}
</style>
