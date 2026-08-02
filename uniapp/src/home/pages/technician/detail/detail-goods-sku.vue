<template>
    <view class="goods-sku-container">
        <view class="section-title">
            <text>全部服务</text>
        </view>

        <view class="goods-list" v-if="technicianDetail?.goodsSkuList?.length">
            <view class="goods-item" v-for="(item, index) in technicianDetail.goodsSkuList" :key="index"
                @click="navigateToGoodsDetail(item.goods_id)">
                <view class="goods-left">
                    <image class="goods-image" v-if="item.sku_image" :src="formatImageUrl(item.sku_image, ThumbnailPresets.medium, 'goods')"
                        mode="aspectFill"></image>
                    <image class="goods-image" v-else :src="formatImageUrl(item.goods.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill">
                    </image>
                </view>
                <view class="goods-center">
                    <view class="goods-name">{{ item.goods.goods_name }}</view>
                    <view class="goods-sku" v-if="item.sku_name">{{ item.sku_name }}</view>
                    <view class="goods-bottom">
                        <view class="price-container">
                            <text class="price">¥{{ item.sku_price }}</text>
                            <text class="market-price" v-if="item.market_price > 0">¥{{ item.market_price }}</text>
                        </view>
                        <view class="book-btn" @click="handleBuyNow(item)">去预约</view>
                    </view>
                </view>
            </view>
        </view>

        <view v-else>
            <DsEmpty text="暂无推荐商品" />
        </view>
    </view>
</template>

<script setup lang="ts">
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import DsEmpty from '@/components/ds-empty/index.vue';
import { addTblCart } from '@/home/api/tbl-cart/tblCart'


const props = defineProps({
    technicianDetail: {
        type: Object,
        default: () => ({})
    }
});



// 跳转到商品详情页
const navigateToGoodsDetail = (goodsId: number) => {
    uni.navigateTo({
        url: `/home/pages/goods/detail?id=${goodsId}`
    });
};


// 立即购买
const handleBuyNow = async (item: any) => {

    const params = {
        goods_id: item.goods_id,
        sku_id: item.id,
        quantity: 1,
        promotion_platform: item.promotion_platform || '',
        promotion_type: item.promotion_type || '',
        promotion_related_id: item.promotion_related_id || 0
    };

    const res = await addTblCart(params, 'house');

    if (res.code === 10000) {
        // 跳转至订单结算页面
        uni.navigateTo({
            url: '/home/platform/house/pages/order/checkout?cart_ids=' + res.data + '&technician_id=' + props.technicianDetail.id
        });

    } else {
        uni.showToast({
            title: res.message,
            icon: 'none'
        });
    }

};


</script>

<style lang="scss" scoped>
.goods-sku-container {
    background-color: #fff;
    padding: 30rpx;
    margin: 0 20rpx 20rpx 20rpx;
    border-radius: 20rpx;
    overflow: hidden;

    .section-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 20rpx;
        position: relative;
        padding-left: 20rpx;

        &::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6rpx;
            height: 30rpx;
            background-color: #FF6034;
            border-radius: 3rpx;
        }
    }

    .goods-list {
        .goods-item {
            display: flex;
            padding: 20rpx 0;
            border-bottom: 1px solid #f0f0f0;

            &:last-child {
                border-bottom: none;
            }

            .goods-left {
                width: 160rpx;
                height: 160rpx;
                margin-right: 20rpx;
                flex-shrink: 0;

                .goods-image {
                    width: 100%;
                    height: 100%;
                    border-radius: 12rpx;
                    background-color: #f5f5f5;
                }
            }

            .goods-center {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                overflow: hidden;
                padding: 10rpx 0;

                .goods-name {
                    font-size: 28rpx;
                    color: #333;
                    font-weight: bold;
                    line-height: 1.4;
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 2;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    margin-bottom: 10rpx;
                }

                .goods-sku {
                    font-size: 24rpx;
                    color: #666;
                    background-color: #f8f8f8;
                    padding: 4rpx 12rpx;
                    border-radius: 4rpx;
                    display: inline-block;
                    margin-bottom: 16rpx;
                }

                .goods-bottom {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;

                    .price-container {
                        display: flex;
                        align-items: center;

                        .price {
                            font-size: 32rpx;
                            color: #ff4d4f;
                            font-weight: bold;
                        }

                        .market-price {
                            font-size: 24rpx;
                            color: #999;
                            text-decoration: line-through;
                            margin-left: 10rpx;
                        }
                    }

                    .book-btn {
                        background-color: #4cd964;
                        color: #fff;
                        font-size: 26rpx;
                        padding: 10rpx 30rpx;
                        border-radius: 30rpx;
                    }
                }
            }
        }
    }

}
</style>
