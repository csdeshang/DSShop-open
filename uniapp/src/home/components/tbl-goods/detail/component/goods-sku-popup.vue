<template>
    <uni-popup ref="popup" type="bottom">
        <view class="sku-popup">
            <!-- 顶部商品信息 -->
            <view class="goods-info">
                <image class="goods-image"
                    :src="formatImageUrl(currentSku?.sku_image || goodsDetail.cover_image, ThumbnailPresets.medium, 'goods')"
                    mode="aspectFill">
                </image>
                <view class="info-right">
                    <!-- 促销标签 -->
                    <view class="promotion-tags" v-if="hasPromotions">
                        <view class="promotion-tag" v-if="hasFlashsale">
                            {{ currentSku?.promotions?.flashsale.promotion_type_desc }}
                        </view>
                        <view class="promotion-tag" v-if="hasWholesale">
                            {{ currentSku?.promotions?.wholesale.promotion_type_desc }}
                        </view>
                        <view class="promotion-tag" v-if="hasUserDiscount">
                            {{ currentSku?.promotions?.userdiscount.promotion_type_desc }}
                        </view>
                    </view>

                    <!-- 价格显示 -->
                    <view class="price-container">
                        <view class="price" v-if="hasPromotion">
                            <text class="promotion-price">¥{{ currentSku?.promotion_price }}</text>
                            <text class="original-price">¥{{ currentSku?.sku_price }}</text>
                        </view>
                        <view class="price" v-else>
                            ¥{{ currentSku?.sku_price || goodsDetail.goods_minprice }}
                        </view>
                    </view>

                    <view class="stock">库存: {{ currentSku?.sku_stock || goodsDetail.stock_num }}件</view>
                    <view class="selected">已选: {{ getSelectedText() }}</view>
                </view>
                <view class="close-btn" @click="close">
                    <uni-icons type="closeempty" size="20"></uni-icons>
                </view>
            </view>

            <!-- SKU 选项列表 -->
            <scroll-view class="sku-list" scroll-y>
                <view v-for="(spec, index) in specList" :key="index" class="sku-item">
                    <view class="title">{{ spec.name }}</view>
                    <view class="options">
                        <view v-for="(option, idx) in spec.values" :key="idx" class="option-item"
                            :class="getOptionClass(spec.name, option)" @click="selectSpec(spec.name, option)">
                            {{ option }}
                        </view>
                    </view>
                </view>

                <!-- 数量选择 -->
                <view class="quantity-section">
                    <view class="title">数量</view>
                    <view class="number-box">
                        <text class="minus" @click="handleMinus">-</text>
                        <input type="number" v-model="quantity" class="input" />
                        <text class="plus" @click="handlePlus">+</text>
                    </view>
                </view>
            </scroll-view>

            <!-- 底部按钮 -->
            <view class="bottom-btn">
                <button class="add-cart-btn" v-if="actionMode === 'addToCart' || actionMode === 'show'"
                    @click="handleAddToCart">
                    加入购物车
                </button>
                <button class="buy-now-btn" v-if="actionMode === 'buyNow' || actionMode === 'show'"
                    @click="handleBuyNow">
                    立即购买
                </button>
            </view>
        </view>
    </uni-popup>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image';

import { addTblCart } from '@/home/api/tbl-cart/tblCart'

const props = defineProps({
    goodsDetail: {
        type: Object,
        required: true
    },
    selectedSku: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['confirm', 'close']);

const popup = ref();
const quantity = ref(1);
const selectedSpecs = ref<Record<string, string>>({});
const actionMode = ref('');

// 当前选中的SKU
const currentSku = computed(() => {
    if (!isSkuSelected.value || !props.goodsDetail?.skuList || !Array.isArray(props.goodsDetail.skuList)) {
        return null;
    }

    const selectedSpecStr = Object.values(selectedSpecs.value).join(',');
    return props.goodsDetail.skuList.find((sku: any) => sku.sku_spec_format === selectedSpecStr);
});

// 判断是否有促销信息
const hasPromotions = computed(() => {
    return hasFlashsale.value || hasWholesale.value || hasUserDiscount.value;
});

// 判断是否有限时折扣
const hasFlashsale = computed(() => {
    return currentSku.value?.promotions?.flashsale?.enabled;
});

// 判断是否有批发价
const hasWholesale = computed(() => {
    return currentSku.value?.promotions?.wholesale?.enabled &&
        currentSku.value?.promotions?.wholesale?.rules?.length > 0;
});

// 判断是否有会员折扣
const hasUserDiscount = computed(() => {
    return currentSku.value?.promotions?.userdiscount?.enabled &&
        currentSku.value?.promotions?.userdiscount?.levels?.length > 0;
});

// 是否有促销价格
const hasPromotion = computed(() => {
    return currentSku.value?.promotion_price &&
        currentSku.value.promotion_price < currentSku.value.sku_price;
});

// 是否已选择完所有规格
const isSkuSelected = computed(() => {
    // 确保 goodsSpec 存在且是数组
    if (!props.goodsDetail?.goodsSpec || !Array.isArray(props.goodsDetail.goodsSpec)) {
        return false;
    }
    return props.goodsDetail.goodsSpec.every((spec: any) => selectedSpecs.value[spec.spec_name]);
});

// 获取已选择的规格文本
const getSelectedText = () => {
    const selected = [];
    for (const [key, value] of Object.entries(selectedSpecs.value)) {
        if (value) {
            selected.push(`${key}:${value}`);
        }
    }
    return selected.length ? selected.join('、') : '请选择规格';
};

// 判断某个选项是否可选
const isOptionAvailable = (specName: string, optionValue: string) => {
    return true;
};

// 获取选项的类名
const getOptionClass = (specName: string, optionValue: string) => {
    return {
        active: selectedSpecs.value[specName] === optionValue,
        disabled: !isOptionAvailable(specName, optionValue)
    };
};

// 选择规格
const selectSpec = (specName: string, value: string) => {
    if (isOptionAvailable(specName, value)) {
        selectedSpecs.value[specName] = value;
        // 选择完规格后直接触发确认事件
        if (isSkuSelected.value) {
            confirmSelect();
        }
    }
};

// 获取规格选项列表
const specList = computed(() => {
    // 确保 goodsSpec 存在且是数组
    if (!props.goodsDetail?.goodsSpec || !Array.isArray(props.goodsDetail.goodsSpec)) {
        return [];
    }
    return props.goodsDetail.goodsSpec.map((spec: any) => ({
        name: spec.spec_name,
        values: spec.spec_value.split(',')
    }));
});

// 确认选择
const confirmSelect = () => {
    if (isSkuSelected.value) {
        emit('confirm', {
            sku: currentSku.value,
            quantity: quantity.value,
            specs: selectedSpecs.value
        });
    }
};

// 加入购物车
const handleAddToCart = async () => {
    if (isSkuSelected.value) {
        const params = {
            goods_id: props.goodsDetail.id,
            sku_id: currentSku.value.id,
            quantity: quantity.value,
            promotion_platform: currentSku.value.promotion_platform || '',
            promotion_type: currentSku.value.promotion_type || '',
            promotion_related_id: currentSku.value.promotion_related_id || 0
        };

        const platform = props.goodsDetail.platform || 'mall';
        const res = await addTblCart(params, platform);

        if (res.code === 10000) {
            uni.showToast({
                title: '已加入购物车',
                icon: 'success'
            });
            close();
        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }

    } else {
        uni.showToast({
            title: '请先选择规格',
            icon: 'none'
        });
    }
};

// 立即购买
const handleBuyNow = async () => {
    if (isSkuSelected.value) {
        const params = {
            goods_id: props.goodsDetail.id,
            sku_id: currentSku.value.id,
            quantity: quantity.value,
            promotion_platform: currentSku.value.promotion_platform || '',
            promotion_type: currentSku.value.promotion_type || '',
            promotion_related_id: currentSku.value.promotion_related_id || 0
        };

        const platform = props.goodsDetail.platform || 'mall';
        const res = await addTblCart(params, platform);

        if (res.code === 10000) {
            // 跳转至订单结算页面
            const platform = props.goodsDetail.platform || 'mall';
            uni.navigateTo({
                url: '/home/platform/' + platform + '/pages/order/checkout?cart_ids=' + res.data
            });

        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }
    } else {
        uni.showToast({
            title: '请先选择规格',
            icon: 'none'
        });
    }
};

// 打开弹窗
const open = (mode: string) => {
    actionMode.value = mode;
    popup.value?.open();
};

// 关闭弹窗
const close = () => {
    popup.value?.close();
    emit('close');
};

// 处理减少数量
const handleMinus = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// 处理增加数量
const handlePlus = () => {
    const maxStock = currentSku.value?.sku_stock || props.goodsDetail.stock_num;
    if (quantity.value < maxStock) {
        quantity.value++;
    }
};

// 初始化选择
const initSelectedSpecs = () => {
    // 创建新对象，避免直接修改响应式对象
    const newSelectedSpecs: Record<string, string> = {};

    try {
        // 首先检查是否有skuList数据
        if (!props.goodsDetail.skuList || !props.goodsDetail.goodsSpec) {
            console.log('商品规格数据不完整');
            selectedSpecs.value = newSelectedSpecs;
            return;
        }

        // 如果有selectedSku.sku_id，则根据sku_id查找对应的SKU信息
        if (props.selectedSku && props.selectedSku.sku_id) {
            // 从skuList中查找匹配的SKU
            const matchedSku = props.goodsDetail.skuList.find((sku: any) => sku.id === props.selectedSku.sku_id);

            if (matchedSku && matchedSku.sku_spec_format) {
                // 找到了匹配的SKU，使用其规格信息
                const specValues = matchedSku.sku_spec_format.split(',');
                const specNames = props.goodsDetail.goodsSpec.map((spec: any) => spec.spec_name);

                // 将规格名称和值对应起来
                for (let i = 0; i < Math.min(specNames.length, specValues.length); i++) {
                    if (specNames[i] && specValues[i]) {
                        newSelectedSpecs[specNames[i]] = specValues[i];
                    }
                }
            } else {
                // 找不到匹配的SKU，尝试使用默认SKU
                useDefaultSku(newSelectedSpecs);
            }
        } else {
            // 没有selectedSku.sku_id，使用默认SKU
            useDefaultSku(newSelectedSpecs);
        }

        // 更新响应式状态
        selectedSpecs.value = newSelectedSpecs;
    } catch (error) {
        console.error('初始化规格选择失败:', error);
        selectedSpecs.value = newSelectedSpecs;
    }
};

// 提取使用默认SKU的逻辑为单独函数
const useDefaultSku = (targetObj: Record<string, string>) => {
    if (props.goodsDetail.defaultSku && props.goodsDetail.defaultSku.sku_spec_format && props.goodsDetail.goodsSpec) {
        const defaultSpecs = props.goodsDetail.defaultSku.sku_spec_format.split(',');
        const specNames = props.goodsDetail.goodsSpec.map((spec: any) => spec.spec_name);

        for (let i = 0; i < Math.min(specNames.length, defaultSpecs.length); i++) {
            if (specNames[i] && defaultSpecs[i]) {
                targetObj[specNames[i]] = defaultSpecs[i];
            }
        }

        console.log('使用默认SKU初始化规格:', targetObj);
    }
};

// 添加对goodsDetail的监听，确保数据加载后能正确初始化
watch(() => props.goodsDetail.id, (newVal) => {
    if (newVal) {
        // 只有商品ID变化时才重新初始化，避免不必要的更新
        initSelectedSpecs();
    }
}, { immediate: true });

// 暴露方法给父组件
defineExpose({
    open,
    close
});
</script>

<style lang="scss" scoped>
.sku-popup {
    background-color: #fff;
    border-radius: 24rpx 24rpx 0 0;
    min-height: 25vh;

    .goods-info {
        padding: 30rpx;
        display: flex;
        position: relative;

        .goods-image {
            width: 180rpx;
            height: 180rpx;
            border-radius: 12rpx;
        }

        .info-right {
            flex: 1;
            margin-left: 20rpx;

            .promotion-tags {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 8rpx;

                .promotion-tag {
                    height: 32rpx;
                    line-height: 32rpx;
                    padding: 0 8rpx;
                    background-color: #FFF0ED;
                    border: 1rpx solid #FF6034;
                    color: #FF6034;
                    font-size: 18rpx;
                    border-radius: 4rpx;
                    margin-right: 8rpx;
                    margin-bottom: 6rpx;
                }
            }

            .price-container {
                margin-bottom: 8rpx;

                .price {
                    color: #FF4444;
                    font-size: 36rpx;
                    font-weight: bold;

                    .promotion-price {
                        color: #FF4444;
                        font-size: 36rpx;
                        font-weight: bold;
                    }

                    .original-price {
                        font-size: 24rpx;
                        color: #999;
                        text-decoration: line-through;
                        font-weight: normal;
                        margin-left: 10rpx;
                    }
                }
            }

            .stock,
            .selected {
                font-size: 24rpx;
                color: #666;
                margin-top: 10rpx;
            }
        }

        .close-btn {
            position: absolute;
            right: 20rpx;
            top: 20rpx;
            padding: 10rpx;
        }
    }

    .sku-list {
        max-height: 35vh;
        padding: 0 30rpx;

        .sku-item {
            margin-bottom: 30rpx;

            .title {
                font-size: 28rpx;
                color: #333;
                margin-bottom: 20rpx;
            }

            .options {
                display: flex;
                flex-wrap: wrap;
                gap: 20rpx;

                .option-item {
                    padding: 12rpx 30rpx;
                    border-radius: 100rpx;
                    border: 2rpx solid #EEEEEE;
                    font-size: 24rpx;
                    color: #333;

                    &.active {
                        background: #FFF0ED;
                        border-color: #FF4444;
                        color: #FF4444;
                    }

                    &.disabled {
                        color: #999;
                        background: #F5F5F5;
                        border-color: #F5F5F5;
                    }
                }
            }
        }

        .quantity-section {
            display: flex;
            align-items: center;
            margin-bottom: 30rpx;
            padding: 20rpx 0;

            .title {
                font-size: 28rpx;
                color: #333;
                margin-right: 40rpx;
            }

            .number-box {
                display: flex;
                align-items: center;
                border: 2rpx solid #EEEEEE;
                border-radius: 4rpx;

                .minus,
                .plus {
                    width: 60rpx;
                    height: 60rpx;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 36rpx;
                    color: #333;
                    background: #f5f5f5;
                }

                .input {
                    width: 80rpx;
                    height: 60rpx;
                    text-align: center;
                    font-size: 28rpx;
                    border-left: 2rpx solid #EEEEEE;
                    border-right: 2rpx solid #EEEEEE;
                }
            }
        }
    }

    .bottom-btn {
        padding: 30rpx 30rpx;
        border-top: 2rpx solid #F5F5F5;
        display: flex;
        gap: 20rpx;

        .add-cart-btn,
        .buy-now-btn {
            height: 80rpx;
            line-height: 80rpx;
            background: #FF6034;
            color: #fff;
            font-size: 28rpx;
            border-radius: 40rpx;
            margin-left: 0;
            flex: 1;
            transition: background 0.3s, transform 0.2s;

            &:disabled {
                opacity: 0.6;
            }
        }

        .buy-now-btn {
            background: #FF4444;
        }
    }
}
</style>