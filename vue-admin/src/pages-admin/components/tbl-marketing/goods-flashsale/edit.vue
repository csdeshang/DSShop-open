<template>
    <el-drawer v-model="dialogVisible" :title="popTitle" size="80%">
        <el-scrollbar style="height: 100%;">
            <el-form :model="formData" label-width="160px">
                <el-form-item label="商品名称">
                    <span>{{ formData.goods_info?.goods_name }}</span>
                </el-form-item>

                <el-form-item label="是否参与限时折扣">
                    <el-radio-group v-model="formData.goods_info.is_flashsale_goods">
                        <el-radio :value="0">不参与</el-radio>
                        <el-radio :value="1">参与</el-radio>
                    </el-radio-group>
                </el-form-item>

                <div v-show="formData.goods_info.is_flashsale_goods == 1">
                    <el-form-item label="开始时间">
                        <el-date-picker v-model="formData.activity_time.start_time" type="datetime" placeholder="请选择开始时间" />
                    </el-form-item>

                    <el-form-item label="结束时间">
                        <el-date-picker v-model="formData.activity_time.end_time" type="datetime" placeholder="请选择结束时间" />
                    </el-form-item>

                    <el-table :data="formData.sku_flashsale_list" border style="width: 100%" v-if="formData.sku_flashsale_list.length > 0">
                        <el-table-column label="规格名称" prop="sku_name" width="300">
                            <template #default="{ row }">
                                {{ row.sku_name || '默认规格' }}
                            </template>
                        </el-table-column>
                        <el-table-column label="原价" prop="sku_price" width="120">
                            <template #default="{ row }">
                                <span class="text-gray-500">￥{{ row.sku_price }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="折扣价" width="300">
                            <template #default="{ row }">
                                <el-input-number v-model="row.flashsale_price" :min="0" :max="row.sku_price" :precision="2" style="width: 180px" />
                            </template>
                        </el-table-column>
                        <el-table-column label="折扣率" min-width="120">
                            <template #default="{ row }">
                                <span class="text-green-500" v-if="Number(row.flashsale_price) > 0 && Number(row.flashsale_price) < Number(row.sku_price)">
                                    {{ ((Number(row.flashsale_price) / Number(row.sku_price)) * 100).toFixed(1) }}%
                                </span>
                                <span class="text-red-500" v-else-if="Number(row.flashsale_price) >= Number(row.sku_price)">折扣价必须低于原价</span>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </el-form>

            <div class="flex justify-center mt-[20px]">
                <el-button type="primary" @click="handleSubmit" :loading="loading">保存</el-button>
                <el-button @click="dialogVisible = false">取消</el-button>
            </div>
        </el-scrollbar>
    </el-drawer>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'GoodsFlashsaleEdit'
})
</script>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { getTblGoodsFlashsaleInfo, updateTblGoodsFlashsale } from '@/pages-admin/main/api/tbl-marketing/tblGoodsFlashsale'

interface GoodsInfo {
  id: number
  goods_name: string
  flashsale_goods_status: number
  is_flashsale_goods: number
}

interface SkuFlashsale {
  sku_id: number
  sku_name: string
  sku_price: number
  flashsale_price: number
  start_time: string
  end_time: string
  status: number
}

interface ActivityTime {
  start_time: Date | null
  end_time: Date | null
}

interface FormData {
  goods_info: GoodsInfo
  activity_time: ActivityTime
  sku_flashsale_list: SkuFlashsale[]
}

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = '参与限时折扣'

const initialFormData: FormData = {
  goods_info: { id: 0, goods_name: '', flashsale_goods_status: 0, is_flashsale_goods: 0 },
  activity_time: { start_time: null, end_time: null },
  sku_flashsale_list: []
}

const formData = reactive<FormData>({ ...initialFormData })
const emit = defineEmits(['complete'])

const setDialogData = async (row: any = {}) => {
  loading.value = true
  popTitle = '参与限时折扣'
  Object.assign(formData, JSON.parse(JSON.stringify(initialFormData)))

  if (row.id) {
    const data = (await getTblGoodsFlashsaleInfo(row.id)).data
    Object.assign(formData, data)
    if (formData.sku_flashsale_list?.length > 0) {
      const skuItem = formData.sku_flashsale_list[0]
      if (skuItem.start_time && skuItem.end_time) {
        formData.activity_time = {
          start_time: new Date(skuItem.start_time),
          end_time: new Date(skuItem.end_time)
        }
      }
    }
  }
  loading.value = false
}

const handleSubmit = async () => {
  if (loading.value) return
  if (formData.goods_info.is_flashsale_goods === 0) {
    submitData()
    return
  }
  if (!formData.activity_time.start_time || !formData.activity_time.end_time) {
    ElMessage.warning('请选择开始时间和结束时间')
    return
  }
  if (formData.activity_time.start_time >= formData.activity_time.end_time) {
    ElMessage.warning('开始时间必须早于结束时间')
    return
  }
  for (const sku of formData.sku_flashsale_list) {
    const skuPrice = Number(sku.sku_price)
    const flashsalePrice = Number(sku.flashsale_price)
    if (flashsalePrice <= 0) {
      ElMessage.warning(`规格"${sku.sku_name || '默认规格'}"的折扣价必须大于0`)
      return
    }
    if (flashsalePrice >= skuPrice) {
      ElMessage.warning(`规格"${sku.sku_name || '默认规格'}"的折扣价必须低于原价`)
      return
    }
  }
  submitData()
}

const submitData = async () => {
  try {
    loading.value = true
    if (formData.goods_info.is_flashsale_goods === 1 && formData.activity_time.start_time && formData.activity_time.end_time) {
      const formatDate = (date: Date): string => date.toISOString().slice(0, 19).replace('T', ' ')
      const startTime = formatDate(formData.activity_time.start_time)
      const endTime = formatDate(formData.activity_time.end_time)
      formData.sku_flashsale_list.forEach(sku => {
        sku.start_time = startTime
        sku.end_time = endTime
      })
    }
    const result = await updateTblGoodsFlashsale({
      goods_id: formData.goods_info.id,
      is_flashsale_goods: formData.goods_info.is_flashsale_goods,
      sku_flashsale_list: formData.sku_flashsale_list
    })
    if (result.code === 10000) {
      ElMessage.success('保存成功')
      dialogVisible.value = false
      emit('complete')
    } else {
      ElMessage.error(result.message || '保存失败')
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

const openDialog = () => {
  dialogVisible.value = true
}

defineExpose({ openDialog, setDialogData })
</script>

<style scoped>
.el-input-number { width: 120px; }
.el-table { margin: 20px 0; }
.el-form-item { margin-bottom: 20px; }
</style>
