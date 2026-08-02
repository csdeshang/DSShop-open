<template>
    <view>
        <z-paging ref="paging" v-model="dataList" @query="queryList">

            <!-- 订单状态选项卡 - 使用z-paging的fixed特性 -->
            <template v-slot:top>
                <view class="order-tabs">
                    <!-- 全部选项 -->
                    <view class="tab-item" :class="{ active: currentTab === '' }" @click="switchTab('')">
                        全部
                    </view>
                    
                    <!-- 动态渲染状态选项 -->
                    <view v-for="option in commission_status_options" :key="option.value" 
                        class="tab-item" :class="{ active: currentTab === String(option.value) }" 
                        @click="switchTab(String(option.value))">
                        {{ option.label }}
                    </view>
                </view>
            </template>
         


            <view class="balance-list">
                <view class="order-item" v-for="item in dataList" :key="item.id">
                    <view class="item-header">
                        <view class="type-badge" :class="'type-' + item.commission_type">
                            {{ item.commission_type_desc }}
                        </view>
                        <view class="amount" :class="['status-' + item.commission_status]">
                            {{ item.commission_amount }}
                        </view>
                    </view>

                    <view class="item-body">
                        <view class="info-row">
                            <text class="label">订单状态：</text>
                            <text class="value">{{ item.commission_status_desc }}</text>
                        </view>

                        <view class="info-row">
                            <text class="label">支付金额：</text>
                            <text class="value">{{ item.pay_price / 100 }}元</text>
                        </view>

                        <view class="info-row">
                            <text class="label">备注：</text>
                            <text class="value">{{ item.commission_remark }}</text>
                        </view>

                        <view class="info-row">
                            <text class="label">时间：</text>
                            <text class="value">{{ item.create_at }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getDistributorOrderPages } from '@/home/api/distributor/distributor'
import { useEnum } from '@/hooks/useEnum'

// 使用枚举 Hook
const { options: commission_status_options } = useEnum('default.distributor_order.commission_status')

interface StatusOption {
    label: string;
    value: number;
}

interface OrderItem {
    id: number;
    order_id: number;
    order_goods_id: number;
    distributor_user_id: number;
    user_id: number;
    pay_price: number;
    distributor_level_id: number;
    commission_amount: number;
    commission_type: string;
    commission_type_desc: string;
    commission_status: number;
    commission_status_desc: string;
    commission_remark: string;
    create_at: string;
    update_at: string;
}

// 当前选中的选项卡 (空字符串表示全部)
const currentTab = ref('')

// 切换选项卡
const switchTab = (status: string) => {
    if (currentTab.value !== status) {
        currentTab.value = status
        // 使用z-paging提供的刷新方法
        if (paging.value) {
            paging.value.reload()
        }
    }
}

const paging = ref<any>(null)
const dataList = ref<OrderItem[]>([])

const queryList = async (page_current: number, page_size: number) => {
    try {
        // 根据当前选中的tab获取对应状态的数据
        const params: any = {
            page_current,
            page_size,
        }
        
        // 如果选择了特定状态（非全部）
        if (currentTab.value !== '') {
            params.commission_status = currentTab.value
        }
        
        const result = await getDistributorOrderPages(params)
        paging.value?.complete(result.data.data)
    } catch (e) {
        console.log('下拉加载', e)
        paging.value?.complete(false)
    }
}
</script>

<style scoped lang="scss">
.order-tabs {
    display: flex;
    background-color: #fff;
    padding: 20rpx 0;
    border-radius: 10rpx;
    margin-bottom: 10rpx;
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
        color: #2196f3;
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
        background-color: #2196f3;
        border-radius: 2rpx;
    }
}

.balance-list {
    padding: 10px;
}

.order-item {
    background-color: #fff;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f5f5f5;
}

.type-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    background-color: #e8f5e9;
    color: #4caf50;

    &.type-self {
        background-color: #e3f2fd;
        color: #2196f3;
    }
}

.amount {
    font-size: 16px;
    font-weight: bold;

    &.status-0 {
        color: #ff9800; // 待付款
    }

    &.status-1 {
        color: #2196f3; // 待结算
    }

    &.status-2 {
        color: #4caf50; // 已结算
    }

    &.status-3 {
        color: #9e9e9e; // 已失效
    }
    
    &.status-4 {
        color: #f44336; // 已退款
    }
}

.item-body {
    padding: 12px 15px;
}

.info-row {
    display: flex;
    margin-bottom: 8px;
    font-size: 14px;

    &:last-child {
        margin-bottom: 0;
    }

    .label {
        color: #666;
        width: 80px;
        flex-shrink: 0;
    }

    .value {
        color: #333;
        flex: 1;
    }
}
</style>