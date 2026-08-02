<template>

    <view>

        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <template #top>
                <!-- 搜索区域 -->
                <view class="search-box">
                    <uni-search-bar v-model="params.goods_name" placeholder="搜索商品" radius="100" cancelButton="auto"
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

                    <!-- 布局切换：只显示一个图标，点击切换到另一布局 -->
                    <view class="layout-switch" @click="changeLayoutType(layoutType === 'grid' ? 'list' : 'grid')">
                        <image class="layout-icon" v-if="layoutType === 'grid'"
                            src="/static/images/icons/list.png" mode="aspectFit" />
                        <image class="layout-icon" v-else
                            src="/static/images/icons/grid.png" mode="aspectFit" />
                    </view>

                    <view class="sort-item filter-trigger" @click="openFilterPopup">
                        <text>筛选</text>
                    </view>
                </view>
            </template>

            <!-- 商品列表 - 网格布局 -->
            <view class="goods-list grid-layout" v-if="layoutType === 'grid' && dataList.length > 0">
                <view v-for="item in dataList" :key="item.id" class="goods-item" @click="goGoodsDetailPage(item)">
                    <image class="goods-img" :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ item.goods_name }}</view>
                        <view class="goods-price">
                            <view class="price-container">
                                <text class="price-symbol">¥</text>
                                <text class="price-value">{{ getPrice(item) }}</text>
                            </view>
                            <view class="sale-count">
                                <text>{{ getTotalSales(item) }} 人已下单</text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>

            <!-- 商品列表 - 列表布局 -->
            <view class="goods-list list-layout" v-else-if="layoutType === 'list' && dataList.length > 0">
                <view v-for="item in dataList" :key="item.id" class="goods-item" @click="goGoodsDetailPage(item)">
                    <image class="goods-img" :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill"></image>
                    <view class="goods-info">
                        <view class="goods-name">{{ item.goods_name }}</view>
                        <view class="goods-sku" v-if="item.goodsSku && item.goodsSku.sku_name">
                            <uni-icons type="tags" size="14" color="#999"></uni-icons>
                            <text>{{ item.goodsSku.sku_name }}</text>
                        </view>
                        <view class="goods-bottom">
                            <view class="goods-price">
                                <text class="price-symbol">¥</text>
                                <text class="price-value">{{ getPrice(item) }}</text>
                                <text
                                    v-if="item.goodsSku && item.goodsSku.market_price && Number(item.goodsSku.market_price) > 0"
                                    class="market-price">¥{{ item.goodsSku.market_price }}</text>
                            </view>
                            <view class="sale-count">
                                <text>{{ getTotalSales(item) }} 人已下单</text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>

        </z-paging>

        <TblGoodsFilterPopup ref="filterPopupRef" :params="params" @confirm="handleFilterConfirm" />
    </view>
</template>

<script lang="ts" setup>
import { getTblGoodsPages } from '@/home/api/tbl-goods/tblGoods'
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { goGoodsDetailPage } from '@/utils/platform'
import TblGoodsFilterPopup from './filter-popup.vue'

const props = defineProps({
    platform: {
        type: String,
        default: 'mall'
    },
})

// 查询参数
const params = ref({
    platform: props.platform,
    goods_name: '',
    sort_field: '',
    sort_type: '',
    category_id: '',
    is_flashsale_goods: '' as '' | '0' | '1',
    is_wholesale_goods: '' as '' | '0' | '1',
    is_userdiscount_goods: '' as '' | '0' | '1',
    is_distributor_goods: '' as '' | '0' | '1',
    min_price: '' as string,
    max_price: '' as string,
    min_goods_avg_score: '' as string,
    max_goods_avg_score: '' as string,
})

// 布局类型：grid(网格) 或 list(列表)
const layoutType = ref('grid')

// 排序选项
const sortOptions = [
    { name: '综合', field: '', type: '', hasIcon: false },
    { name: '销量', field: 'sales_num', type: 'desc', hasIcon: true },
    { name: '价格', field: 'goods_minprice', type: 'asc', hasIcon: true },
    { name: '评分', field: 'avg_goods_score', type: 'desc', hasIcon: true },
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

// 获取商品价格
const getPrice = (item: any): string => {
    if (item.goodsSku && item.goodsSku.sku_price) {
        return item.goodsSku.sku_price;
    }
    return item.goods_minprice || '0.00';
}

// 获取商品总销量
const getTotalSales = (item: any): number => {
    return Number(item.sales_num || 0) + Number(item.virtual_sales_num || 0);
}



// 查询商品列表
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getTblGoodsPages({
            page_current,
            page_size,
            ...params.value
        })
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('加载商品列表失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '加载商品列表失败',
            icon: 'none'
        });
    }
}

// 切换布局类型
const changeLayoutType = (type: string) => {
    layoutType.value = type;
}

// 筛选弹窗引用
const filterPopupRef = ref<InstanceType<typeof TblGoodsFilterPopup> | null>(null)
// 打开筛选弹窗
const openFilterPopup = () => {
    filterPopupRef.value?.open()
}
// 筛选确认：根据弹窗返回的 payload 更新 params 并刷新列表
const handleFilterConfirm = (payload: {
    is_flashsale_goods?: '' | '0' | '1'
    is_wholesale_goods?: '' | '0' | '1'
    is_userdiscount_goods?: '' | '0' | '1'
    is_distributor_goods?: '' | '0' | '1'
    min_price?: string
    max_price?: string
    min_goods_avg_score?: string
    max_goods_avg_score?: string
}) => {
    if (payload.is_flashsale_goods !== undefined) params.value.is_flashsale_goods = payload.is_flashsale_goods
    if (payload.is_wholesale_goods !== undefined) params.value.is_wholesale_goods = payload.is_wholesale_goods
    if (payload.is_userdiscount_goods !== undefined) params.value.is_userdiscount_goods = payload.is_userdiscount_goods
    if (payload.is_distributor_goods !== undefined) params.value.is_distributor_goods = payload.is_distributor_goods
    if (payload.min_price !== undefined) params.value.min_price = payload.min_price ?? ''
    if (payload.max_price !== undefined) params.value.max_price = payload.max_price ?? ''
    if (payload.min_goods_avg_score !== undefined) params.value.min_goods_avg_score = payload.min_goods_avg_score ?? ''
    if (payload.max_goods_avg_score !== undefined) params.value.max_goods_avg_score = payload.max_goods_avg_score ?? ''
    paging.value?.reload()
}

// 页面加载时获取URL参数
onLoad((options: any) => {
    if (options && options.keyword) {
        // 解码URL编码的关键词
        params.value.goods_name = decodeURIComponent(options.keyword);
    }
    if (options && options.category_id) {
        // 设置分类ID
        params.value.category_id = options.category_id;
    }
})



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
        padding: 12rpx;
        transition: opacity 0.2s ease;

        &:active {
            opacity: 0.7;
        }

        .layout-icon {
            width: 40rpx;
            height: 40rpx;
        }
    }
}

// 网格布局 - 简约京东风格
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

                .price-container {
                    display: flex;
                    align-items: baseline;

                    .price-symbol {
                        font-size: 22rpx;
                        color: $ds-primary-color;
                        font-weight: 500;
                    }

                    .price-value {
                        font-size: $ds-font-lg;
                        color: $ds-primary-color;
                        font-weight: 600;
                        margin-left: 2rpx;
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

// 列表布局 - 简约淘宝风格
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
                font-size: $ds-font-md;
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

            .goods-sku {
                display: inline-flex;
                align-items: center;
                font-size: 22rpx;
                color: $ds-text-secondary;
                background-color: $ds-bg-gray;
                padding: 6rpx 12rpx;
                border-radius: 4rpx;
                align-self: flex-start;
                margin-bottom: 12rpx;

                text {
                    margin-left: 4rpx;
                }
            }

            .goods-bottom {
                display: flex;
                align-items: center;
                justify-content: space-between;

                .goods-price {
                    display: flex;
                    align-items: baseline;

                    .price-symbol {
                        font-size: $ds-font-sm;
                        color: $ds-primary-color;
                        font-weight: 500;
                    }

                    .price-value {
                        font-size: $ds-font-xl;
                        color: $ds-primary-color;
                        font-weight: 600;
                        margin-left: 2rpx;
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
