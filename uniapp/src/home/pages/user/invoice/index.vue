<template>
    <view class="invoice-container">
        <view v-if="invoiceList.length === 0" class="empty-list">
            <image src="/static/images/empty/empty.png" mode="aspectFit" class="empty-image"></image>
            <text class="empty-text">暂无发票信息，请添加~</text>
        </view>
        <view v-else class="invoice-list">
            <view v-for="invoice in invoiceList" :key="invoice.id" class="invoice-item">
                <view class="invoice-info">
                    <view class="invoice-main">
                        <view class="default-switch" @click="setDefault(invoice.id)">
                            <uni-icons :type="invoice.is_default ? 'checkbox-filled' : 'checkbox'" size="24"
                                :color="invoice.is_default ? '#FF6034' : '#ddd'"></uni-icons>
                        </view>
                        <text class="title">{{ invoice.invoice_title }}</text>
                        <text v-if="invoice.is_default" class="default-tag">默认</text>
                    </view>
                    <view class="invoice-detail">
                        <text>{{ invoice.invoice_type === 1 ? '个人' : '企业' }} / {{ invoice.invoice_kind === 1 ? '普通发票' : '增值税专票' }}</text>
                    </view>
                    <view class="invoice-detail" v-if="invoice.tax_number">
                        <text>税号：{{ invoice.tax_number }}</text>
                    </view>
                    <view class="invoice-detail" v-if="invoice.receiver_email">
                        <text>邮箱：{{ invoice.receiver_email }}</text>
                    </view>
                    <view class="invoice-detail" v-if="invoice.receiver_mobile">
                        <text>手机：{{ invoice.receiver_mobile }}</text>
                    </view>
                </view>
                <view class="divider"></view>
                <view class="actions">
                    <view class="action-btn edit" @click="editInvoice(invoice.id)">
                        <uni-icons type="compose" size="16" color="#FF6034"></uni-icons>
                        <text>编辑</text>
                    </view>
                    <view class="action-btn delete" @click="deleteInvoice(invoice.id)">
                        <uni-icons type="trash" size="16" color="#999"></uni-icons>
                        <text>删除</text>
                    </view>
                </view>
            </view>
        </view>

        <view class="add-invoice-btn" @click="addInvoice">
            <uni-icons type="plusempty" size="20" color="#fff"></uni-icons>
            <text>新增发票信息</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { getUserInvoiceList, deleteUserInvoice, setDefaultUserInvoice } from '@/home/api/user/userInvoice'

interface InvoiceItem {
    id: number
    invoice_type: number
    invoice_kind: number
    invoice_title: string
    tax_number: string
    receiver_email: string
    receiver_mobile: string
    is_default: number
}

const invoiceList = ref<InvoiceItem[]>([])

const loadInvoiceList = () => {
    getUserInvoiceList().then((res) => {
        if (res.code === 10000) {
            invoiceList.value = res.data || []
        } else {
            uni.showToast({
                title: res.message || '加载失败',
                icon: 'none'
            })
        }
    })
}

const editInvoice = (id: number) => {
    goToPage({ url: `/home/pages/user/invoice/edit?id=${id}` })
}

const addInvoice = () => {
    goToPage({ url: '/home/pages/user/invoice/edit' })
}

const deleteInvoice = (id: number) => {
    uni.showModal({
        title: '提示',
        content: '确定要删除该发票信息吗？',
        success: (modalRes) => {
            if (!modalRes.confirm) return
            deleteUserInvoice(id).then((res) => {
                if (res.code === 10000) {
                    uni.showToast({
                        title: '删除成功',
                        icon: 'success'
                    })
                    loadInvoiceList()
                } else {
                    uni.showToast({
                        title: res.message || '删除失败',
                        icon: 'none'
                    })
                }
            })
        }
    })
}

const setDefault = (id: number) => {
    setDefaultUserInvoice(id).then((res) => {
        if (res.code === 10000) {
            invoiceList.value.forEach((invoice) => {
                invoice.is_default = invoice.id === id ? 1 : 0
            })
        } else {
            uni.showToast({
                title: res.message || '设置失败',
                icon: 'none'
            })
        }
    })
}

onShow(() => {
    loadInvoiceList()
})
</script>

<style lang="scss" scoped>
.invoice-container {
    min-height: 100vh;
    background: #f8f8f8;
    padding: 20rpx 30rpx;
    box-sizing: border-box;
    padding-bottom: 140rpx;
}

.empty-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-top: 200rpx;

    .empty-image {
        width: 240rpx;
        height: 240rpx;
        margin-bottom: 30rpx;
    }

    .empty-text {
        font-size: 28rpx;
        color: #999;
    }
}

.invoice-list {
    .invoice-item {
        margin-bottom: 20rpx;
        border-radius: 16rpx;
        background: #fff;
        overflow: hidden;

        .invoice-info {
            padding: 30rpx;
        }

        .invoice-main {
            display: flex;
            align-items: center;
            margin-bottom: 16rpx;

            .default-switch {
                padding: 10rpx;
            }

            .title {
                font-size: 32rpx;
                color: #333;
                font-weight: 600;
                margin-right: 20rpx;
            }

            .default-tag {
                font-size: 22rpx;
                color: #FF6034;
                background: rgba(255, 96, 52, 0.1);
                padding: 4rpx 16rpx;
                border-radius: 20rpx;
            }
        }

        .invoice-detail {
            font-size: 26rpx;
            color: #666;
            line-height: 1.5;
            margin-top: 6rpx;
        }

        .divider {
            height: 2rpx;
            background: #f5f5f5;
            margin: 0 30rpx;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 20rpx 30rpx;
            gap: 30rpx;

            .action-btn {
                display: flex;
                align-items: center;
                gap: 6rpx;
                padding: 10rpx 20rpx;
                border-radius: 26rpx;

                text {
                    font-size: 26rpx;
                }

                &.edit {
                    background: rgba(255, 96, 52, 0.1);

                    text {
                        color: #FF6034;
                    }
                }

                &.delete {
                    background: #f7f7f7;

                    text {
                        color: #999;
                    }
                }
            }
        }
    }
}

.add-invoice-btn {
    position: fixed;
    left: 30rpx;
    right: 30rpx;
    bottom: 40rpx;
    height: 88rpx;
    background: linear-gradient(135deg, #FF6034, #FF8C6A);
    border-radius: 44rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10rpx;
    color: #fff;
    font-size: 30rpx;
    font-weight: 500;
}
</style>
