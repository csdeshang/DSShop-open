<template>
    <view>
        <view class="ds-field-line">
            <text class="field-name">备注名</text>
            <input class="field-input" v-model="invoice.title_name" placeholder="如：公司抬头/个人" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">抬头类型</text>
            <view class="field-info type-tags">
                <view class="type-tag" :class="{ active: invoice.invoice_type === 1 }" @click="invoice.invoice_type = 1">个人</view>
                <view class="type-tag" :class="{ active: invoice.invoice_type === 2 }" @click="invoice.invoice_type = 2">企业</view>
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">发票种类</text>
            <view class="field-info type-tags">
                <view class="type-tag" :class="{ active: invoice.invoice_kind === 1 }" @click="invoice.invoice_kind = 1">普通发票</view>
                <view class="type-tag" :class="{ active: invoice.invoice_kind === 2 }" @click="invoice.invoice_kind = 2">增值税专票</view>
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">发票抬头</text>
            <input class="field-input" v-model="invoice.invoice_title" placeholder="请输入发票抬头" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">税号</text>
            <input class="field-input" v-model="invoice.tax_number" placeholder="企业开票建议填写" />
        </view>

        <view v-if="invoice.invoice_kind === 2">
            <view class="ds-field-line">
                <text class="field-name">注册地址</text>
                <input class="field-input" v-model="invoice.register_address" placeholder="请输入注册地址" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">注册电话</text>
                <input class="field-input" v-model="invoice.register_phone" placeholder="请输入注册电话" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">开户银行</text>
                <input class="field-input" v-model="invoice.bank_name" placeholder="请输入开户银行" />
            </view>
            <view class="ds-field-line">
                <text class="field-name">银行账号</text>
                <input class="field-input" v-model="invoice.bank_account" placeholder="请输入银行账号" />
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">收票邮箱</text>
            <input class="field-input" v-model="invoice.receiver_email" placeholder="请输入收票邮箱" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">收票手机</text>
            <input class="field-input" v-model="invoice.receiver_mobile" placeholder="请输入收票手机号" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">设为默认</text>
            <view class="field-info">
                <switch @change="switchChange" :checked="!!invoice.is_default"></switch>
            </view>
        </view>

        <view class="mt-20 mb-20 ml-20 mr-20">
            <button class="ds-btn-lg" type="warn" @click="saveInvoice">{{ submitTitle }}</button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { createUserInvoice, updateUserInvoice, getUserInvoiceInfo } from '@/home/api/user/userInvoice'

let submitTitle = ref('')

const invoice = ref({
    id: '',
    title_name: '',
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
    is_default: false
})

const loadInvoiceData = (id: string) => {
    getUserInvoiceInfo(Number(id)).then((res) => {
        if (res.code === 10000) {
            invoice.value = {
                ...res.data,
                id: String(res.data.id),
                is_default: !!res.data.is_default
            }
        }
    })
}

const validate = () => {
    if (!invoice.value.invoice_title || invoice.value.invoice_title.trim() === '') {
        uni.showToast({ title: '请输入发票抬头', icon: 'none' })
        return false
    }
    if (invoice.value.receiver_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(invoice.value.receiver_email)) {
        uni.showToast({ title: '收票邮箱格式不正确', icon: 'none' })
        return false
    }
    if (invoice.value.receiver_mobile && !/^1[3-9]\d{9}$/.test(invoice.value.receiver_mobile)) {
        uni.showToast({ title: '收票手机号格式不正确', icon: 'none' })
        return false
    }
    return true
}

const saveInvoice = () => {
    if (!validate()) return

    const params = {
        ...invoice.value,
        is_default: invoice.value.is_default ? 1 : 0
    }

    const submitPromise = invoice.value.id && invoice.value.id !== ''
        ? updateUserInvoice(params)
        : createUserInvoice(params)

    submitPromise.then((res) => {
        if (res.code === 10000) {
            uni.showToast({
                title: submitTitle.value + '成功',
                icon: 'success'
            })
            setTimeout(() => {
                goToPage({ url: '/home/pages/user/invoice/index' })
            }, 1200)
        } else {
            uni.showToast({
                title: res.message || '保存失败',
                icon: 'none'
            })
        }
    })
}

const switchChange = (event: any) => {
    invoice.value.is_default = event.detail.value
}

onLoad((options: any) => {
    invoice.value.id = options.id ? String(options.id) : ''
    if (invoice.value.id) {
        loadInvoiceData(invoice.value.id)
        submitTitle.value = '修改发票'
    } else {
        submitTitle.value = '添加发票'
    }
})
</script>

<style lang="scss" scoped>
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
</style>

