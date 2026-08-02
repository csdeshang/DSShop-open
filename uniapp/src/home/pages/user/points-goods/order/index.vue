<template>
    <view class="order-container">
        <z-paging ref="paging" v-model="orders" @query="queryList">
            <!-- 订单状态选项卡 - 使用z-paging的fixed特性 -->
            <template v-slot:top>
                <view class="order-tabs">
                    <view v-for="(tab, index) in tabs" :key="index" class="tab-item"
                        :class="{ active: currentTab === index }" @click="switchTab(index)">
                        {{ tab.label }}
                    </view>
                </view>
            </template>

            <!-- 订单列表 -->
            <view class="order-list" v-if="orders.length > 0">
                <view class="order-item" v-for="order in orders" :key="order.id">
                    <!-- 订单头部信息 -->
                    <view class="order-header">
                        <view class="order-sn">订单号：{{ order.order_sn }}</view>
                        <view class="order-status">{{ order.order_status_desc }}</view>
                    </view>

                    <!-- 订单商品信息 -->
                    <view class="order-goods" @click="goToOrderDetail(order.id)">
                        <image class="goods-image" :src="formatImageUrl(order.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
                        <view class="goods-info">
                            <view class="goods-name">{{ order.goods_name }}</view>
                            <view class="goods-price-qty">
                                <text class="goods-points">{{ order.points_price }}积分</text>
                                <text class="goods-qty">x{{ order.exchange_num }}</text>
                            </view>
                        </view>
                    </view>

                    <!-- 订单金额信息 -->
                    <view class="order-total">
                        <text>共{{ order.exchange_num }}件商品</text>
                        <text>总积分：<text class="points">{{ order.total_points }}积分</text></text>
                    </view>


                    <!-- 订单操作按钮 -->
                    <view class="order-actions">
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('cancel')"
                            @click="cancelOrder(order)">取消订单</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('confirm')"
                            @click="confirmReceive(order)">确认收货</button>
                        <button class="ds-btn-md" v-if="order.user_available_actions?.includes('evaluate')"
                            @click="evaluateOrder(order)">评价</button>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import {
    getPointsGoodsOrderPages,
    cancelPointsGoodsOrder,
    confirmPointsGoodsOrder,
} from '@/home/api/user/userPointsGoodsOrder';

// 定义类型
interface PointsGoodsOrder {
    id: number;
    order_sn: string;
    goods_id: number;
    goods_name: string;
    goods_image: string;
    points_price: number;
    exchange_num: number;
    total_points: number;
    order_status: number;
    order_status_desc: string;
    delivery_method: string;
    delivery_method_desc: string;
    receiver_name: string;
    receiver_mobile: string;
    receiver_address: string;
    express_company: string;
    express_no: string;
    express_time: number;
    receive_time: number;
    remark: string;
    create_at: number;
    update_at: number;
    user_available_actions?: string[];
}

// 定义订单状态选项卡
const tabs = [
    { label: '全部', value: '' },
    { label: '待发货', value: '10' },
    { label: '已发货', value: '20' },
    { label: '已收货', value: '30' },
    { label: '已完成', value: '40' }
];

// 当前选中的选项卡
const currentTab = ref(0);

// 订单列表
const orders = ref<PointsGoodsOrder[]>([]);

// z-paging 引用
const paging = ref<any>(null);

// 切换选项卡
const switchTab = (index: number) => {
    if (currentTab.value !== index) {
        currentTab.value = index;
        // 使用z-paging提供的刷新方法
        if (paging.value) {
            paging.value.reload();
        }
    }
};

// 查询列表数据 - 提供给z-paging的回调函数
const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getPointsGoodsOrderPages({
            page_current,
            page_size,
            order_status: tabs[currentTab.value].value
        });

        // 处理完成后通知z-paging
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('查询积分商品订单列表失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '加载订单失败',
            icon: 'none'
        });
    }
};

// 跳转到订单详情
const goToOrderDetail = (orderId: number) => {
    uni.navigateTo({
        url: `/home/pages/user/points-goods/order/detail?id=${orderId}`
    });
};

// 取消订单
const cancelOrder = async (orderInfo: PointsGoodsOrder) => {
    uni.showModal({
        title: '提示',
        content: '确定要取消该订单吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    // 调用取消订单API
                    await cancelPointsGoodsOrder(orderInfo.id);
                    uni.showToast({
                        title: '订单已取消',
                        icon: 'success'
                    });
                    // 刷新列表
                    if (paging.value) {
                        paging.value.reload();
                    }
                } catch (error) {
                    console.error('取消订单失败:', error);
                    uni.showToast({
                        title: '取消订单失败',
                        icon: 'none'
                    });
                }
            }
        }
    });
};

// 确认收货
const confirmReceive = async (orderInfo: PointsGoodsOrder) => {
    uni.showModal({
        title: '提示',
        content: '确认已收到商品吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    // 调用确认收货API
                    const result = await confirmPointsGoodsOrder(orderInfo.id);
                    if (result.code == 10000) {
                        uni.showToast({
                            title: '确认收货成功',
                            icon: 'success'
                        });
                        // 刷新列表
                        if (paging.value) {
                            paging.value.reload();
                        }
                    } else {
                        uni.showToast({
                            title: result.message,
                            icon: 'none'
                        });
                    }
                } catch (error) {
                    console.error('确认收货失败:', error);
                    uni.showToast({
                        title: '确认收货失败',
                        icon: 'none'
                    });
                }
            }
        }
    });
};

// 评价订单
const evaluateOrder = (orderInfo: PointsGoodsOrder) => {
    uni.navigateTo({
        url: `/home/pages/user/points-goods/evaluate/add?order_id=${orderInfo.id}`
    });
};
</script>

<style lang="scss" scoped>
.order-container {
    background-color: #f5f5f5;
    min-height: 100vh;
}

.order-tabs {
    display: flex;
    background-color: #fff;
    padding: 20rpx 0;
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .tab-item {
        flex: 1;
        text-align: center;
        font-size: 26rpx;
        color: #666;
        position: relative;
        padding-bottom: 10rpx;
    }

    .tab-item.active {
        color: #FF6034;
        font-weight: bold;
    }

    .tab-item.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40rpx;
        height: 4rpx;
        background-color: #FF6034;
        border-radius: 2rpx;
    }
}

.order-list {
    padding: 20rpx;
    margin-bottom: 20rpx;
}

.order-item {
    background-color: #fff;
    border-radius: 10rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20rpx;
        border-bottom: 1rpx solid #f0f0f0;

        .order-sn {
            font-size: 24rpx;
            color: #999;
        }

        .order-status {
            font-size: 26rpx;
            color: #FF6034;
        }
    }

    .order-goods {
        display: flex;
        padding: 20rpx 0;
        border-bottom: 1rpx solid #f0f0f0;

        .goods-image {
            width: 160rpx;
            height: 160rpx;
            border-radius: 8rpx;
            margin-right: 20rpx;
            background-color: #f9f9f9;
        }

        .goods-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;

            .goods-name {
                font-size: 28rpx;
                color: #333;
                margin-bottom: 10rpx;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
            }

            .goods-price-qty {
                display: flex;
                justify-content: space-between;
                align-items: center;

                .goods-points {
                    font-size: 28rpx;
                    color: #FF6034;
                    font-weight: bold;
                }

                .goods-qty {
                    font-size: 24rpx;
                    color: #999;
                }
            }
        }
    }

    .order-total {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 20rpx 0;
        font-size: 26rpx;
        color: #666;
        border-bottom: 1rpx solid #f0f0f0;

        .points {
            font-size: 30rpx;
            color: #FF6034;
            font-weight: bold;
            margin-left: 10rpx;
        }
    }


    .order-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 20rpx;
    }
}

</style>
