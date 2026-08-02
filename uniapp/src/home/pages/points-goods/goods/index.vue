<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <template #top>
                <!-- 搜索区域 -->
                <view class="search-box">
                    <uni-search-bar v-model="params.goods_name" placeholder="搜索积分商品" radius="100" cancelButton="auto"
                        @confirm="handleSearch" @cancel="handleCancel" @clear="handleClear" />
                </view>

                <!-- 排序和布局切换 -->
                <view class="filter-bar">
                    <view v-for="(item, index) in sortOptions" :key="index" class="sort-item"
                        :class="{ active: params.sort_field === item.field }" @click="changeSort(item.field, item.type)">
                        <text>{{ item.name }}</text>
                        <view class="sort-icon" v-if="item.hasIcon">
                            <uni-icons :type="getSortIcon(item.field)" size="14"
                                :color="params.sort_field === item.field ? '#E1251B' : '#999'"></uni-icons>
                        </view>
                    </view>

                    <!-- 布局切换 -->
                    <view class="layout-switch">
                        <view class="layout-btn" :class="{ active: layoutType === 'grid' }"
                            @click="changeLayoutType('grid')">
                            <uni-icons type="bars" size="20"
                                :color="layoutType === 'grid' ? '#E1251B' : '#999'"></uni-icons>
                        </view>
                        <view class="layout-btn" :class="{ active: layoutType === 'list' }"
                            @click="changeLayoutType('list')">
                            <uni-icons type="list" size="20"
                                :color="layoutType === 'list' ? '#E1251B' : '#999'"></uni-icons>
                        </view>
                    </view>
                </view>
            </template>

            <!-- 商品列表 - 网格布局 -->
            <view class="goods-list grid-layout" v-if="layoutType === 'grid' && dataList.length > 0">
                <view v-for="item in dataList" :key="item.id" class="goods-item" @click="goPointsGoodsDetailPage(item)">
                    <image class="goods-img" :src="formatImageUrl(item.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ item.goods_name }}</view>
                        <view class="goods-price">
                            <view class="price-container">
                                <text class="points-symbol">{{ item.points_price }}</text>
                                <text class="points-text">积分</text>
                            </view>
                            <view class="sale-count">
                                <text>{{ item.exchange_num || 0 }} 人已兑换</text>
                            </view>
                        </view>
                        <view class="market-price" v-if="item.market_price && Number(item.market_price) > 0">
                            <text>市场价：¥{{ item.market_price }}</text>
                        </view>
                    </view>
                </view>
            </view>

            <!-- 商品列表 - 列表布局 -->
            <view class="goods-list list-layout" v-else-if="layoutType === 'list' && dataList.length > 0">
                <view v-for="item in dataList" :key="item.id" class="goods-item" @click="goPointsGoodsDetailPage(item)">
                    <image class="goods-img" :src="formatImageUrl(item.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ item.goods_name }}</view>
                        <view class="goods-desc" v-if="item.goods_advword">{{ item.goods_advword }}</view>
                        <view class="goods-bottom">
                            <view class="goods-price">
                                <text class="points-symbol">{{ item.points_price }}</text>
                                <text class="points-text">积分</text>
                                <text v-if="item.market_price && Number(item.market_price) > 0"
                                    class="market-price">¥{{ item.market_price }}</text>
                            </view>
                            <view class="sale-count">
                                <text>{{ item.exchange_num || 0 }} 人已兑换</text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script lang="ts" setup>
import { getPointsGoodsPages } from '@/home/api/points-goods/pointsGoods'
import { ref } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// 查询参数
const params = ref({
    goods_name: '',
    sort_field: '',
    sort_type: '',
    goods_status: 1, // 只显示上架商品
})

// 布局类型：grid(网格) 或 list(列表)
const layoutType = ref('grid')

// 排序选项
const sortOptions = [
    { name: '综合', field: '', type: '', hasIcon: false },
    { name: '积分', field: 'points_price', type: 'asc', hasIcon: true },
    { name: '兑换量', field: 'exchange_num', type: 'desc', hasIcon: false },
    { name: '库存', field: 'stock_num', type: 'desc', hasIcon: false }
]

// 分页相关
const paging = ref<any>(null)
const dataList = ref<any[]>([])

// 获取排序图标
const getSortIcon = (field: string) => {
    if (field !== params.value.sort_field) return 'sort';
    return params.value.sort_type === 'asc' ? 'arrow-up' : 'arrow-down';
}

// 切换排序
const changeSort = (field: string, defaultType: string) => {
    // 如果点击当前已选中的排序字段，则切换排序方向
    if (params.value.sort_field === field && field !== '') {
        params.value.sort_type = params.value.sort_type === 'asc' ? 'desc' : 'asc';
    } else {
        // 否则，切换为新的排序字段和默认排序方向
        params.value.sort_field = field;
        params.value.sort_type = defaultType;
    }

    // 重新加载数据
    if (paging.value) {
        paging.value.reload();
    }
}

// 搜索商品
const handleSearch = () => {
    if (paging.value) {
        paging.value.reload();
    }
}

// 取消搜索
const handleCancel = () => {
    params.value.goods_name = '';
    if (paging.value) {
        paging.value.reload();
    }
}

// 清除搜索内容
const handleClear = () => {
    params.value.goods_name = '';
    if (paging.value) {
        paging.value.reload();
    }
}

// 查询商品列表
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getPointsGoodsPages({
            page_current,
            page_size,
            ...params.value
        })
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('加载积分商品列表失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '加载积分商品列表失败',
            icon: 'none'
        });
    }
}

// 切换布局类型
const changeLayoutType = (type: string) => {
    layoutType.value = type;
    console.log('Changing layout to:', layoutType.value);
}

// 跳转到积分商品详情页
const goPointsGoodsDetailPage = (item: any) => {
    uni.navigateTo({
        url: `/home/pages/points-goods/goods/detail/index?id=${item.id}`
    });
}
</script>

<style lang="scss" scoped>
.search-box {
    padding: 20rpx;
    background-color: $ds-bg-white;
    border-bottom: 1rpx solid $ds-border-light;
}

.filter-bar {
    display: flex;
    align-items: center;
    height: 88rpx;
    padding: 0 20rpx;
    background-color: $ds-bg-white;
    border-bottom: 1rpx solid $ds-border-light;

    .sort-item {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: $ds-font-md;
        color: $ds-text-secondary;
        height: 100%;
        position: relative;
        transition: color 0.2s ease;

        .sort-icon {
            margin-left: 6rpx;
        }

        &.active {
            color: $ds-primary-color;
            font-weight: 500;
        }

        &:active {
            opacity: 0.7;
        }
    }

    .layout-switch {
        display: flex;
        align-items: center;

        .layout-btn {
            padding: 12rpx;
            transition: opacity 0.2s ease;

            &.active {
                color: $ds-primary-color;
            }

            &:active {
                opacity: 0.7;
            }
        }
    }
}

// 网格布局 - 积分商品风格
.goods-list.grid-layout {
    display: flex;
    flex-wrap: wrap;
    padding: 12rpx;
    background-color: $ds-bg-gray;

    .goods-item {
        width: calc(50% - 12rpx);
        margin: 6rpx;
        background-color: $ds-bg-white;
        border-radius: 8rpx;
        overflow: hidden;
        transition: transform 0.2s ease;

        &:active {
            transform: scale(0.98);
        }

        .goods-img {
            width: 100%;
            aspect-ratio: 1/1;
            height: auto;
            background-color: $ds-bg-gray;
            display: block;
        }

        .goods-info {
            padding: 16rpx;

            .goods-name {
                font-size: $ds-font-md;
                color: $ds-text-primary;
                line-height: 1.4;
                height: 80rpx;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                line-clamp: 2;
                -webkit-box-orient: vertical;
                margin-bottom: 16rpx;
            }

            .goods-price {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 8rpx;

                .price-container {
                    display: flex;
                    align-items: baseline;

                    .points-symbol {
                        font-size: $ds-font-lg;
                        color: $ds-primary-color;
                        font-weight: 600;
                    }

                    .points-text {
                        font-size: 22rpx;
                        color: $ds-primary-color;
                        font-weight: 500;
                        margin-left: 4rpx;
                    }
                }

                .sale-count {
                    font-size: 22rpx;
                    color: $ds-text-light;

                    text {
                        margin-left: 0;
                    }
                }
            }

            .market-price {
                font-size: 22rpx;
                color: $ds-text-light;
                text-decoration: line-through;
            }
        }
    }
}

// 列表布局 - 积分商品风格
.goods-list.list-layout {
    padding: 0;
    background-color: $ds-bg-gray;

    .goods-item {
        display: flex;
        padding: 20rpx;
        margin-bottom: 12rpx;
        background-color: $ds-bg-white;
        transition: opacity 0.2s ease;

        &:active {
            opacity: 0.8;
        }

        .goods-img {
            width: 180rpx;
            height: 180rpx;
            border-radius: 8rpx;
            background-color: $ds-bg-gray;
            flex-shrink: 0;
        }

        .goods-info {
            flex: 1;
            margin-left: 20rpx;
            display: flex;
            flex-direction: column;
            justify-content: space-between;

            .goods-name {
                font-size: $ds-font-lg;
                color: $ds-text-primary;
                line-height: 1.4;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                line-clamp: 2;
                -webkit-box-orient: vertical;
                margin-bottom: 12rpx;
            }

            .goods-desc {
                font-size: $ds-font-sm;
                color: $ds-text-light;
                line-height: 1.3;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                line-clamp: 2;
                -webkit-box-orient: vertical;
                margin-bottom: 12rpx;
            }

            .goods-bottom {
                display: flex;
                align-items: center;
                justify-content: space-between;

                .goods-price {
                    display: flex;
                    align-items: baseline;

                    .points-symbol {
                        font-size: $ds-font-xl;
                        color: $ds-primary-color;
                        font-weight: 600;
                    }

                    .points-text {
                        font-size: $ds-font-sm;
                        color: $ds-primary-color;
                        font-weight: 500;
                        margin-left: 4rpx;
                    }

                    .market-price {
                        font-size: $ds-font-sm;
                        color: $ds-text-light;
                        text-decoration: line-through;
                        margin-left: 12rpx;
                    }
                }

                .sale-count {
                    font-size: 22rpx;
                    color: $ds-text-light;

                    text {
                        margin-left: 0;
                    }
                }
            }
        }
    }
}
</style>
