<template>
    <view class="coupon-list-page">

        <!-- 优惠券列表 -->
        <z-paging ref="pagingRef" v-model="couponList" @query="queryList">
            <template #top>
                <!-- 筛选栏 -->
                <view class="filter-bar">
                    <view class="filter-tabs">
                        <!-- 状态选项 -->
                        <view class="filter-tab" 
                            v-for="item in status_options" 
                            :key="item.value"
                            :class="[pageParams.status === item.value ? 'active' : '']" 
                            @click="changeFilter(Number(item.value))">
                            {{ item.label }}
                        </view>
                    </view>
                </view>
            </template>

            <view class="coupon-list">
                <view class="coupon-item" v-for="coupon in couponList" :key="coupon.id"
                    :class="[coupon.status != 1 ? 'disabled' : '']" @click="handleCouponClick(coupon)">
                    <view class="coupon-left">
                        <view class="coupon-amount">
                            <text class="amount-symbol" v-if="coupon.coupon_type === 1">￥</text>
                            <text class="amount-value">{{ formatDiscountValue(coupon) }}</text>
                            <text class="amount-unit" v-if="coupon.coupon_type === 2">折</text>
                        </view>
                        <text class="coupon-threshold" v-if="Number(coupon.min_spend) > 0">
                            满{{ coupon.min_spend }}可用
                        </text>
                        <text class="coupon-type">{{ coupon.coupon_type_desc }}</text>
                    </view>

                    <view class="coupon-right">
                        <view class="coupon-info">
                            <text class="coupon-name">{{ coupon.coupon_name }}</text>
                            <text class="coupon-date">{{ formatDate(coupon) }}</text>
                            <text class="stock-info">剩余{{ coupon.available_stock }}张</text>
                        </view>

                        <view class="coupon-action">
                            <button class="receive-btn" v-if="coupon.available_stock > 0"
                                @click.stop="receiveCoupon(coupon)" :disabled="coupon.status === 0">
                                {{ coupon.status === 0 ? '已停用' : '立即领取' }}
                            </button>
                            <view class="status-tag sold-out" v-else>
                                已抢完
                            </view>
                        </view>
                    </view>

                    <view class="coupon-border"></view>
                </view>
            </view>

            <!-- 自定义空数据 -->
            <template #empty>
                <view class="empty-state">
                    <text class="empty-text">暂无优惠券</text>
                </view>
            </template>
        </z-paging>

        <!-- 优惠券详情弹窗 -->
        <uni-popup ref="detailPopupRef" type="bottom">
            <view class="detail-popup" v-if="selectedCoupon">
                <view class="popup-header">
                    <text class="popup-title">优惠券详情</text>
                    <uni-icons type="closeempty" size="24" color="#999" @click="closeDetail" />
                </view>

                <view class="detail-content">
                    <view class="detail-item">
                        <text class="label">优惠券名称：</text>
                        <text class="value">{{ selectedCoupon.coupon_name }}</text>
                    </view>
                    <view class="detail-item">
                        <text class="label">优惠类型：</text>
                        <text class="value">{{ selectedCoupon.coupon_type_desc }}</text>
                    </view>
                    <view class="detail-item">
                        <text class="label">优惠金额：</text>
                        <text class="value amount">
                            <text v-if="selectedCoupon.coupon_type === 1">￥{{ selectedCoupon.discount_value }}</text>
                            <text v-else>{{ (selectedCoupon.discount_value * 10).toFixed(1) }}折</text>
                        </text>
                    </view>
                    <view class="detail-item">
                        <text class="label">使用条件：</text>
                        <text class="value">
                            {{ selectedCoupon.min_spend > 0 ? `满${selectedCoupon.min_spend}元可用` : '无门槛' }}
                        </text>
                    </view>
                    <view class="detail-item">
                        <text class="label">有效期：</text>
                        <text class="value">{{ formatDateRange(selectedCoupon) }}</text>
                    </view>
                    <view class="detail-item">
                        <text class="label">库存信息：</text>
                        <text class="value">剩余{{ selectedCoupon.available_stock }}张</text>
                    </view>
                </view>

                <view class="popup-actions">
                    <button class="receive-btn-large" v-if="selectedCoupon.available_stock > 0"
                        @click="receiveCoupon(selectedCoupon)" :disabled="selectedCoupon.status === 0">
                        {{ selectedCoupon.status === 0 ? '已停用' : '立即领取' }}
                    </button>
                    <view class="sold-out-text" v-else>
                        该优惠券已抢完
                    </view>
                </view>
            </view>
        </uni-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { getTblStoreCouponPages } from '@/home/api/tbl-store/tblStoreCoupon'
import { receiveTblStoreCoupon } from '@/home/api/user/userTblStoreCoupon'

import { useEnum } from '@/hooks/useEnum'

// 使用枚举 Hook获取变动类型选项
const { options: status_options } = useEnum('default.tbl_store_coupon.status')

// 页面参数接口
interface PageParams {
    status: number
}

// 分页参数接口
interface GetCouponListParams {
    page_current: number
    page_size: number
    status: number
}

// 优惠券接口
interface Coupon {
    id: number
    coupon_name: string
    coupon_type: number
    coupon_type_desc: string
    discount_value: number
    min_spend: number
    start_time: string
    end_time: string
    total_stock: number
    claimed_stock: number
    available_stock: number
    limit_per_user: number
    claim_type: number
    claim_type_desc: string
    points_required: number
    sort: number
    status: number
    status_desc: string
    create_at: string
    update_at: string
}

// 页面参数
const pageParams = ref<PageParams>({ status: 1 })
// z-paging 实例引用
const pagingRef = ref<any>(null)
// 优惠券列表数据
const couponList = ref<Coupon[]>([])
// 选中的优惠券
const selectedCoupon = ref<Coupon | null>(null)
// 详情弹窗引用
const detailPopupRef = ref()

// 页面加载
onLoad((options: any) => {
    if (options.status !== undefined) {
        pageParams.value.status = Number(options.status)
    }
})

onShow(() => {
    // 页面显示时刷新数据
    pagingRef.value?.reload()
})

// 返回上一页
const goBack = () => {
    uni.navigateBack()
}

// 分页查询方法
const queryList = async (page_current: number, page_size: number) => {
    try {
        const params: GetCouponListParams = {
            status: pageParams.value.status,
            page_current,
            page_size
        }
        
        const result = await getTblStoreCouponPages(params)
        
        if (result.code === 10000) {
            // 完成分页加载，传入当前页数据
            pagingRef.value?.complete(result.data.data)
        } else {
            // 加载失败
            pagingRef.value?.complete(false)
            uni.showToast({
                title: result.message || '加载失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('优惠券加载失败:', error)
        // 加载异常
        pagingRef.value?.complete(false)
        uni.showToast({
            title: '网络错误，请重试',
            icon: 'none'
        })
    }
}

// 切换筛选
const changeFilter = (filterValue: number) => {
    pageParams.value.status = filterValue
    // 重新加载数据
    pagingRef.value?.reload()
}

// 手动刷新方法
const refresh = () => {
    pagingRef.value?.reload()
}

// 点击优惠券
const handleCouponClick = (coupon: Coupon) => {
    selectedCoupon.value = coupon
    detailPopupRef.value.open()
}

// 关闭详情弹窗
const closeDetail = () => {
    detailPopupRef.value.close()
    selectedCoupon.value = null
}

// 领取优惠券
const receiveCoupon = async (coupon: Coupon) => {
    try {
        const res = await receiveTblStoreCoupon(coupon.id)
        if (res.code === 10000) {
            uni.showToast({
                title: '领取成功',
                icon: 'success'
            })
            // 更新库存
            coupon.available_stock = Math.max(0, coupon.available_stock - 1)
            coupon.claimed_stock = coupon.claimed_stock + 1

            // 如果是在详情弹窗中，关闭弹窗
            if (selectedCoupon.value && selectedCoupon.value.id === coupon.id) {
                closeDetail()
            }
        } else {
            uni.showToast({
                title: res.message || '领取失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('领取优惠券失败:', error)
        uni.showToast({
            title: '领取失败',
            icon: 'none'
        })
    }
}

// 格式化折扣值
const formatDiscountValue = (coupon: Coupon) => {
    if (coupon.coupon_type === 1) {
        return coupon.discount_value.toString()
    } else if (coupon.coupon_type === 2) {
        return (coupon.discount_value * 10).toString()
    } else {
        return coupon.discount_value.toString()
    }
}

// 格式化日期（简短版）
const formatDate = (coupon: Coupon) => {
    if (!coupon.start_time || !coupon.end_time) return '长期有效'

    const startDate = new Date(coupon.start_time.replace(/-/g, '/'))
    const endDate = new Date(coupon.end_time.replace(/-/g, '/'))

    const startYear = startDate.getFullYear()
    const startMonth = startDate.getMonth() + 1
    const startDay = startDate.getDate()
    const endYear = endDate.getFullYear()
    const endMonth = endDate.getMonth() + 1
    const endDay = endDate.getDate()

    if (startYear === endYear) {
        return `${startYear}.${startMonth}.${startDay}-${endMonth}.${endDay}`
    } else {
        return `${startYear}.${startMonth}.${startDay}-${endYear}.${endMonth}.${endDay}`
    }
}

// 格式化日期范围（详细版）
const formatDateRange = (coupon: Coupon) => {
    if (!coupon.start_time || !coupon.end_time) return '长期有效'

    const startTime = coupon.start_time.replace(/-/g, '/')
    const endTime = coupon.end_time.replace(/-/g, '/')

    return `${startTime} 至 ${endTime}`
}

// 暴露刷新方法
defineExpose({
    refresh
})

</script>

<style lang="scss" scoped>
.coupon-list-page {
    background: #f8f8f8;
    min-height: 100vh;
}

.filter-bar {
    background: #fff;
    padding: 20rpx;

    .filter-tabs {
        display: flex;
        gap: 20rpx;

        .filter-tab {
            padding: 12rpx 24rpx;
            background: #f5f5f5;
            border-radius: 20rpx;
            font-size: 28rpx;
            color: #666;
            transition: all 0.3s;

            &.active {
                background: linear-gradient(to right, #ff6b6b, #f56c6c);
                color: #fff;
            }
        }
    }
}

.coupon-list {
    padding: 0 20rpx;

    .coupon-item {
        display: flex;
        background: #fff;
        border-radius: 16rpx;
        margin-bottom: 20rpx;
        overflow: hidden;
        box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
        position: relative;

        &.disabled {
            opacity: 0.6;

            .coupon-left {
                background: linear-gradient(135deg, #bdbdbd, #e0e0e0);
            }
        }

        .coupon-left {
            width: 180rpx;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20rpx;
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            color: #fff;
            position: relative;

            &::before {
                content: '';
                position: absolute;
                top: -10rpx;
                right: -10rpx;
                width: 40rpx;
                height: 40rpx;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            &::after {
                content: '';
                position: absolute;
                bottom: -5rpx;
                left: -5rpx;
                width: 20rpx;
                height: 20rpx;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .coupon-amount {
                display: flex;
                align-items: baseline;
                margin-bottom: 8rpx;

                .amount-symbol {
                    font-size: 28rpx;
                    margin-right: 4rpx;
                }

                .amount-value {
                    font-size: 48rpx;
                    font-weight: bold;
                }

                .amount-unit {
                    font-size: 28rpx;
                    margin-left: 4rpx;
                }
            }

            .coupon-threshold {
                font-size: 22rpx;
                opacity: 0.9;
                margin-bottom: 4rpx;
            }

            .coupon-type {
                font-size: 20rpx;
                opacity: 0.8;
            }
        }

        .coupon-right {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20rpx;

            .coupon-info {
                flex: 1;

                .coupon-name {
                    display: block;
                    font-size: 30rpx;
                    font-weight: 500;
                    color: #333;
                    margin-bottom: 8rpx;
                    line-height: 1.4;
                }

                .coupon-date {
                    display: block;
                    font-size: 24rpx;
                    color: #999;
                    margin-bottom: 6rpx;
                }

                .stock-info {
                    display: block;
                    font-size: 22rpx;
                    color: #ff9500;
                }
            }

            .coupon-action {
                .receive-btn {
                    background: linear-gradient(to right, #ff6b6b, #f56c6c);
                    color: #fff;
                    border-radius: 20rpx;
                    font-size: 24rpx;
                    padding: 0 20rpx;
                    height: 40rpx;
                    line-height: 40rpx;
                    border: none;

                    &:disabled {
                        background: #e0e0e0;
                        color: #999;
                    }
                }

                .status-tag {
                    font-size: 24rpx;
                    padding: 8rpx 16rpx;
                    border-radius: 16rpx;

                    &.sold-out {
                        background: #fef0f0;
                        color: #f56c6c;
                    }
                }
            }
        }

        .coupon-border {
            position: absolute;
            left: 180rpx;
            top: 0;
            bottom: 0;
            width: 0;
            border-left: 1px dashed #ddd;

            &::before,
            &::after {
                content: '';
                position: absolute;
                width: 16rpx;
                height: 16rpx;
                background: #f8f8f8;
                border-radius: 50%;
                left: -9rpx;
            }

            &::before {
                top: -8rpx;
            }

            &::after {
                bottom: -8rpx;
            }
        }
    }
}

.empty-state {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 100rpx 20rpx;

    .empty-text {
        font-size: 28rpx;
        color: #999;
    }
}

// 详情弹窗样式
.detail-popup {
    background: #fff;
    border-radius: 20rpx 20rpx 0 0;
    padding: 40rpx;
    max-height: 80vh;

    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40rpx;

        .popup-title {
            font-size: 36rpx;
            font-weight: bold;
            color: #333;
        }
    }

    .detail-content {
        .detail-item {
            display: flex;
            margin-bottom: 24rpx;

            .label {
                width: 180rpx;
                font-size: 28rpx;
                color: #666;
            }

            .value {
                flex: 1;
                font-size: 28rpx;
                color: #333;

                &.amount {
                    color: #f56c6c;
                    font-weight: bold;
                }
            }
        }
    }

    .popup-actions {
        margin-top: 40rpx;

        .receive-btn-large {
            width: 100%;
            height: 80rpx;
            line-height: 80rpx;
            background: linear-gradient(to right, #ff6b6b, #f56c6c);
            color: #fff;
            border-radius: 40rpx;
            font-size: 32rpx;
            border: none;

            &:disabled {
                background: #e0e0e0;
                color: #999;
            }
        }

        .sold-out-text {
            text-align: center;
            color: #f56c6c;
            font-size: 28rpx;
            padding: 20rpx;
        }
    }
}
</style>
