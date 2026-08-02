<template>
    <view class="invoice-detail-page" v-if="loaded">
        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="info" size="18" color="#333"></uni-icons>
                <text class="card-title">开票状态</text>
            </view>
            <view class="info-item">
                <text class="info-label">开票状态</text>
                <text class="info-value highlight">{{ detail.invoice_status_desc }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">订单编号</text>
                <text class="info-value">{{ detail.order_sn }}</text>
            </view>
            <view class="info-item" v-if="detail.invoice_amount != null && detail.invoice_amount !== ''">
                <text class="info-label">开票金额</text>
                <text class="info-value">¥{{ detail.invoice_amount }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">申请时间</text>
                <text class="info-value">{{ detail.create_at || '—' }}</text>
            </view>
            <view class="info-item" v-if="detail.update_at">
                <text class="info-label">更新时间</text>
                <text class="info-value">{{ detail.update_at || '—' }}</text>
            </view>
        </view>

        <view class="ds-card">
            <view class="card-header">
                <uni-icons type="shop" size="18" color="#333"></uni-icons>
                <text class="card-title">发票信息</text>
            </view>
            <view class="info-item">
                <text class="info-label">抬头类型</text>
                <text class="info-value">{{ detail.invoice_type === 2 ? '企业' : '个人' }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">发票种类</text>
                <text class="info-value">{{ detail.invoice_kind === 2 ? '增值税专票' : '普通发票' }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">发票抬头</text>
                <text class="info-value">{{ detail.invoice_title || '—' }}</text>
            </view>
            <view class="info-item" v-if="detail.tax_number">
                <text class="info-label">税号</text>
                <text class="info-value">{{ detail.tax_number }}</text>
            </view>
            <template v-if="detail.invoice_kind === 2">
                <view class="info-item" v-if="detail.register_address">
                    <text class="info-label">注册地址</text>
                    <text class="info-value">{{ detail.register_address }}</text>
                </view>
                <view class="info-item" v-if="detail.register_phone">
                    <text class="info-label">注册电话</text>
                    <text class="info-value">{{ detail.register_phone }}</text>
                </view>
                <view class="info-item" v-if="detail.bank_name">
                    <text class="info-label">开户银行</text>
                    <text class="info-value">{{ detail.bank_name }}</text>
                </view>
                <view class="info-item" v-if="detail.bank_account">
                    <text class="info-label">银行账号</text>
                    <text class="info-value">{{ detail.bank_account }}</text>
                </view>
            </template>
            <view class="info-item" v-if="detail.receiver_email">
                <text class="info-label">收票邮箱</text>
                <text class="info-value">{{ detail.receiver_email }}</text>
            </view>
            <view class="info-item" v-if="detail.receiver_mobile">
                <text class="info-label">收票手机</text>
                <text class="info-value">{{ detail.receiver_mobile }}</text>
            </view>
            <view class="info-item" v-if="detail.apply_remark">
                <text class="info-label">申请说明</text>
                <text class="info-value">{{ detail.apply_remark }}</text>
            </view>
        </view>

        <view class="ds-card" v-if="detail.reject_reason">
            <view class="card-header">
                <uni-icons type="info" size="18" color="#c00"></uni-icons>
                <text class="card-title">驳回说明</text>
            </view>
            <view class="reject-text">{{ detail.reject_reason }}</view>
            <view class="info-item" v-if="detail.reject_time">
                <text class="info-label">驳回时间</text>
                <text class="info-value">{{ detail.reject_time || '—' }}</text>
            </view>
        </view>

        <view class="ds-card" v-if="detail.void_reason || isInvoiceVoided">
            <view class="card-header">
                <uni-icons type="info" size="18" color="#999"></uni-icons>
                <text class="card-title">作废说明</text>
            </view>
            <view class="void-text">{{ detail.void_reason || '—' }}</view>
            <view class="info-item" v-if="detail.void_time">
                <text class="info-label">作废时间</text>
                <text class="info-value">{{ detail.void_time || '—' }}</text>
            </view>
        </view>

        <view class="ds-card" v-if="detail.invoice_no || detail.out_invoice_no">
            <view class="card-header">
                <uni-icons type="wallet" size="18" color="#333"></uni-icons>
                <text class="card-title">{{ isInvoiceVoided ? '历史票面（已作废）' : '票据信息' }}</text>
            </view>
            <view v-if="isInvoiceVoided" class="ticket-hint">以下为作废前开票记录，仅供参考</view>
            <view class="info-item" v-if="detail.invoice_no">
                <text class="info-label">发票号码</text>
                <text class="info-value">{{ detail.invoice_no }}</text>
            </view>
            <view class="info-item" v-if="detail.out_invoice_no">
                <text class="info-label">外部流水号</text>
                <text class="info-value">{{ detail.out_invoice_no }}</text>
            </view>
            <view class="info-item" v-if="detail.issue_time">
                <text class="info-label">开票完成时间</text>
                <text class="info-value">{{ detail.issue_time || '—' }}</text>
            </view>
        </view>

        <view class="ds-card" v-if="detail.invoice_file_url">
            <view class="card-header">
                <uni-icons type="link" size="18" color="#333"></uni-icons>
                <text class="card-title">电子发票</text>
            </view>
            <button class="ds-btn-md file-btn" @click="openInvoiceFile">复制下载链接</button>
        </view>

        <view class="ds-card" v-if="logList.length > 0">
            <view class="card-header">
                <uni-icons type="list" size="18" color="#333"></uni-icons>
                <text class="card-title">处理记录</text>
            </view>
            <view v-for="(log, idx) in logList" :key="idx" class="log-item">
                <view class="log-line">
                    <text class="log-status">{{ log.invoice_status_desc }}</text>
                    <text class="log-time">{{ log.create_at || '—' }}</text>
                </view>
                <view class="log-msg" v-if="log.message">{{ log.message }}</view>
            </view>
        </view>

        <view class="bottom-space" />
    </view>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getTblOrderInvoiceInfo } from '@/home/api/tbl-order/tblOrderInvoice'
import { formatImageUrl } from '@/utils/image'

const invoiceId = ref(0)
const loaded = ref(false)
const detail = reactive<Record<string, any>>({
    orderInvoiceLogList: [],
})

const logList = computed(() => {
    const list = detail.orderInvoiceLogList
    return Array.isArray(list) ? list : []
})

/** 5 = 已作废，与 TblOrderInvoiceEnum::STATUS_VOIDED 一致 */
const isInvoiceVoided = computed(() => Number(detail.invoice_status) === 5)

const resolveFileUrl = (url: string) => {
    if (!url) return ''
    if (url.startsWith('http://') || url.startsWith('https://')) return url
    return formatImageUrl(url, undefined, 'default')
}

const openInvoiceFile = () => {
    const url = resolveFileUrl(detail.invoice_file_url || '')
    if (!url) {
        uni.showToast({ title: '暂无文件地址', icon: 'none' })
        return
    }
    uni.setClipboardData({
        data: url,
        success: () => {
            uni.showToast({ title: '链接已复制，可到浏览器打开', icon: 'none' })
        },
    })
}

const loadDetail = async () => {
    try {
        uni.showLoading({ title: '加载中' })
        const res = await getTblOrderInvoiceInfo(invoiceId.value)
        uni.hideLoading()
        if (res.code !== 10000 || !res.data) {
            uni.showToast({ title: res.message || '记录不存在', icon: 'none' })
            setTimeout(() => uni.navigateBack(), 1500)
            return
        }
        Object.assign(detail, res.data)
        loaded.value = true
    } catch (e) {
        uni.hideLoading()
        uni.showToast({ title: '加载失败', icon: 'none' })
        setTimeout(() => uni.navigateBack(), 1500)
    }
}

onLoad((options: any) => {
    const id = options?.id ? parseInt(options.id, 10) : 0
    if (!id) {
        uni.showToast({ title: '参数错误', icon: 'none' })
        setTimeout(() => uni.navigateBack(), 1200)
        return
    }
    invoiceId.value = id
    loadDetail()
})
</script>

<style scoped lang="scss">
/* 与订单详情一致：页面不加水平 padding，由全局 .ds-card 的 margin 控制边距，避免「双边框」过宽 */
.invoice-detail-page {
    min-height: 100vh;
    background-color: #f5f5f5;
    padding-bottom: 40rpx;
}

.highlight {
    color: #ff6034;
    font-weight: 600;
}

.reject-text {
    font-size: 26rpx;
    color: #c00;
    line-height: 1.55;
    padding: 0 0 8rpx;
}

.void-text {
    font-size: 26rpx;
    color: #666;
    line-height: 1.55;
    padding: 0 0 8rpx;
}

.ticket-hint {
    font-size: 24rpx;
    color: #999;
    padding: 0 0 12rpx;
    line-height: 1.4;
}

.file-btn {
    width: 100%;
    margin-top: 4rpx;
}

.log-item {
    padding: 16rpx 0;
    border-bottom: 1rpx solid #f0f0f0;
}

.log-item:last-child {
    border-bottom: none;
}

.log-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 26rpx;
}

.log-status {
    color: #333;
    font-weight: 500;
}

.log-time {
    color: #999;
    font-size: 24rpx;
    flex-shrink: 0;
    margin-left: 16rpx;
}

.log-msg {
    font-size: 26rpx;
    color: #666;
    margin-top: 8rpx;
    line-height: 1.45;
}

.bottom-space {
    height: 24rpx;
}
</style>
