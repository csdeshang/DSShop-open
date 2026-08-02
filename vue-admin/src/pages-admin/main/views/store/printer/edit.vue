<template>
  <el-drawer v-model="dialogVisible" :title="popTitle" size="60%">
    <el-scrollbar style="height: 100%">
      <el-form :model="formData" :rules="formRules" label-width="120px" ref="formRef">
        <el-form-item label="打印机名称" prop="printer_name">
          <el-input v-model="formData.printer_name" placeholder="请输入打印机名称" />
        </el-form-item>

        <el-form-item label="打印机编号" prop="printer_sn">
          <el-input v-model="formData.printer_sn" placeholder="请输入打印机编号" />
        </el-form-item>

        <el-form-item label="打印机密钥" prop="printer_key">
          <el-input v-model="formData.printer_key" placeholder="请输入打印机密钥" />
        </el-form-item>

        <el-form-item label="打印机手机号" prop="printer_mobile">
          <el-input v-model="formData.printer_mobile" placeholder="打印机手机号(WIFI模式选填)" />
        </el-form-item>

        <el-form-item label="打印机服务商" prop="printer_provider">
          <el-select v-model="formData.printer_provider" placeholder="请选择打印机服务商" style="width: 100%">
            <el-option v-for="item in printer_provider_options" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>

        <el-form-item label="是否启用" prop="is_enabled">
          <el-radio-group v-model="formData.is_enabled">
            <el-radio :value="0">禁用</el-radio>
            <el-radio :value="1">启用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>

      <div class="flex justify-center mt-[20px]">
        <el-button type="primary" @click="handleSubmit" :loading="loading">保存</el-button>
        <el-button @click="dialogVisible = false">取消</el-button>
      </div>
    </el-scrollbar>
  </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'

import { useEnum } from '@/hooks/useEnum'
import {
  createTblStorePrinter,
  getTblStorePrinterInfo,
  updateTblStorePrinter
} from '@/pages-admin/main/api/tbl-store/tblStorePrinter'

const dialogVisible = ref(false)
const loading = ref(false)
const formRef = ref()
let popTitle = ''

const formData = reactive({
  id: 0,
  printer_name: '',
  printer_sn: '',
  printer_key: '',
  printer_mobile: '',
  printer_provider: '',
  is_enabled: 1
})

const formRules = {
  printer_name: [{ required: true, message: '请输入打印机名称', trigger: 'blur' }],
  printer_sn: [{ required: true, message: '请输入打印机编号', trigger: 'blur' }],
  printer_key: [{ required: true, message: '请输入打印机密钥', trigger: 'blur' }],
  printer_provider: [{ required: true, message: '请选择打印机服务商', trigger: 'change' }]
}

const { options: printer_provider_options } = useEnum('default.tbl_store_printer.printer_provider')

const emit = defineEmits(['complete'])

const setDialogData = async (row: any = null) => {
  loading.value = true
  popTitle = row?.id ? '编辑打印机' : '添加打印机'

  Object.assign(formData, {
    id: 0,
    printer_name: '',
    printer_sn: '',
    printer_key: '',
    printer_mobile: '',
    printer_provider: '',
    is_enabled: 1
  })

  if (row?.id) {
    try {
      const res = await getTblStorePrinterInfo(row.id)
      if (res.code === 10000) {
        Object.assign(formData, res.data)
      }
    } catch (error) {
      console.error('获取打印机信息失败:', error)
    }
  }

  loading.value = false
}

const handleSubmit = async () => {
  try {
    await formRef.value?.validate()
    loading.value = true

    if (formData.id) {
      await updateTblStorePrinter(formData)
    } else {
      await createTblStorePrinter(formData)
    }

    ElMessage.success('保存成功')
    dialogVisible.value = false
    emit('complete')
  } catch (error) {
    console.error('保存失败:', error)
  } finally {
    loading.value = false
  }
}

defineExpose({
  openDialog: () => {
    dialogVisible.value = true
  },
  setDialogData
})
</script>
