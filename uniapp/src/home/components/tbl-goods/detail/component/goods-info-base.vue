<template>
    <view class="info-container">
        <!-- 商品名称和促销语 -->
        <view class="goods-header">
            <view class="goods-name">{{ goodsDetail.goods_name }}</view>
            <view class="goods-advword" v-if="goodsDetail.goods_advword">{{ goodsDetail.goods_advword }}</view>
        </view>
        
        <!-- 促销标签 -->
        <view class="promotion-tags" v-if="hasPromotions">
            <view class="promotion-tag" v-if="hasFlashsale">
                <text>{{ currentSku?.promotions?.flashsale.promotion_type_desc }}</text>
            </view>
            <view class="promotion-tag" v-if="hasWholesale">
                <text>{{ currentSku?.promotions?.wholesale.promotion_type_desc }}</text>
            </view>
            <view class="promotion-tag" v-if="hasUserDiscount">
                <text>{{ currentSku?.promotions?.userdiscount.promotion_type_desc }}</text>
            </view>
        </view>
        
        <!-- 价格和销量区域 -->
        <view class="price-info">
            <view class="price-row">
                <!-- 促销价格显示 -->
                <view class="current-price">
                    <text class="symbol">¥</text>
                    <text class="value">{{ hasPromotion ? currentSku.promotion_price : selectedSku.sku_price }}</text>
                </view>
                <view class="market-price" v-if="hasPromotion || (selectedSku.market_price && selectedSku.market_price !== selectedSku.sku_price)">
                    <text v-if="hasPromotion">¥{{ selectedSku.sku_price }}</text>
                    <text v-else>¥{{ selectedSku.market_price }}</text>
                </view>
                <view class="discount-tag" v-if="hasDiscount">
                    <text v-if="currentSku?.discount_rate">{{ currentSku.discount_rate }}折</text>
                    <text v-else>{{ discountPercent }}折</text>
                </view>
                
                <!-- 添加收藏按钮 -->
                <view class="favorite-btn" @click="handleFavorite">
                    <uni-icons :type="isFavorite ? 'star-filled' : 'star'" size="20" :color="isFavorite ? '#FF6034' : '#999'"></uni-icons>
                    <text :class="{ active: isFavorite }">{{ isFavorite ? '已收藏' : '收藏' }}</text>
                </view>
            </view>
            
            <view class="sales-info">
                <view class="stock">库存: {{ selectedSku.sku_stock }}件</view>
                <view class="sales">销量: {{ totalSales }}</view>
            </view>
        </view>
        
        <!-- 促销信息详情 -->
        <view class="promotion-info" v-if="hasPromotions">
            <!-- 限时折扣信息 -->
            <view class="promotion-item" v-if="hasFlashsale">
                <view class="promotion-label">
                    <text class="label-tag">{{ currentSku?.promotions?.flashsale.promotion_type_desc }}</text>
                    <text class="label-text">限时优惠</text>
                </view>
                <view class="promotion-value">
                    <text>¥{{ currentSku?.promotions?.flashsale.flashsale_price }}</text>
                </view>
            </view>
            
            <!-- 批发价格信息 -->
            <view class="promotion-item" v-if="hasWholesale">
                <view class="promotion-label">
                    <text class="label-tag">{{ currentSku?.promotions?.wholesale.promotion_type_desc }}</text>
                    <text class="label-text">批发价格</text>
                </view>
                <view class="promotion-value">
                    <view v-for="(rule, index) in currentSku?.promotions?.wholesale.rules" :key="index" class="wholesale-rule">
                        <text>{{ rule.quantity_min }}件起 ¥{{ rule.wholesale_price }}/件</text>
                    </view>
                </view>
            </view>
            
            <!-- 会员折扣信息 -->
            <view class="promotion-item" v-if="hasUserDiscount">
                <view class="promotion-label">
                    <text class="label-tag">{{ currentSku?.promotions?.userdiscount.promotion_type_desc }}</text>
                    <text class="label-text">会员专享</text>
                </view>
                <view class="promotion-value">
                    <view v-for="(level, index) in currentSku?.promotions?.userdiscount.levels" :key="index" class="member-level">
                        <text>{{ level.user_level_name }} ¥{{ level.user_level_price }}</text>
                    </view>
                </view>
            </view>
        </view>
        
        <!-- 商品标签和信息 -->
        <view class="goods-tags" v-if="hasTags">
            <view class="tag" v-if="goodsDetail.goods_status === 1">
                <uni-icons type="checkbox-filled" size="14" color="#FF6034"></uni-icons>
                <text>正品保障</text>
            </view>
            <view class="tag">
                <uni-icons type="checkbox-filled" size="14" color="#FF6034"></uni-icons>
                <text>急速发货</text>
            </view>
            <view class="tag">
                <uni-icons type="checkbox-filled" size="14" color="#FF6034"></uni-icons>
                <text>7天无理由退换</text>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { createGoodsFavorite, deleteGoodsFavorite, getGoodsFavoriteStatus } from '@/home/api/user/UserTblGoodsFavorites';
import { computed, ref, watch } from 'vue';
import useUserStore from '@/stores/user';
import { storeToRefs } from 'pinia';

const userStore = useUserStore();
const { isLogin } = storeToRefs(userStore);

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

// 收藏状态
const isFavorite = ref(false);

// 获取当前选中的SKU完整信息
const currentSku = computed(() => {
    if (!props.selectedSku?.sku_id || !props.goodsDetail?.skuList) return null;
    
    return props.goodsDetail.skuList.find((sku: any) => sku.id === props.selectedSku.sku_id);
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

// 是否有折扣显示
const hasDiscount = computed(() => {
    return (currentSku.value?.discount_rate > 0) || discountPercent.value > 0;
});

// 计算总销量（实际销量+虚拟销量）
const totalSales = computed(() => {
    return (Number(props.goodsDetail.sales_num) || 0) + (Number(props.goodsDetail.virtual_sales_num) || 0);
});

// 计算折扣百分比
const discountPercent = computed(() => {
    if (!props.selectedSku.market_price || Number(props.selectedSku.market_price) <= 0 || 
        !props.selectedSku.sku_price || Number(props.selectedSku.sku_price) <= 0) {
        return 0;
    }
    
    const marketPrice = Number(props.selectedSku.market_price);
    const skuPrice = Number(props.selectedSku.sku_price);
    
    if (marketPrice <= skuPrice) return 0;
    
    // 计算折扣并保留1位小数
    const discount = Math.floor((skuPrice / marketPrice) * 10 * 10) / 10;
    return discount;
});

// 判断是否有标签展示
const hasTags = computed(() => {
    return true; // 这里可以根据实际情况来判断
});

// 获取收藏状态
const fetchFavoriteStatus = async () => {
    if (!isLogin.value) return;
    
    try {
        const goodsId = props.goodsDetail.id;
        if (!goodsId) return;
        
        const res = await getGoodsFavoriteStatus(goodsId);
        isFavorite.value = res.data.user_id ? true : false;
    } catch (error) {
        console.error('获取收藏状态失败:', error);
    }
};

// 收藏/取消收藏商品
const handleFavorite = async () => {

    if (!isLogin.value) {
        uni.showToast({
            title: '请先登录',
            icon: 'none'
        });
        return;
    }
    
    try {
        const goodsId = props.goodsDetail.id;
        if (!goodsId) return;
        
        if (isFavorite.value) {
            // 取消收藏
            const res = await deleteGoodsFavorite(goodsId);
            if (res.code === 10000) {
                isFavorite.value = false;
                uni.showToast({
                    title: '已取消收藏',
                    icon: 'none'
                });
            } else {
                uni.showToast({
                    title: res.message || '取消收藏失败',
                    icon: 'none'
                });
            }
        } else {
            // 添加收藏
            const res = await createGoodsFavorite(goodsId);
            if (res.code === 10000) {
                isFavorite.value = true;
                uni.showToast({
                    title: '收藏成功',
                    icon: 'none'
                });
            } else {
                uni.showToast({
                    title: res.message || '收藏失败',
                    icon: 'none'
                });
            }
        }
    } catch (error) {
        console.error('操作失败:', error);
        uni.showToast({
            title: '网络异常，请稍后重试',
            icon: 'none'
        });
    }
};

// 添加对goodsDetail的监听，确保数据加载后能正确初始化
watch(() => props.goodsDetail.id, (newVal) => {
    if (newVal) {
        // 只有商品ID变化时才重新初始化，避免不必要的更新
        fetchFavoriteStatus();
    }
}, { immediate: true });


</script>

<style lang="scss" scoped>
.info-container {
    margin: 20rpx;
    padding: 30rpx;
    background-color: #fff;
    border-radius: 16rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

/* 商品标题区域 */
.goods-header {
    margin-bottom: 24rpx;
    
    .goods-name {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
        line-height: 1.4;
        margin-bottom: 12rpx;
    }
    
    .goods-advword {
        font-size: 26rpx;
        color: #666;
        line-height: 1.4;
    }
}

/* 促销标签区域 */
.promotion-tags {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 20rpx;
    
    .promotion-tag {
        height: 36rpx;
        line-height: 36rpx;
        padding: 0 12rpx;
        background-color: #FFF0ED;
        border: 1rpx solid #FF6034;
        color: #FF6034;
        font-size: 20rpx;
        border-radius: 4rpx;
        margin-right: 12rpx;
        margin-bottom: 10rpx;
    }
}

/* 价格区域 */
.price-info {
    margin-bottom: 24rpx;
    
    .price-row {
        display: flex;
        align-items: center;
        margin-bottom: 16rpx;
        
        .current-price {
            display: flex;
            align-items: baseline;
            color: #FF6034;
            
            .symbol {
                font-size: 28rpx;
                font-weight: normal;
                margin-right: 4rpx;
            }
            
            .value {
                font-size: 44rpx;
                font-weight: bold;
                line-height: 1;
            }
        }
        
        .market-price {
            margin-left: 16rpx;
            font-size: 24rpx;
            color: #999;
            text-decoration: line-through;
            align-self: center;
        }
        
        .discount-tag {
            height: 34rpx;
            line-height: 34rpx;
            padding: 0 12rpx;
            background: linear-gradient(to right, #FF6034, #FF8C19);
            color: #fff;
            font-size: 20rpx;
            border-radius: 4rpx;
            margin-left: 16rpx;
            align-self: center;
        }
        
        /* 收藏按钮样式 */
        .favorite-btn {
            margin-left: auto;
            display: flex;
            align-items: center;
            padding: 6rpx 16rpx;
            border-radius: 30rpx;
            background-color: #f8f8f8;
            
            text {
                font-size: 24rpx;
                color: #999;
                margin-left: 6rpx;
                
                &.active {
                    color: #FF6034;
                }
            }
        }
    }
    
    .sales-info {
        display: flex;
        justify-content: space-between;
        font-size: 24rpx;
        color: #999;
        
        .stock {
            background-color: #f6f7f9;
            padding: 6rpx 16rpx;
            border-radius: 20rpx;
        }
        
        .sales {
            padding: 6rpx 16rpx;
        }
    }
}

/* 促销信息详情 */
.promotion-info {
    margin-bottom: 20rpx;
    padding: 16rpx;
    background-color: #FFFAF8;
    border-radius: 12rpx;
    
    .promotion-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12rpx 0;
        border-bottom: 1rpx dashed #FFE4DC;
        
        &:last-child {
            border-bottom: none;
        }
        
        .promotion-label {
            display: flex;
            align-items: center;
            
            .label-tag {
                height: 32rpx;
                line-height: 32rpx;
                padding: 0 10rpx;
                background-color: #FF6034;
                color: #fff;
                font-size: 20rpx;
                border-radius: 4rpx;
                margin-right: 12rpx;
            }
            
            .label-text {
                font-size: 26rpx;
                color: #333;
            }
        }
        
        .promotion-value {
            font-size: 26rpx;
            color: #FF6034;
            font-weight: 500;
            text-align: right;
            
            .wholesale-rule, .member-level {
                margin-bottom: 8rpx;
                
                &:last-child {
                    margin-bottom: 0;
                }
            }
        }
    }
}

/* 商品标签 */
.goods-tags {
    display: flex;
    flex-wrap: wrap;
    border-top: 1rpx dashed #efefef;
    padding-top: 20rpx;
    
    .tag {
        display: flex;
        align-items: center;
        margin-right: 24rpx;
        margin-bottom: 10rpx;
        font-size: 22rpx;
        color: #666;
        
        uni-icons {
            margin-right: 4rpx;
        }
    }
}
</style>