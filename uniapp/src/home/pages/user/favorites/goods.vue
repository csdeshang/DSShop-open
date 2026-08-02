<template>
    <view class="goods-favorites">
        <z-paging ref="paging" v-model="goodsList" @query="queryList">

            
            <view class="goods-list">
                <view class="goods-item" v-for="item in goodsList" :key="item.id" @click="goGoodsDetailPage(item.goods)">
                    <image :src="formatImageUrl(item.goods?.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" class="goods-image"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ item.goods?.goods_name || '未知商品' }}</view>

                        <view class="goods-actions">
                            <button class="action-btn" @click.stop="cancelFavorite(item.goods_id)">取消收藏</button>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { deleteGoodsFavorite, getGoodsFavoritePages } from '@/home/api/user/UserTblGoodsFavorites';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';

import { goGoodsDetailPage } from '@/utils/platform';

// 定义商品类型，根据实际数据结构更新
interface GoodsInfo {
    id: number;
    goods_name: string;
    cover_image: string;
    goods_minprice: string;
    collect_num: number;
}

interface GoodsItem {
    id: number;
    user_id: number;
    goods_id: number;
    create_at: string;
    goods: GoodsInfo;
}

const paging = ref<any>(null);
const goodsList = ref<GoodsItem[]>([]);

// 保持原有的queryList函数
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getGoodsFavoritePages({page_current,page_size,});
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('获取收藏商品失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '获取数据失败，请稍后重试',
            icon: 'none'
        });
    }
};

// 取消收藏
const cancelFavorite = async (goodsId: number) => {
    try {
        const res = await deleteGoodsFavorite(goodsId);
        if (res.code === 10000) {
            uni.showToast({
                title: '已取消收藏',
                icon: 'success'
            });
            // 通过刷新列表来更新数据
            if (paging.value) {
                paging.value.reload();
            }
        } else {
            uni.showToast({
                title: res.message || '操作失败',
                icon: 'none'
            });
        }
    } catch (error) {
        console.error('取消收藏失败:', error);
        uni.showToast({
            title: '网络异常，请稍后重试',
            icon: 'none'
        });
    }
};




// 用于解决默认导出问题
defineOptions({
    name: 'GoodsFavorites'
});
</script>

<style lang="scss" scoped>
.goods-favorites {
    min-height: calc(100vh - 80rpx);
    background-color: #f8f8f8;
}


.goods-list {
    padding: 20rpx;

    .goods-item {
        display: flex;
        background-color: #fff;
        border-radius: 12rpx;
        margin-bottom: 20rpx;
        padding: 20rpx;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);

        .goods-image {
            width: 180rpx;
            height: 180rpx;
            border-radius: 8rpx;
            margin-right: 20rpx;
            flex-shrink: 0;
        }

        .goods-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;

            .goods-name {
                font-size: 28rpx;
                color: #333;
                line-height: 1.4;
                margin-bottom: 10rpx;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
            }

            .goods-price {
                font-size: 32rpx;
                color: #ff4141;
                font-weight: bold;
                margin-bottom: 20rpx;
            }

            .goods-actions {
                display: flex;
                justify-content: flex-end;

                .action-btn {
                    min-width: 140rpx;
                    font-size: 24rpx;
                    color: #666;
                    background-color: #f5f5f5;
                    border-radius: 30rpx;
                    padding: 0 20rpx;
                    height: 50rpx;
                    line-height: 50rpx;
                    text-align: center;
                }
            }
        }
    }
}
</style>
