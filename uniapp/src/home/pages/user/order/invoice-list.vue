<template>
    <view class="invoice-list-page">
        <z-paging ref="pagingRef" v-model="dataList" @query="queryList">
            <template v-slot:top>
                <view class="top-card">
                    <view v-if="filterOrderId > 0" class="filter-tip">
                        <text>仅显示本订单的开票记录</text>
                    </view>
                    <scroll-view scroll-x class="invoice-tabs-scroll" :show-scrollbar="false">
                        <view class="invoice-tabs-inner">
                            <view
                                v-for="(tab, index) in statusTabs"
                                :key="index"
                                class="invoice-tab-item"
                                :class="{ active: statusIndex === index }"
                                @click="switchStatus(index)"
                            >
                                {{ tab.label }}
                            </view>
                        </view>
                    </scroll-view>
                </view>
            </template>
            <view class="invoice-list">
                <view
                    v-for="item in dataList"
                    :key="item.id"
                    class="invoice-item"
                    @click="goDetail(item.id)"
                >
                    <view class="item-header">
                        <text class="order-sn">{{ item.order_sn }}</text>
                        <text class="status">{{ item.invoice_status_desc }}</text>
                    </view>
                    <view class="item-body">
                        <view class="info-row">
                            <text class="label">抬头</text>
                            <text class="value ellipsis">{{ item.invoice_title || '—' }}</text>
                        </view>
                        <view class="info-row" v-if="item.invoice_amount != null && item.invoice_amount !== ''">
                            <text class="label">开票金额</text>
                            <text class="value price">¥{{ item.invoice_amount }}</text>
                        </view>
                        <view class="info-row">
                            <text class="label">申请时间</text>
                            <text class="value">{{ item.create_at || '—' }}</text>
                        </view>
                    </view>
                    <view class="item-foot">
                        <text class="link">查看详情</text>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getTblOrderInvoicePages } from '@/home/api/tbl-order/tblOrderInvoice'

const pagingRef = ref<any>(null)
const dataList = ref<any[]>([])
const filterOrderId = ref(0)
const statusIndex = ref(0)

const statusTabs = [
    { label: '全部', value: '' },
    { label: '待处理', value: 1 },
    { label: '处理中', value: 2 },
    { label: '已开票', value: 3 },
    { label: '已驳回', value: 4 },
    { label: '已作废', value: 5 },
]

const switchStatus = (index: number) => {
    if (statusIndex.value === index) return
    statusIndex.value = index
    pagingRef.value?.reload()
}

const queryList = async (page_current: number, page_size: number) => {
    try {
        const tab = statusTabs[statusIndex.value]
        const params: Record<string, any> = {
            page_current,
            page_size,
        }
        if (tab.value !== '') {
            params.invoice_status = tab.value
        }
        if (filterOrderId.value > 0) {
            params.order_id = filterOrderId.value
        }
        const result = await getTblOrderInvoicePages(params)
        const rows = result?.data?.data ?? result?.data ?? []
        pagingRef.value?.complete(Array.isArray(rows) ? rows : [])
    } catch (e) {
        console.error('开票记录列表加载失败', e)
        pagingRef.value?.complete(false)
        uni.showToast({ title: '加载失败', icon: 'none' })
    }
}

const goDetail = (id: number) => {
    uni.navigateTo({
        url: `/home/pages/user/order/invoice-detail?id=${id}`,
    })
}

onLoad((options: any) => {
    const oid = options?.order_id ? parseInt(options.order_id, 10) : 0
    filterOrderId.value = oid > 0 ? oid : 0
})
</script>

<style scoped lang="scss">
/* 与订单列表 order/index 一致：灰底 + 白卡片 10rpx 圆角 + 同款阴影 */
.invoice-list-page {
    min-height: 100vh;
    background-color: #f5f5f5;
}

.top-card {
    margin: 20rpx;
    background-color: #fff;
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.filter-tip {
    padding: 16rpx 20rpx;
    font-size: 24rpx;
    color: #888;
    border-bottom: 1rpx solid #f0f0f0;
}

.invoice-tabs-scroll {
    width: 100%;
}

.invoice-tabs-inner {
    display: inline-flex;
    padding: 16rpx 12rpx;
    white-space: nowrap;
}

.invoice-tab-item {
    display: inline-block;
    padding: 8rpx 20rpx;
    margin: 0 6rpx;
    font-size: 26rpx;
    color: #666;
    border-radius: 24rpx;
    background: #f2f2f2;
}

.invoice-tab-item.active {
    color: #ff6034;
    background: rgba(255, 96, 52, 0.12);
    font-weight: bold;
}

.invoice-list {
    padding: 20rpx;
    margin-bottom: 20rpx;
}

.invoice-item {
    background-color: #fff;
    border-radius: 10rpx;
    margin-bottom: 20rpx;
    padding: 20rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20rpx;
    border-bottom: 1rpx solid #f0f0f0;
}

.order-sn {
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
}

.status {
    font-size: 26rpx;
    color: #ff6034;
}

.item-body {
    padding-top: 12rpx;
}

.info-row {
    display: flex;
    margin-bottom: 10rpx;
    font-size: 26rpx;
    align-items: flex-start;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-row .label {
    color: #999;
    min-width: 140rpx;
    flex-shrink: 0;
}

.info-row .value {
    color: #333;
    flex: 1;
}

.info-row .value.price {
    color: #ff6034;
    font-weight: bold;
}

.ellipsis {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-foot {
    padding-top: 16rpx;
    text-align: right;
    border-top: 1rpx solid #f5f5f5;
    margin-top: 12rpx;
}

.link {
    font-size: 26rpx;
    color: #ff6034;
}
</style>
