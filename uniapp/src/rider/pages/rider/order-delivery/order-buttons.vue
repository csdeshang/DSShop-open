<template>
    <view>
        <button class="ds-btn-md" v-if="orderDelivery.rider_available_actions?.includes('grab')"
            @click="handleGrab(orderDelivery.order_id)">抢单</button>
        <button class="ds-btn-md" v-if="orderDelivery.rider_available_actions?.includes('cancel')"
            @click="handleCancel(orderDelivery.order_id)">取消</button>
        <button class="ds-btn-md" v-if="orderDelivery.rider_available_actions?.includes('arrived')"
            @click="handleArrived(orderDelivery.order_id)">已到店</button>
        <button class="ds-btn-md" v-if="orderDelivery.rider_available_actions?.includes('pickup')"
            @click="handlePickup(orderDelivery.order_id)">取货</button>
        <button class="ds-btn-md" v-if="orderDelivery.rider_available_actions?.includes('complete')"
            @click="handleComplete(orderDelivery.order_id)">完成送达</button>
    </view>
</template>

<script setup lang="ts">
import {
    grabOrderDelivery,
    cancelOrderDelivery,
    arrivedOrderDelivery,
    pickupOrderDelivery,
    completeOrderDelivery
} from '@/rider/api/order/orderDelivery';

import { useLbsStore } from '@/stores/modules/lbs'
const lbsStore = useLbsStore()

const props = defineProps({
    orderDelivery: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['refresh'])

// 抢单
const handleGrab = async (order_id: string) => {
    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    uni.showModal({
        title: '抢单确认',
        content: '确定要抢此订单吗？',
        success: (res) => {
            if (res.confirm) {
                // 显示加载提示
                uni.showLoading({
                    title: '处理中...',
                    mask: true
                });

                // 调用API
                grabOrderDelivery({
                    order_id: order_id,
                    latitude: lbsStore.latitude,
                    longitude: lbsStore.longitude
                }).then(res => {
                    // 关闭加载提示
                    uni.hideLoading();

                    if (res.code === 10000) {
                        uni.showToast({
                            title: '抢单成功',
                            icon: 'success'
                        });

                        // 刷新列表
                        emit('refresh')
                    } else {
                        uni.showToast({
                            title: res.message || '操作失败',
                            icon: 'none'
                        });
                    }
                })
                    .catch(err => {
                        // 关闭加载提示
                        uni.hideLoading();

                        uni.showToast({
                            title: '网络错误，请重试',
                            icon: 'none'
                        });
                        console.error('操作失败:', err);
                    });
            }
        }
    });
};

// 取消订单
const handleCancel = async (order_id: string) => {
    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    uni.showModal({
        title: '取消订单',
        content: '确定要取消此订单吗？取消后无法恢复',
        success: (res) => {
            if (res.confirm) {
                // 显示加载提示
                uni.showLoading({
                    title: '处理中...',
                    mask: true
                });

                // 调用取消订单API
                cancelOrderDelivery({
                    order_id: order_id,
                    latitude: lbsStore.latitude,
                    longitude: lbsStore.longitude
                })
                    .then(res => {
                        // 关闭加载提示
                        uni.hideLoading();

                        if (res.code === 10000) {
                            uni.showToast({
                                title: '订单已取消',
                                icon: 'success'
                            });

                            // 刷新列表
                            emit('refresh')
                        } else {
                            uni.showToast({
                                title: res.message || '取消失败',
                                icon: 'none'
                            });
                        }
                    })
                    .catch(err => {
                        // 关闭加载提示
                        uni.hideLoading();

                        uni.showToast({
                            title: '网络错误，请重试',
                            icon: 'none'
                        });
                        console.error('取消订单失败:', err);
                    });
            }
        }
    });
};

// 已到店
const handleArrived = async (order_id: string) => {
    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    uni.showModal({
        title: '到店确认',
        content: '确认已到达店铺？',
        success: (res) => {
            if (res.confirm) {
                // 显示加载提示
                uni.showLoading({
                    title: '处理中...',
                    mask: true
                });

                // 调用API
                arrivedOrderDelivery({
                    order_id: order_id,
                    latitude: lbsStore.latitude,
                    longitude: lbsStore.longitude
                })
                    .then(res => {
                        // 关闭加载提示
                        uni.hideLoading();

                        if (res.code === 10000) {
                            uni.showToast({
                                title: '已确认到店',
                                icon: 'success'
                            });

                            // 刷新列表
                            emit('refresh')
                        } else {
                            uni.showToast({
                                title: res.message || '操作失败',
                                icon: 'none'
                            });
                        }
                    })
                    .catch(err => {
                        // 关闭加载提示
                        uni.hideLoading();

                        uni.showToast({
                            title: '网络错误，请重试',
                            icon: 'none'
                        });
                        console.error('操作失败:', err);
                    });
            }
        }
    });
};

// 取货
const handlePickup = async (order_id: string) => {
    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    uni.showModal({
        title: '取货确认',
        content: '确认已取到商品？',
        success: (res) => {
            if (res.confirm) {
                // 显示加载提示
                uni.showLoading({
                    title: '处理中...',
                    mask: true
                });

                // 调用API
                pickupOrderDelivery({
                    order_id: order_id,
                    latitude: lbsStore.latitude,
                    longitude: lbsStore.longitude
                })
                    .then(res => {
                        // 关闭加载提示
                        uni.hideLoading();

                        if (res.code === 10000) {
                            uni.showToast({
                                title: '取货成功',
                                icon: 'success'
                            });

                            // 刷新列表
                            emit('refresh')
                        } else {
                            uni.showToast({
                                title: res.message || '操作失败',
                                icon: 'none'
                            });
                        }
                    })
                    .catch(err => {
                        // 关闭加载提示
                        uni.hideLoading();

                        uni.showToast({
                            title: '网络错误，请重试',
                            icon: 'none'
                        });
                        console.error('操作失败:', err);
                    });
            }
        }
    });
};

// 完成送达
const handleComplete = async (order_id: string) => {
    // 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
    // 暂时设置为 1 天
    await lbsStore.getLocation(true, 24 * 60 * 60)

    uni.showModal({
        title: '完成送达',
        content: '确认已送达商品给客户？',
        success: (res) => {
            if (res.confirm) {
                // 显示加载提示
                uni.showLoading({
                    title: '处理中...',
                    mask: true
                });

                // 调用完成送达API
                completeOrderDelivery({
                    order_id: order_id,
                    latitude: lbsStore.latitude,
                    longitude: lbsStore.longitude
                })
                    .then(res => {
                        // 关闭加载提示
                        uni.hideLoading();

                        if (res.code === 10000) {
                            uni.showToast({
                                title: '送达完成',
                                icon: 'success'
                            });

                            // 刷新列表
                            emit('refresh')
                        } else {
                            uni.showToast({
                                title: res.message || '操作失败',
                                icon: 'none'
                            });
                        }
                    })
                    .catch(err => {
                        // 关闭加载提示
                        uni.hideLoading();

                        uni.showToast({
                            title: '网络错误，请重试',
                            icon: 'none'
                        });
                        console.error('操作失败:', err);
                    });
            }
        }
    });
};
</script>

<style scoped lang="scss"></style>
