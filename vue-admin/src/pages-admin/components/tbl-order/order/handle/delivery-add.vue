<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="700px" :destroy-on-close="true">
        <el-form :model="formData" label-width="100px" ref="formRef" v-loading="loading">
            <el-form-item label="交付方式" prop="delivery_method">
                <el-radio-group v-model="formData.delivery_method">
                    <el-radio v-for="(name, index) in deliveryMethodDict" :key="index" :value="index">{{ name }}</el-radio>
                </el-radio-group>
            </el-form-item>

            <div v-if="formData.delivery_method === 'express'">
                <el-form-item label="快递公司" prop="express_company">
                    <el-select v-model="formData.express_company" placeholder="请选择快递公司">
                        <el-option v-for="(item, index) in expressCompanyList" :key="index" :value="item.name">{{ item.name }}</el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="快递单号" prop="express_number">
                    <el-input v-model="formData.express_number" placeholder="请输入快递单号" />
                </el-form-item>
            </div>

            <div v-if="formData.delivery_method === 'rider'">骑手配送需店铺添加骑手，或者选择抢单模式</div>
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
import type { FormInstance } from 'element-plus'
import { reactive, ref } from 'vue'
import { getTblOrderDeliveryDict, addTblOrderDelivery } from '@/pages-admin/main/api/tbl-order/tblOrderDelivery'
import { getSysExpressList } from '@/api/system/sysExpress'
import type { SysExpress } from '@/api/system/sysExpress.model'

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = ''

const formData = reactive({
  order_id: '',
  delivery_method: '',
  express_type: '',
  express_id: '',
  express_number: '',
  express_company: '',
  rider_id: '',
  technician_id: 0,
})

const formRef = ref<FormInstance>()
const emit = defineEmits(['complete'])

const handleSubmit = async () => {
  if (loading.value || !formRef.value) return
  await formRef.value.validate()
  loading.value = true
  addTblOrderDelivery(formData).then(() => {
    loading.value = false
    dialogVisible.value = false
    emit('complete')
  }).catch(() => {
    loading.value = false
  })
}

const deliveryMethodDict = ref<Record<string, string>>({})
const fetchTblOrderDeliveryDict = async (platform: string) => {
  const res = await getTblOrderDeliveryDict({ platform })
  deliveryMethodDict.value = res.data
}

const expressCompanyList = ref<SysExpress[]>([])
const fetchSysExpressList = async (platform: string) => {
  const res = await getSysExpressList({ platform })
  expressCompanyList.value = res.data
}

const setDialogData = async (row: any = null) => {
  loading.value = true
  popTitle = '订单发货'
  fetchTblOrderDeliveryDict(row.platform)
  fetchSysExpressList(row.platform)
  formData.order_id = row.id
  loading.value = false
}

defineExpose({
  openDialog: () => { dialogVisible.value = true },
  setDialogData
})
</script>
