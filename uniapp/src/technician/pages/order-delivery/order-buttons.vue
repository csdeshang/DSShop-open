<template>
    <view>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('grab')"
        @click="handleGrab(orderDelivery.order_id)">抢单</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('technician_accept')"
        @click="handleTechnicianAccept(orderDelivery.order_id)">确认接单</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('technician_departure')"
        @click="handleTechnicianDeparture(orderDelivery.order_id)">出发</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('technician_arrived')"
        @click="handleTechnicianArrived(orderDelivery.order_id)">已到达</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('start_service')"
        @click="handleStartService(orderDelivery.order_id)">开始服务</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('complete')"
        @click="handleComplete(orderDelivery.order_id)">完成服务</button>
    <button class="ds-btn-md" v-if="orderDelivery.technician_available_actions?.includes('cancel')"
        @click="handleCancel(orderDelivery.order_id)">取消订单</button>
    </view>
</template>

<script setup lang="ts">



import {
    grabOrderDelivery,
    acceptOrderDelivery,
    departureOrderDelivery,
    arrivedOrderDelivery,
    startServiceOrderDelivery,
    completeOrderDelivery,
    cancelOrderDelivery
} from '@/technician/api/orderDelivery';


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

// 确认接单
const handleTechnicianAccept = async (order_id: string) => {

// 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
// 暂时设置为 1 天
await lbsStore.getLocation(true, 24 * 60 * 60)

uni.showModal({
    title: '接单确认',
    content: '确定要接受此订单吗？',
    success: (res) => {
        if (res.confirm) {
            // 显示加载提示
            uni.showLoading({
                title: '处理中...',
                mask: true
            });

            // 调用API
            acceptOrderDelivery({
                order_id: order_id,
                latitude: lbsStore.latitude,
                longitude: lbsStore.longitude
            })
                .then(res => {
                    // 关闭加载提示
                    uni.hideLoading();

                    if (res.code === 10000) {
                        uni.showToast({
                            title: '接单成功',
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

// 出发
const handleTechnicianDeparture = async (order_id: string) => {

// 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
// 暂时设置为 1 天
await lbsStore.getLocation(true, 24 * 60 * 60)

uni.showModal({
    title: '出发确认',
    content: '确认已出发前往客户地点？',
    success: (res) => {
        if (res.confirm) {
            // 显示加载提示
            uni.showLoading({
                title: '处理中...',
                mask: true
            });

            // 调用出发API
            departureOrderDelivery({
                order_id: order_id,
                latitude: lbsStore.latitude,
                longitude: lbsStore.longitude
            })
                .then(res => {
                    // 关闭加载提示
                    uni.hideLoading();

                    if (res.code === 10000) {
                        uni.showToast({
                            title: '已确认出发',
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

// 已到达
const handleTechnicianArrived = async (order_id: string) => {

// 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
// 暂时设置为 1 天
await lbsStore.getLocation(true, 24 * 60 * 60)

uni.showModal({
    title: '到达确认',
    content: '确认已到达客户地点？',
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
                            title: '已确认到达',
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

// 开始服务
const handleStartService = async (order_id: string) => {

// 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
// 暂时设置为 1 天
await lbsStore.getLocation(true, 24 * 60 * 60)

uni.showModal({
    title: '开始服务',
    content: '确认开始提供服务？',
    success: (res) => {
        if (res.confirm) {
            // 显示加载提示
            uni.showLoading({
                title: '处理中...',
                mask: true
            });

            // 调用API
            startServiceOrderDelivery({
                order_id: order_id,
                latitude: lbsStore.latitude,
                longitude: lbsStore.longitude
            })
                .then(res => {
                    // 关闭加载提示
                    uni.hideLoading();

                    if (res.code === 10000) {
                        uni.showToast({
                            title: '已开始服务',
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

// 完成服务
const handleComplete = async (order_id: string) => {

// 检查位置信息是否过期（5分钟过期时间），如过期则更新位置信息
// 暂时设置为 1 天
await lbsStore.getLocation(true, 24 * 60 * 60)

uni.showModal({
    title: '完成服务',
    content: '确认已完成所有服务内容？',
    success: (res) => {
        if (res.confirm) {
            // 显示加载提示
            uni.showLoading({
                title: '处理中...',
                mask: true
            });

            // 调用完成服务API
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
                            title: '服务已完成',
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


</script>

<style scoped lang="scss"></style>
