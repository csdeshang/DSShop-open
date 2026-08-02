<template>
    <view>
        <view class="ds-card" v-if="orderDetail.id">
            <view class="card-header">
                <text class="card-title">订单信息</text>
            </view>
            <view class="info-item">
                <text class="info-label">订单编号</text>
                <text class="info-value">{{ orderDetail.order_sn }}</text>
            </view>
            <view class="info-item">
                <text class="info-label">实付款</text>
                <text class="info-value">¥{{ orderDetail.pay_amount }}</text>
            </view>
        </view>

        <!-- 第一步：仅选择来源，不展示表单 -->
        <view class="ds-card" v-if="orderDetail.id && !showInvoiceForm">
            <view class="card-header">
                <text class="card-title">选择开票信息</text>
            </view>
            <text class="choice-tip">请从常用发票中选一条，或选择手动填写；确认后将展示完整信息供核对与提交。</text>
            <view v-if="templateList.length > 0">
                <picker mode="selector" :range="templateList" range-key="label" @change="onTemplatePick">
                    <view class="picker-line">
                        <text>从常用发票选择</text>
                        <text class="picker-hint">点击选择</text>
                    </view>
                </picker>
            </view>
            <view v-else class="choice-empty">暂无常用发票，请使用手动填写。</view>
            <view class="manual-fill-wrap">
                <button class="ds-btn-md manual-fill-btn" @click="startManualFill">手动填写</button>
            </view>
        </view>

        <!-- 第二步：展示表单后提交 -->
        <template v-if="showInvoiceForm">
        <view class="ds-card form-source-card" v-if="sourceSummary">
            <view class="info-item">
                <text class="info-label">当前方式</text>
                <text class="info-value">{{ sourceSummary }}</text>
            </view>
            <text class="rechoose-link" @click="goBackToChoose">重新选择抬头</text>
        </view>

        <view class="ds-field-line">
            <text class="field-name">抬头类型</text>
            <view class="field-info type-tags">
                <view class="type-tag" :class="{ active: form.invoice_type === 1 }" @click="form.invoice_type = 1">个人</view>
                <view class="type-tag" :class="{ active: form.invoice_type === 2 }" @click="form.invoice_type = 2">企业</view>
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">发票种类</text>
            <view class="field-info type-tags">
                <view class="type-tag" :class="{ active: form.invoice_kind === 1 }" @click="form.invoice_kind = 1">普通发票</view>
                <view class="type-tag" :class="{ active: form.invoice_kind === 2 }" @click="form.invoice_kind = 2">增值税专票</view>
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">发票抬头</text>
            <input class="field-input" v-model="form.invoice_title" placeholder="请输入发票抬头" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">税号</text>
            <input class="field-input" v-model="form.tax_number" placeholder="企业必填" />
        </view>

        <view v-if="form.invoice_kind === 2">
            <view class="ds-field-line">
                <text class="field-name">注册地址</text>
                <input class="field-input" v-model="form.register_address" placeholder="请输入注册地址" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">注册电话</text>
                <input class="field-input" v-model="form.register_phone" placeholder="请输入注册电话" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">开户银行</text>
                <input class="field-input" v-model="form.bank_name" placeholder="请输入开户银行" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">银行账号</text>
                <input class="field-input" v-model="form.bank_account" placeholder="请输入银行账号" />
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">收票邮箱</text>
            <input class="field-input" v-model="form.receiver_email" placeholder="选填" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">收票手机</text>
            <input class="field-input" v-model="form.receiver_mobile" placeholder="选填" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">申请说明</text>
            <textarea class="field-textarea" v-model="form.apply_remark" placeholder="选填" />
        </view>

        <view class="ml-20 mr-20 mb-40">
            <button class="ds-btn-lg" type="warn" @click="submitApply">{{ submitButtonText }}</button>
        </view>
        </template>
    </view>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getTblOrderInfo } from '@/home/api/tbl-order/tblOrder'
import {
    applyTblOrderInvoice,
    INVOICE_STATUS_NOT_SUBMITTED,
    INVOICE_STATUS_REJECTED,
    INVOICE_STATUS_VOIDED,
} from '@/home/api/tbl-order/tblOrderInvoice'
import { getUserInvoiceList } from '@/home/api/user/userInvoice'

const orderId = ref(0)
const orderDetail = reactive({
    id: 0,
    order_sn: '',
    pay_amount: '',
    invoice_status: INVOICE_STATUS_NOT_SUBMITTED,
    user_available_actions: [] as string[],
})

const submitButtonText = computed(() => {
    const s = Number(orderDetail.invoice_status)
    if (s === INVOICE_STATUS_REJECTED || s === INVOICE_STATUS_VOIDED) {
        return '重新提交开票申请'
    }
    return '提交开票申请'
})

const form = reactive({
    order_id: 0,
    invoice_type: 1,
    invoice_kind: 1,
    invoice_title: '',
    tax_number: '',
    register_address: '',
    register_phone: '',
    bank_name: '',
    bank_account: '',
    receiver_email: '',
    receiver_mobile: '',
    apply_remark: '',
})

const templateList = ref<{ label: string; raw: Record<string, any> }[]>([])

/** 是否已选来源并展示表单（选择常用 / 手动填写） */
const showInvoiceForm = ref(false)
/** 第二步顶部摘要：当前方式说明 */
const sourceSummary = ref('')

const fillFormFromInvoiceRow = (row: Record<string, any>) => {
    form.invoice_type = Number(row.invoice_type) || 1
    form.invoice_kind = Number(row.invoice_kind) || 1
    form.invoice_title = row.invoice_title || ''
    form.tax_number = row.tax_number || ''
    form.register_address = row.register_address || ''
    form.register_phone = row.register_phone || ''
    form.bank_name = row.bank_name || ''
    form.bank_account = row.bank_account || ''
    form.receiver_email = row.receiver_email || ''
    form.receiver_mobile = row.receiver_mobile || ''
}

const loadOrder = async () => {
    const res = await getTblOrderInfo(orderId.value)
    if (res.code !== 10000 || !res.data) {
        uni.showToast({ title: '订单不存在', icon: 'none' })
        setTimeout(() => uni.navigateBack(), 1200)
        return
    }
    Object.assign(orderDetail, res.data)
    if (!res.data.user_available_actions?.includes('invoice')) {
        uni.showToast({ title: '当前不可申请开票', icon: 'none' })
        setTimeout(() => uni.navigateBack(), 1500)
        return
    }
    form.order_id = orderId.value
}

const loadTemplates = async () => {
    try {
        const res = await getUserInvoiceList()
        if (res.code === 10000 && Array.isArray(res.data)) {
            templateList.value = res.data.map((row: any) => ({
                label: row.title_name || row.invoice_title || `发票#${row.id}`,
                raw: row,
            }))
        }
    } catch {
        templateList.value = []
    }
}

/** 第一步：从常用发票选择后进入第二步 */
const onTemplatePick = (e: any) => {
    const idx = Number(e.detail.value)
    const item = templateList.value[idx]
    const row = item?.raw
    if (!row) return
    fillFormFromInvoiceRow(row)
    sourceSummary.value = item.label || '常用发票'
    showInvoiceForm.value = true
}

const resetFormFieldsOnly = () => {
    form.invoice_type = 1
    form.invoice_kind = 1
    form.invoice_title = ''
    form.tax_number = ''
    form.register_address = ''
    form.register_phone = ''
    form.bank_name = ''
    form.bank_account = ''
    form.receiver_email = ''
    form.receiver_mobile = ''
    form.apply_remark = ''
}

/** 第一步：手动填写，进入第二步展示空表单 */
const startManualFill = () => {
    resetFormFieldsOnly()
    form.order_id = orderId.value
    sourceSummary.value = '手动填写'
    showInvoiceForm.value = true
}

/** 返回第一步并清空 */
const goBackToChoose = () => {
    uni.showModal({
        title: '提示',
        content: '将清空当前填写内容并返回上一步，确定吗？',
        success: (res) => {
            if (!res.confirm) return
            resetFormFieldsOnly()
            form.order_id = orderId.value
            sourceSummary.value = ''
            showInvoiceForm.value = false
        },
    })
}

/** 提交成功后：从详情来的返回详情（onShow 会刷新）；否则打开订单详情，避免 navigateBack 落到无关页 */
const goAfterInvoiceSubmitSuccess = () => {
    const pages = getCurrentPages()
    if (pages.length >= 2) {
        const prev = pages[pages.length - 2] as { route?: string }
        const route = prev.route || ''
        if (route.includes('order/detail')) {
            uni.navigateBack({ delta: 1 })
            return
        }
    }
    const url = `/home/pages/user/order/detail?id=${orderId.value}`
    uni.redirectTo({
        url,
        fail: () => {
            uni.navigateBack()
        },
    })
}

const validateForm = () => {
    if (!form.invoice_title?.trim()) {
        uni.showToast({ title: '请填写发票抬头', icon: 'none' })
        return false
    }
    if (form.invoice_type === 2 && !form.tax_number?.trim()) {
        uni.showToast({ title: '企业抬头请填写税号', icon: 'none' })
        return false
    }
    if (form.receiver_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.receiver_email)) {
        uni.showToast({ title: '收票邮箱格式不正确', icon: 'none' })
        return false
    }
    if (form.receiver_mobile && !/^1\d{10}$/.test(form.receiver_mobile)) {
        uni.showToast({ title: '收票手机格式不正确', icon: 'none' })
        return false
    }
    return true
}

const submitApply = async () => {
    if (!showInvoiceForm.value) {
        uni.showToast({ title: '请先选择开票信息', icon: 'none' })
        return
    }
    if (!validateForm()) return
    try {
        uni.showLoading({ title: '提交中' })
        const res = await applyTblOrderInvoice({ ...form, order_id: orderId.value })
        uni.hideLoading()
        if (res.code === 10000) {
            const okTitle =
                orderDetail.invoice_status === INVOICE_STATUS_REJECTED ||
                orderDetail.invoice_status === INVOICE_STATUS_VOIDED
                    ? '已重新提交'
                    : '已提交申请'
            uni.showToast({ title: okTitle, icon: 'success' })
            setTimeout(() => goAfterInvoiceSubmitSuccess(), 1200)
        } else {
            uni.showToast({ title: res.msg || res.message || '提交失败', icon: 'none' })
        }
    } catch (err: any) {
        uni.hideLoading()
        uni.showToast({ title: err?.msg || err?.message || '提交失败', icon: 'none' })
    }
}

onLoad(async (options: any) => {
    const id = options?.order_id ? parseInt(options.order_id, 10) : 0
    if (!id) {
        uni.showToast({ title: '参数错误', icon: 'none' })
        setTimeout(() => uni.navigateBack(), 1200)
        return
    }
    orderId.value = id
    await loadTemplates()
    await loadOrder()
})
</script>

<style lang="scss" scoped>
.card-title {
    font-size: 30rpx;
    font-weight: 600;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12rpx 0;
    font-size: 28rpx;
}

.info-label {
    color: #888;
}

.picker-line {
    display: flex;
    justify-content: space-between;
    padding: 20rpx 0;
    font-size: 28rpx;
}

.picker-hint {
    color: #ff6034;
}

.type-tags {
    display: flex;
    gap: 12rpx;
}

.type-tag {
    padding: 8rpx 18rpx;
    border-radius: 8rpx;
    background: #f2f2f2;
    color: #666;
    font-size: 24rpx;
}

.type-tag.active {
    color: #ff6034;
    background: rgba(255, 96, 52, 0.12);
}

.field-textarea {
    min-height: 120rpx;
    width: 100%;
    font-size: 28rpx;
}

.choice-tip {
    display: block;
    font-size: 26rpx;
    color: #888;
    line-height: 1.5;
    padding-bottom: 16rpx;
}

.choice-empty {
    font-size: 26rpx;
    color: #888;
    padding: 12rpx 0 20rpx;
}

.manual-fill-wrap {
    padding-top: 24rpx;
}

.manual-fill-btn {
    width: 100%;
    background: #ff6034;
    color: #fff;
    border: none;
}

.form-source-card {
    .rechoose-link {
        display: block;
        font-size: 26rpx;
        color: #ff6034;
        padding-top: 12rpx;
    }
}
</style>
