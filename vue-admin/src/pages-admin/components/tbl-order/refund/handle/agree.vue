<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="700px" :destroy-on-close="true">

        <el-form :model="formData" label-width="100px" ref="formRef" v-loading="loading">
            <el-form-item label="申请退款金额">
                <el-input v-model="refundInfo.apply_amount" placeholder="申请退款金额" disabled />
            </el-form-item>
            <el-form-item label="同意备注" prop="agree_reason">
                <el-input v-model="formData.agree_reason" placeholder="可填写同意退款的原因或备注（选填）" type="textarea" :rows="3" maxlength="255" show-word-limit />
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" :loading="loading" @click="handleSubmit">确认</el-button>
            </span>
        </template>
    </el-dialog>
</template>


<script lang="ts" setup>
import type { FormInstance } from 'element-plus';
import {  reactive, ref } from 'vue';

import { agreeTblOrderRefund } from '@/pages-admin/main/api/tbl-order/tblOrderRefund'


const dialogVisible = ref(false)
const loading = ref(false)
let popTitle: string = ''


const refundInfo = reactive({
    'apply_amount': '',
})



const formData = reactive({
    'refund_id': '',
    'agree_reason': '',
})




const formRef = ref<FormInstance>()





const emit = defineEmits(['complete'])
const handleSubmit = async () => {
    if (loading.value || !formRef.value) return
    await formRef.value.validate()

    loading.value = true
    agreeTblOrderRefund(formData).then(res => {
        loading.value = false
        dialogVisible.value = false
        emit('complete')
    }).catch(() => {
        loading.value = false
    })
}



const setDialogData = async (row: any = null) => {
    loading.value = true
    popTitle = '同意退款'

    refundInfo.apply_amount = row.apply_amount

    formData.refund_id = row.id
    formData.agree_reason = ''

    loading.value = false
}

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})




</script>
