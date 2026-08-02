<template>
  <el-dialog v-model="dialogVisible" title="打印订单" width="500px" :destroy-on-close="true">
    <el-form :model="formData" label-width="90px" ref="formRef" :rules="formRules" v-loading="loading">
      <el-form-item label="订单ID" prop="order_id">
        <el-input v-model.trim="formData.order_id" placeholder="请输入订单ID" clearable class="input-width" />
      </el-form-item>
      <el-form-item label="打印机" prop="printer_id">
        <el-select v-model="formData.printer_id" placeholder="请选择打印机" clearable class="input-width">
          <el-option
            v-for="printer in printerList"
            :key="printer.id"
            :label="`${printer.printer_name} (${printer.printer_sn})`"
            :value="printer.id"
            :disabled="printer.printer_status !== 1"
          />
        </el-select>
      </el-form-item>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="loading" @click="handleSubmit">确认打印</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script lang="ts" setup>
import type { FormInstance } from 'element-plus'
import { computed, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'

import { getTblStorePrinterPages, printTblOrder } from '@/pages-admin/main/api/tbl-store/tblStorePrinter'

const dialogVisible = ref(false)
const loading = ref(false)

const initialFormData = {
  order_id: '',
  printer_id: ''
}

const formData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()

const formRules = computed(() => ({
  order_id: [{ required: true, message: '请输入订单ID', trigger: 'blur' }],
  printer_id: [{ required: true, message: '请选择打印机', trigger: 'blur' }]
}))

const emit = defineEmits(['complete'])

const printerList = ref<Record<string, any>[]>([])

const getPrinterList = async () => {
  try {
    const res = await getTblStorePrinterPages({
      page_current: 1,
      page_size: 1000
    })
    if (res.code === 10000) {
      printerList.value = res.data.data || []
    }
  } catch (error) {
    console.error('获取打印机列表失败:', error)
  }
}

const handleSubmit = async () => {
  if (loading.value || !formRef.value) return
  await formRef.value.validate()

  loading.value = true
  try {
    await printTblOrder(formData)
    ElMessage.success('打印成功')
    dialogVisible.value = false
    emit('complete')
  } catch (error) {
    console.error('打印失败:', error)
  } finally {
    loading.value = false
  }
}

defineExpose({
  openDialog: () => {
    dialogVisible.value = true
    getPrinterList()
    Object.assign(formData, initialFormData)
  }
})
</script>

<style scoped>
.input-width {
  width: 100%;
}
</style>
