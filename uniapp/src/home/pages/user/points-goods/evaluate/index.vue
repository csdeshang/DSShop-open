<template>
    <view class="evaluate-container">
        <z-paging ref="paging" v-model="evaluates" @query="queryList">
            <!-- 评价状态选项卡 -->
            <template v-slot:top>
                <view class="evaluate-tabs">
                    <view v-for="(tab, index) in tabs" :key="index" class="tab-item"
                        :class="{ active: currentTab === index }" @click="switchTab(index)">
                        {{ tab.label }}
                    </view>
                </view>
            </template>

            <!-- 评价列表 -->
            <view class="evaluate-list" v-if="evaluates.length > 0">
                <view class="evaluate-item" v-for="evaluate in evaluates" :key="evaluate.id">
                    <!-- 商品信息 -->
                    <view class="goods-info" @click="goToGoodsDetail(evaluate.goods_id)">
                        <image class="goods-image" :src="formatImageUrl(evaluate.goods_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
                        <view class="goods-details">
                            <view class="goods-name">{{ evaluate.goods_name }}</view>
                            <view class="goods-points">{{ evaluate.points_price }}积分</view>
                        </view>
                    </view>

                    <!-- 评价内容 -->
                    <view class="evaluate-content">
                        <!-- 评分 -->
                        <view class="evaluate-score">
                            <view class="score-label">评分：</view>
                            <view class="stars">
                                <text v-for="i in 5" :key="i" class="star" 
                                    :class="{ active: i <= evaluate.evaluate_score }">★</text>
                            </view>
                            <text class="score-text">{{ evaluate.evaluate_score }}分</text>
                        </view>

                        <!-- 评价文字 -->
                        <view class="evaluate-text" v-if="evaluate.evaluate_content">
                            {{ evaluate.evaluate_content }}
                        </view>

                        <!-- 评价图片 -->
                        <view class="evaluate-images" v-if="evaluate.evaluate_images && evaluate.evaluate_images.length > 0">
                            <image v-for="(image, index) in evaluate.evaluate_images" :key="index"
                                class="evaluate-image" :src="formatImageUrl(image, ThumbnailPresets.medium)" mode="aspectFill"
                                @click="previewImage(evaluate.evaluate_images, index)" />
                        </view>

                        <!-- 评价时间 -->
                        <view class="evaluate-time">
                            {{ formatTime(evaluate.create_at) }}
                        </view>
                    </view>

                    <!-- 操作按钮 -->
                    <view class="evaluate-actions">
                        <button class="ds-btn-sm" @click="goToEvaluateDetail(evaluate.id)">查看详情</button>
                        <button class="ds-btn-sm" v-if="evaluate.can_edit" @click="editEvaluate(evaluate)">编辑评价</button>
                    </view>
                </view>
            </view>

            <!-- 空状态 -->
            <view class="empty-state" v-else>
                <image class="empty-icon" src="/static/images/empty-evaluate.png" mode="aspectFit" />
                <text class="empty-text">暂无评价记录</text>
                <button class="ds-btn-md" @click="goToOrderList">去评价</button>
            </view>
        </z-paging>
    </view>
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import { getPointsGoodsEvaluatePages } from '@/home/api/user/userPointsGoodsEvaluate';

// 定义类型
interface PointsGoodsEvaluate {
    id: number;
    order_id: number;
    goods_id: number;
    goods_name: string;
    goods_image: string;
    points_price: number;
    user_id: number;
    evaluate_score: number;
    evaluate_content: string;
    evaluate_images: string[];
    is_anonymous: number;
    can_edit: boolean;
    create_at: number;
    update_at: number;
}

// 定义评价状态选项卡
const tabs = [
    { label: '全部', value: '' },
    { label: '好评', value: '5' },
    { label: '中评', value: '3,4' },
    { label: '差评', value: '1,2' }
];

// 当前选中的选项卡
const currentTab = ref(0);

// 评价列表
const evaluates = ref<PointsGoodsEvaluate[]>([]);

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
        const params: any = {
            page_current,
            page_size
        };

        // 根据选项卡添加评分筛选
        const tabValue = tabs[currentTab.value].value;
        if (tabValue) {
            if (tabValue.includes(',')) {
                // 多个评分值，使用IN查询
                params.evaluate_score = tabValue.split(',');
            } else {
                // 单个评分值
                params.evaluate_score = tabValue;
            }
        }

        const result = await getPointsGoodsEvaluatePages(params);

        // 处理完成后通知z-paging
        if (paging.value) {
            paging.value.complete(result.data.data);
        }
    } catch (e) {
        console.error('查询积分商品评价列表失败:', e);
        if (paging.value) {
            paging.value.complete(false);
        }
        uni.showToast({
            title: '加载评价失败',
            icon: 'none'
        });
    }
};

// 跳转到商品详情
const goToGoodsDetail = (goodsId: number) => {
    uni.navigateTo({
        url: `/home/pages/goods/detail?id=${goodsId}`
    });
};

// 跳转到评价详情
const goToEvaluateDetail = (evaluateId: number) => {
    uni.navigateTo({
        url: `/home/pages/user/points-goods/evaluate/detail?id=${evaluateId}`
    });
};

// 编辑评价
const editEvaluate = (evaluate: PointsGoodsEvaluate) => {
    uni.navigateTo({
        url: `/home/pages/user/points-goods/evaluate/add?order_id=${evaluate.order_id}&edit_id=${evaluate.id}`
    });
};

// 预览图片
const previewImage = (images: string[], current: number) => {
    const urls = images.map(img => formatImageUrl(img, ThumbnailPresets.original));
    uni.previewImage({
        urls,
        current: current
    });
};

// 跳转到订单列表
const goToOrderList = () => {
    uni.switchTab({
        url: '/home/pages/user/points-goods/order/index'
    });
};

// 格式化时间
const formatTime = (timestamp: number): string => {
    const date = new Date(timestamp * 1000);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    
    // 小于1分钟
    if (diff < 60000) {
        return '刚刚';
    }
    
    // 小于1小时
    if (diff < 3600000) {
        return Math.floor(diff / 60000) + '分钟前';
    }
    
    // 小于1天
    if (diff < 86400000) {
        return Math.floor(diff / 3600000) + '小时前';
    }
    
    // 小于7天
    if (diff < 604800000) {
        return Math.floor(diff / 86400000) + '天前';
    }
    
    // 超过7天显示具体日期
    return date.getFullYear() + '-' + 
           String(date.getMonth() + 1).padStart(2, '0') + '-' + 
           String(date.getDate()).padStart(2, '0');
};
</script>

<style lang="scss" scoped>
.evaluate-container {
    background-color: #f5f5f5;
    min-height: 100vh;
}

.evaluate-tabs {
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

.evaluate-list {
    padding: 20rpx;
    margin-bottom: 20rpx;
}

.evaluate-item {
    background-color: #fff;
    border-radius: 10rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .goods-info {
        display: flex;
        padding-bottom: 20rpx;
        border-bottom: 1rpx solid #f0f0f0;

        .goods-image {
            width: 120rpx;
            height: 120rpx;
            border-radius: 8rpx;
            margin-right: 20rpx;
            background-color: #f9f9f9;
        }

        .goods-details {
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

            .goods-points {
                font-size: 26rpx;
                color: #FF6034;
                font-weight: bold;
            }
        }
    }

    .evaluate-content {
        padding: 20rpx 0;

        .evaluate-score {
            display: flex;
            align-items: center;
            margin-bottom: 20rpx;

            .score-label {
                font-size: 26rpx;
                color: #666;
                margin-right: 10rpx;
            }

            .stars {
                margin-right: 10rpx;

                .star {
                    font-size: 32rpx;
                    color: #ddd;
                    margin-right: 4rpx;
                }

                .star.active {
                    color: #FF6034;
                }
            }

            .score-text {
                font-size: 24rpx;
                color: #FF6034;
                font-weight: bold;
            }
        }

        .evaluate-text {
            font-size: 28rpx;
            color: #333;
            line-height: 1.6;
            margin-bottom: 20rpx;
        }

        .evaluate-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10rpx;
            margin-bottom: 20rpx;

            .evaluate-image {
                width: 120rpx;
                height: 120rpx;
                border-radius: 8rpx;
                background-color: #f9f9f9;
            }
        }

        .evaluate-time {
            font-size: 24rpx;
            color: #999;
        }
    }

    .evaluate-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 20rpx;
        border-top: 1rpx solid #f0f0f0;
        gap: 20rpx;
    }
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100rpx 0;

    .empty-icon {
        width: 200rpx;
        height: 200rpx;
        margin-bottom: 20rpx;
    }

    .empty-text {
        font-size: 28rpx;
        color: #999;
        margin-bottom: 40rpx;
    }
}
</style>